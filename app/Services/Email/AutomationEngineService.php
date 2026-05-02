<?php

namespace App\Services\Email;

use App\Jobs\ExecuteAutomationStepJob;
use App\Jobs\ProcessVisualAutomationExecutionJob;
use App\Jobs\SendAutomationChannelMessageJob;
use App\Models\AutomationExecution;
use App\Models\AutomationExecutionHistory;
use App\Models\EmailAutomation;
use App\Models\EmailContact;
use App\Models\EmailContactTag;
use Illuminate\Support\Arr;

class AutomationEngineService
{
    public function __construct(
        private readonly EmailContactService $contactService
    ) {
    }

    public function queueTrigger(int $userId, string $triggerType, array $context = []): int
    {
        $context['trigger_type'] = $triggerType;
        $contact = $this->contactService->upsertFromPayload($userId, $context);

        $automations = $this->resolveAutomations($userId, $triggerType, $context);
        $count = 0;

        foreach ($automations as $automation) {
            if (!$this->triggerMatches($automation, $context)) {
                continue;
            }

            $this->scheduleAutomation($automation, $contact, $context);
            $count++;
        }

        return $count;
    }

    private function resolveAutomations(int $userId, string $triggerType, array $context)
    {
        $query = EmailAutomation::query()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->where('trigger_type', $triggerType);

        $preferredIds = array_values(array_filter((array) ($context['preferred_automation_ids'] ?? [])));
        if (!empty($preferredIds)) {
            $query->whereIn('id', $preferredIds);
        }

        return $query->with('steps')->get();
    }

    private function scheduleAutomation(EmailAutomation $automation, ?EmailContact $contact, array $context): void
    {
        if ($automation->builder_mode && !empty($automation->visual_nodes)) {
            $this->scheduleVisualAutomation($automation, $contact, $context);
            return;
        }

        $delaySeconds = 0;
        $contactId = $contact?->id;

        foreach ($automation->steps as $step) {
            if ($step->step_type === 'wait') {
                $delaySeconds += $this->delayToSeconds($step->delay_value, $step->delay_unit);
                continue;
            }

            if ($step->step_type !== 'send_email' || !$step->template_id) {
                continue;
            }

            $job = new ExecuteAutomationStepJob(
                automationId: $automation->id,
                stepId: $step->id,
                contactId: $contactId,
                context: $context
            );

            if ($delaySeconds > 0) {
                dispatch($job->delay(now()->addSeconds($delaySeconds)));
                continue;
            }

            dispatch($job);
        }
    }

    private function scheduleVisualAutomation(EmailAutomation $automation, ?EmailContact $contact, array $context): void
    {
        $nodes = is_array($automation->visual_nodes) ? $automation->visual_nodes : [];
        if (empty($nodes)) {
            return;
        }

        $triggerNode = collect($nodes)->first(fn ($node) => ($node['type'] ?? null) === 'trigger') ?: $nodes[0];
        $triggerNodeId = (string) ($triggerNode['id'] ?? '');

        if ($triggerNodeId === '') {
            return;
        }

        $execution = AutomationExecution::create([
            'automation_id' => $automation->id,
            'contact_id' => $contact?->id,
            'status' => 'active',
            'current_node_id' => $triggerNodeId,
            'scheduled_for' => now(),
            'context' => $context,
        ]);

        $this->logExecutionEvent($execution->id, $triggerNodeId, 'entered', [
            'from' => 'trigger',
        ]);

        ProcessVisualAutomationExecutionJob::dispatch($execution->id);
    }

