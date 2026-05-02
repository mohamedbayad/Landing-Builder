# Data Model and Event Contracts (MVP)

## Design Goals

- Deterministic workflow execution with idempotent side effects.
- Workspace-level isolation for multi-tenant safety.
- Query-efficient analytics without blocking execution throughput.

## Core Entities

## `workflows`

- `id` (uuid, pk)
- `workspace_id` (uuid, indexed)
- `name` (varchar)
- `status` (`draft|live|paused|archived`)
- `version` (int, optimistic concurrency)
- `timezone` (varchar, IANA tz)
- `trigger_config` (jsonb)
- `nodes` (jsonb)
- `edges` (jsonb)
- `published_at` (timestamptz, nullable)
- `created_at`, `updated_at`

Notes:
- `nodes` and `edges` are persisted as graph JSON for builder parity.
- Publish snapshots are immutable via versioning; edits produce new `version`.

## `contacts`

- `id` (uuid, pk)
- `workspace_id` (uuid, indexed)
- `external_ref` (varchar, nullable)
- `email` (varchar, nullable, indexed)
- `phone_e164` (varchar, nullable, indexed)
- `instagram_handle` (varchar, nullable)
- `first_name`, `last_name` (varchar, nullable)
- `custom_fields` (jsonb)
- `created_at`, `updated_at`

## `contact_tags`

- `workspace_id` (uuid, indexed)
- `contact_id` (uuid, indexed)
- `tag` (varchar, indexed)
- `created_at`
- Unique: (`workspace_id`, `contact_id`, `tag`)

## `contact_consents`

- `id` (uuid, pk)
- `workspace_id` (uuid, indexed)
- `contact_id` (uuid, indexed)
- `channel` (`email|whatsapp|sms|instagram`)
- `status` (`opted_in|opted_out|unconfirmed`)
- `source` (varchar)  // csv_import, form, api, manual
- `evidence` (jsonb)  // ip, form_id, import_file_id
- `consented_at` (timestamptz, nullable)
- `revoked_at` (timestamptz, nullable)
- Unique: (`workspace_id`, `contact_id`, `channel`)

## `workflow_executions`

- `id` (uuid, pk)
- `workspace_id` (uuid, indexed)
- `workflow_id` (uuid, indexed)
- `workflow_version` (int)
- `contact_id` (uuid, indexed)
- `status` (`active|completed|exited|failed|paused`)
- `current_node_id` (varchar, nullable)
- `scheduled_for` (timestamptz, indexed)
- `last_error_code` (varchar, nullable)
- `last_error_message` (text, nullable)
- `created_at`, `updated_at`, `completed_at` (nullable)

Unique safety key:
- (`workspace_id`, `workflow_id`, `workflow_version`, `contact_id`, `status='active'`) enforced by partial index strategy.

## `workflow_execution_history`

- `id` (bigserial, pk)
- `workspace_id` (uuid, indexed)
- `execution_id` (uuid, indexed)
- `node_id` (varchar)
- `event_type` (varchar)  // entered, processed, skipped, failed, exited
- `result` (jsonb)
- `created_at` (timestamptz)

## `message_dispatches`

- `id` (uuid, pk)
- `workspace_id` (uuid, indexed)
- `execution_id` (uuid, indexed)
- `workflow_id` (uuid, indexed)
- `node_id` (varchar, indexed)
- `channel` (`email|whatsapp`)
- `provider` (varchar)  // sendgrid, postmark, twilio, meta
- `idempotency_key` (varchar, unique)
- `provider_message_id` (varchar, nullable, indexed)
- `status` (`queued|sent|delivered|opened|clicked|failed|skipped`)
- `error_code` (varchar, nullable)
- `error_payload` (jsonb, nullable)
- `created_at`, `updated_at`

## `workflow_metrics_daily`

- `workspace_id` (uuid, indexed)
- `workflow_id` (uuid, indexed)
- `date` (date, indexed)
- `enrolled_count` (int)
- `completed_count` (int)
- `in_progress_count` (int)
- `dropped_off_count` (int)
- Primary key: (`workspace_id`, `workflow_id`, `date`)

## Queue Topology (BullMQ)

- `workflow-events`: incoming trigger events.
- `workflow-executions`: node processing jobs.
- `message-dispatch`: channel delivery jobs.
- `dlq-workflow-executions`: terminal failures.
- `dlq-message-dispatch`: message delivery failures after retries.

## Event Contract: Trigger Ingest

Topic/Queue Payload (`workflow-events`):

```json
{
  "eventId": "uuid",
  "workspaceId": "uuid",
  "eventType": "tag.added|form.submitted|purchase.made|link.clicked|manual.enroll|webhook.received",
  "contactId": "uuid",
  "occurredAt": "2026-05-01T10:30:00Z",
  "attributes": {
    "tag": "buyer",
    "formId": "frm_123",
    "orderId": "ord_123"
  }
}
```

Rules:
- `eventId` is dedup key for 24h window.
- Trigger matcher resolves workflows by `workspace_id`, `status='live'`, and trigger conditions.

## Event Contract: Execution Job

Payload (`workflow-executions`):

```json
{
  "jobId": "exec_uuid:node_id:attempt_n",
  "workspaceId": "uuid",
  "executionId": "uuid",
  "workflowId": "uuid",
  "workflowVersion": 3,
  "nodeId": "node_12",
  "scheduledFor": "2026-05-01T10:40:00Z",
  "attempt": 1
}
```

Rules:
- Job processing must re-read execution row in transaction.
- Ignore stale jobs where execution no longer references `nodeId`.

## Event Contract: Message Dispatch Job

Payload (`message-dispatch`):

```json
{
  "dispatchId": "uuid",
  "workspaceId": "uuid",
  "executionId": "uuid",
  "workflowId": "uuid",
  "nodeId": "node_20",
  "channel": "email",
  "to": {
    "email": "user@example.com",
    "phoneE164": null
  },
  "content": {
    "subject": "Welcome {{first_name}}",
    "body": "<p>...</p>"
  },
  "idempotencyKey": "ws:exec:node:channel:hash"
}
```

Rules:
- Must check consent before queueing provider call.
- If no consent/recipient, mark `skipped` with reason code.

## Retry and Idempotency Policy

- Execution retries: 5 attempts, exponential backoff (5s, 30s, 2m, 10m, 30m).
- Message retries: provider/network errors only, max 6 attempts.
- Do not retry on permanent failures (invalid recipient, opt-out, template invalid).
- Idempotency key unique per execution-node-channel-content hash.

## Publish/Pause/Resume Semantics

- `publish`: freeze current graph as new `workflow_version`, set status `live`.
- `pause`: prevents new node processing; running jobs check status and defer.
- `resume`: reactivates processing for queued executions.
- Editing a live workflow creates next draft version; no hot mutation of active version.
