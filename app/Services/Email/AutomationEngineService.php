<?php

namespace App\Services\Email;

use App\Jobs\ExecuteAutomationStepJob;
use App\Models\EmailAutomation;
use App\Models\EmailContact;

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
}