    public function processExecution(int $executionId): void
    {
        $execution = AutomationExecution::query()
            ->with(['automation', 'contact.tags'])
            ->find($executionId);

        if (!$execution || !$execution->automation) {
            return;
        }

        $automation = $execution->automation;
        if ($automation->status !== 'active') {
            return;
        }

        $nodes = collect($automation->visual_nodes ?? [])->keyBy(fn ($node) => (string) ($node['id'] ?? ''));
        $edges = collect($automation->visual_edges ?? []);

        if ($nodes->isEmpty()) {
            $this->completeExecution($execution, 'empty_graph');
            return;
        }

        $loopGuard = 0;

        while ($loopGuard < 25) {
            $loopGuard++;

            $nodeId = (string) ($execution->current_node_id ?? '');
            if ($nodeId === '') {
                $this->completeExecution($execution, 'no_current_node');
                return;
            }

            $node = $nodes->get($nodeId);
            if (!$node) {
                $this->failExecution($execution, 'node_missing', "Node [$nodeId] not found.");
                return;
            }

            $type = (string) ($node['type'] ?? '');
            $config = (array) ($node['config'] ?? []);

            $this->logExecutionEvent($execution->id, $nodeId, 'entered', [
                'type' => $type,
            ]);

            $nextNodeId = null;

            if ($type === 'trigger') {
                $nextNodeId = $this->resolveNextNodeId($edges, $nodeId, null);
            } elseif ($type === 'send_message' || $type === 'message') {
                $channel = strtolower((string) ($config['channel'] ?? 'email'));
                $subject = (string) ($config['subject'] ?? '');
                $body = (string) ($config['body'] ?? '');

                SendAutomationChannelMessageJob::dispatch(
                    userId: $automation->user_id,
                    automationId: $automation->id,
                    nodeId: $nodeId,
                    channel: $channel,
                    payload: [
                        'subject' => $subject,
                        'body' => $body,
                    ],
                    contactId: $execution->contact_id,
                    context: (array) $execution->context
                );

                $this->logExecutionEvent($execution->id, $nodeId, 'processed', [
                    'action' => 'send_message',
                    'channel' => $channel,
                ]);

                $nextNodeId = $this->resolveNextNodeId($edges, $nodeId, null);
            } elseif ($type === 'delay') {
                $seconds = $this->resolveDelaySeconds($config);
                $candidate = $this->resolveNextNodeId($edges, $nodeId, null);

                if (!$candidate) {
                    $this->completeExecution($execution, 'delay_without_next');
                    return;
                }

                $execution->update([
                    'current_node_id' => $candidate,
                    'scheduled_for' => now()->addSeconds($seconds),
                ]);

                $this->logExecutionEvent($execution->id, $nodeId, 'processed', [
                    'action' => 'delay',
                    'seconds' => $seconds,
                    'next' => $candidate,
                ]);

                ProcessVisualAutomationExecutionJob::dispatch($execution->id)->delay(now()->addSeconds($seconds));
                return;
            } elseif ($type === 'branch') {
                $branchResult = $this->evaluateBranch($execution, $config);
                $branchLabel = $branchResult ? 'yes' : 'no';
                $nextNodeId = $this->resolveNextNodeId($edges, $nodeId, $branchLabel)
                    ?: $this->resolveNextNodeId($edges, $nodeId, null);

                $this->logExecutionEvent($execution->id, $nodeId, 'processed', [
                    'action' => 'branch',
                    'result' => $branchResult,
                    'branch' => $branchLabel,
                ]);
            } elseif ($type === 'tag') {
                $this->applyTagAction($execution, $config);
                $this->logExecutionEvent($execution->id, $nodeId, 'processed', [
                    'action' => 'tag',
                ]);
                $nextNodeId = $this->resolveNextNodeId($edges, $nodeId, null);
            } elseif ($type === 'goal') {
                $this->logExecutionEvent($execution->id, $nodeId, 'processed', [
                    'action' => 'goal',
                    'goal_name' => (string) ($config['goal_name'] ?? 'Goal'),
                ]);
                $nextNodeId = $this->resolveNextNodeId($edges, $nodeId, null);
            } elseif ($type === 'end' || $type === 'end_workflow') {
                $this->completeExecution($execution, 'end_node');
                return;
            } else {
                $nextNodeId = $this->resolveNextNodeId($edges, $nodeId, null);
            }

            if (!$nextNodeId) {
                $this->completeExecution($execution, 'terminal_node');
                return;
            }

            $execution->update([
                'current_node_id' => $nextNodeId,
                'scheduled_for' => now(),
            ]);
        }

        $this->failExecution($execution, 'loop_guard_reached', 'Execution exceeded loop guard limit.');
    }

    public function previewPath(EmailAutomation $automation, array $context = []): array
    {
        $nodes = collect($automation->visual_nodes ?? [])->keyBy(fn ($node) => (string) ($node['id'] ?? ''));
        $edges = collect($automation->visual_edges ?? []);
        if ($nodes->isEmpty()) {
            return [];
        }

        $triggerNode = $nodes->first(fn ($node) => ($node['type'] ?? '') === 'trigger') ?: $nodes->first();
        $currentNodeId = (string) ($triggerNode['id'] ?? '');

        $path = [];
        $guard = 0;
        while ($currentNodeId !== '' && $guard < 25) {
            $guard++;
            $node = $nodes->get($currentNodeId);
            if (!$node) {
                break;
            }

            $path[] = [
                'id' => $currentNodeId,
                'type' => $node['type'] ?? 'unknown',
                'label' => $node['label'] ?? null,
            ];

            if (in_array(($node['type'] ?? ''), ['end', 'end_workflow'], true)) {
                break;
            }

            $branchLabel = null;
            if (($node['type'] ?? '') === 'branch') {
                $branchLabel = $this->evaluateBranchFromContext((array) ($node['config'] ?? []), $context) ? 'yes' : 'no';
            }

            $currentNodeId = $this->resolveNextNodeId($edges, $currentNodeId, $branchLabel)
                ?: $this->resolveNextNodeId($edges, $currentNodeId, null)
                ?: '';
        }

        return $path;
    }

