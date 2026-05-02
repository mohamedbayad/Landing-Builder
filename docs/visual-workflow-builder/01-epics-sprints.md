# Epics and Sprint Plan (12 Weeks)

## Assumptions

- Team: 2 frontend engineers, 2 backend engineers, 1 QA, 1 designer, 1 PM.
- Cadence: 6 sprints, 2 weeks each.
- MVP launch target: closed beta after Sprint 6.
- V1 channel execution: Email + WhatsApp only.

## Definition of Done (MVP)

- User can create, validate, preview, publish, pause, and resume workflows.
- Workflow engine processes active executions asynchronously with retries.
- Contacts can be imported, tagged, and enrolled through supported triggers.
- Basic analytics available at workflow, block, and message levels.
- Core reliability and consent constraints are enforced.

## Epic 1: Visual Builder Foundations

### Outcomes

- Responsive React Flow canvas with pan/zoom, node drag/drop, edge connect, minimap.
- Undo/redo and autosave every 10 seconds.
- Draft/live/paused status management.

### Key Stories

1. As a user, I can add blocks from a library onto the canvas.
2. As a user, I can connect blocks with directional edges.
3. As a user, I can configure blocks in the right-side panel.
4. As a user, I can undo/redo edits without data loss.
5. As a user, my workflow draft autosaves every 10 seconds.

### Acceptance Criteria

- Canvas supports 100 nodes and maintains interactive performance.
- Save conflict prevention via workflow version checks (`etag`/`version`).
- Undo/redo stack holds at least 50 actions per session.
- Autosave never overwrites newer server versions.

## Epic 2: Core Block Types and Validation

### Outcomes

- 8 core block types implemented with strict config schemas.
- Builder validation prevents invalid publish states.

### Key Stories

1. Trigger block supports date, event, manual, webhook trigger modes.
2. Send Message block supports `email` and `whatsapp` with variable interpolation.
3. Send Sequence supports ordered messages with per-step delays.
4. Delay supports minutes/hours/days/specific datetime.
5. Branch supports yes/no condition evaluation.
6. Tag block supports add/remove.
7. Goal marks conversion and exit criteria.
8. End Workflow terminates execution explicitly.

### Acceptance Criteria

- Exactly one trigger node is required.
- No orphan nodes allowed at publish.
- Cycles blocked unless explicitly marked as supported loop type (out of V1, so blocked).
- Delay min is 1 minute; max is 365 days; timezone-aware datetime storage.

## Epic 3: Message Composer and Preview

### Outcomes

- Rich text editor for message authoring.
- Variable insertion and channel-specific previews.

### Key Stories

1. User can compose email with subject and body.
2. User can compose WhatsApp template content.
3. User can insert variables from contact/custom fields.
4. User can preview channel rendering before publish.

### Acceptance Criteria

- Variable tokens validated at save (`{{field_name}}`).
- Unknown variables blocked at publish with clear errors.
- Attachment metadata accepted for email (file storage integration can be staged).

## Epic 4: Contacts, Segments, and Enrollment

### Outcomes

- Contact import and field/tag management.
- Trigger-based and manual enrollment.
- Consent model for channel-safe messaging.

### Key Stories

1. User imports contacts via CSV with mapping.
2. User creates custom fields and tags.
3. User builds saved segments for filtered enrollment.
4. User manually enrolls contacts into eligible workflows.

### Acceptance Criteria

- Import validates required identifiers and deduplicates by workspace/contact key.
- Consent fields tracked per channel (`email_opt_in`, `whatsapp_opt_in`, source, timestamp).
- Contacts lacking consent are blocked from channel sends and logged as skipped.

## Epic 5: Workflow Execution Engine

### Outcomes

- Durable execution model with queued processing.
- Delay scheduling, branching, retries, idempotent sends.

### Key Stories

1. Trigger events create `workflow_executions`.
2. Worker processes current node and transitions execution.
3. Delay schedules future processing.
4. Branch evaluates deterministic yes/no path.
5. End node completes execution.

### Acceptance Criteria

- Execution transition is atomic (transactional state update + enqueue).
- Send actions include idempotency keys to avoid duplicate delivery.
- Retry policy configurable per channel with exponential backoff.
- Dead-letter queue captures exhausted failures with reason codes.

## Epic 6: Analytics and Operational Controls

### Outcomes

- Dashboard metrics for workflows, blocks, and messages.
- Operational controls for publish/pause/resume and audit trail.

### Key Stories

1. User sees enrolled/completed/in-progress/dropped-off counts.
2. User sees per-message sent/delivered/opened/clicked.
3. User pauses/resumes live workflows safely.
4. User inspects execution logs for debugging.

### Acceptance Criteria

- Metrics refresh interval max 60 seconds for near-real-time panels.
- Pausing a workflow stops new node processing but does not delete scheduled jobs.
- Resume continues from queued execution states.

## Sprint-by-Sprint Breakdown

## Sprint 1 (Weeks 1-2)

- Project scaffolding (frontend/backend/worker).
- Auth + workspace boundary basics.
- Workflow CRUD API and draft persistence.
- React Flow canvas shell, sidebar/topbar scaffolding.

## Sprint 2 (Weeks 3-4)

- Core node rendering and edge connections.
- Right panel configuration framework.
- Undo/redo + autosave.
- Validation framework (graph + node config).

## Sprint 3 (Weeks 5-6)

- Trigger, Delay, Branch, Tag, Goal, End blocks.
- Message editor integration + variables + previews.
- Contact import, custom fields, tags.

## Sprint 4 (Weeks 7-8)

- Execution engine v1 with queue orchestration.
- Email + WhatsApp adapters (sandbox mode acceptable for beta).
- Manual and event enrollment.
- Publish/pause/resume.

## Sprint 5 (Weeks 9-10)

- Analytics ingestion pipeline + dashboard endpoints.
- Execution logs, dead-letter inspection.
- Hardening: retries, idempotency, race-condition tests.

## Sprint 6 (Weeks 11-12)

- End-to-end QA, load and reliability pass.
- Closed beta onboarding flows and template starter set (internal only).
- Docs, runbooks, and launch checklist.

## Cross-Cutting NFR Acceptance

- 99.9% uptime target supported by health checks and horizontal workers.
- P95 non-delay step processing latency under 5 seconds at target load.
- 10,000 concurrent executions in stress test environment.
- PII encryption at rest and auditable data export/deletion paths.