    private function delayToSeconds(?int $value, ?string $unit): int
    {
        $value = max(0, (int) $value);
        return match ($unit) {
            'days' => $value * 86400,
            'hours' => $value * 3600,
            default => $value * 60, // minutes and fallback
        };
    }

    private function triggerMatches(EmailAutomation $automation, array $context): bool
    {
        $config = $automation->trigger_config ?? [];

        $checks = [
            'landing_id',
            'form_endpoint_id',
            'product_id',
        ];

        foreach ($checks as $key) {
            if (!isset($config[$key]) || $config[$key] === '' || $config[$key] === null) {
                continue;
            }

            if ((string) ($context[$key] ?? '') !== (string) $config[$key]) {
                return false;
            }
        }

        return true;
    }

    private function resolveDelaySeconds(array $config): int
    {
        $value = max(1, (int) ($config['value'] ?? $config['delay_value'] ?? 1));
        $unit = (string) ($config['unit'] ?? $config['delay_unit'] ?? 'minutes');

        return $this->delayToSeconds($value, $unit);
    }

    private function resolveNextNodeId($edges, string $sourceId, ?string $label): ?string
    {
        $matches = $edges
            ->filter(function ($edge) use ($sourceId) {
                return (string) ($edge['source'] ?? '') === $sourceId;
            })
            ->values();

        if ($matches->isEmpty()) {
            return null;
        }

        if ($label !== null) {
            $labeled = $matches->first(function ($edge) use ($label) {
                $edgeLabel = strtolower((string) ($edge['label'] ?? $edge['branch'] ?? $edge['conditionLabel'] ?? ''));
                return $edgeLabel === strtolower($label);
            });

            if ($labeled) {
                return (string) ($labeled['target'] ?? '');
            }
        }

        return (string) ($matches->first()['target'] ?? '');
    }

    private function evaluateBranch(AutomationExecution $execution, array $config): bool
    {
        $context = (array) ($execution->context ?? []);
        $contact = $execution->contact;
        $type = strtolower((string) ($config['condition_type'] ?? $config['type'] ?? 'field_equals'));

        if ($type === 'has_tag') {
            $tag = trim((string) ($config['tag'] ?? ''));
            if ($tag === '' || !$contact) {
                return false;
            }

            return EmailContactTag::query()
                ->where('contact_id', $contact->id)
                ->where('tag', $tag)
                ->exists();
        }

        return $this->evaluateBranchFromContext($config, array_merge($context, [
            'first_name' => $contact?->first_name,
            'last_name' => $contact?->last_name,
            'email' => $contact?->email,
            'phone' => $contact?->phone,
        ]));
    }

    private function evaluateBranchFromContext(array $config, array $context): bool
    {
        $field = (string) ($config['field'] ?? '');
        $operator = strtolower((string) ($config['operator'] ?? 'equals'));
        $expected = $config['value'] ?? null;
        $actual = $field !== '' ? data_get($context, $field) : null;

        return match ($operator) {
            'not_equals' => (string) $actual !== (string) $expected,
            'contains' => str_contains(strtolower((string) $actual), strtolower((string) $expected)),
            'exists' => !is_null($actual) && (string) $actual !== '',
            default => (string) $actual === (string) $expected,
        };
    }

    private function applyTagAction(AutomationExecution $execution, array $config): void
    {
        $contact = $execution->contact;
        if (!$contact) {
            return;
        }

        $tag = trim((string) ($config['tag'] ?? ''));
        if ($tag === '') {
            return;
        }

        $action = strtolower((string) ($config['action'] ?? 'add'));
        if ($action === 'remove') {
            EmailContactTag::query()
                ->where('contact_id', $contact->id)
                ->where('tag', $tag)
                ->delete();
            return;
        }

        EmailContactTag::query()->firstOrCreate([
            'contact_id' => $contact->id,
            'tag' => $tag,
        ]);
    }

    private function completeExecution(AutomationExecution $execution, string $reason): void
    {
        $execution->update([
            'status' => 'completed',
            'current_node_id' => null,
            'scheduled_for' => null,
            'completed_at' => now(),
        ]);

        $this->logExecutionEvent($execution->id, null, 'completed', [
            'reason' => $reason,
        ]);
    }

    private function failExecution(AutomationExecution $execution, string $code, string $message): void
    {
        $execution->update([
            'status' => 'failed',
            'last_error_code' => $code,
            'last_error_message' => $message,
            'scheduled_for' => null,
        ]);

        $this->logExecutionEvent($execution->id, $execution->current_node_id, 'failed', [
            'code' => $code,
            'message' => $message,
        ]);
    }

    private function logExecutionEvent(int $executionId, ?string $nodeId, string $eventType, array $result = []): void
    {
        AutomationExecutionHistory::query()->create([
            'execution_id' => $executionId,
            'node_id' => $nodeId,
            'event_type' => $eventType,
            'result' => Arr::where($result, fn ($value) => !is_null($value)),
            'occurred_at' => now(),
        ]);
    }
}
