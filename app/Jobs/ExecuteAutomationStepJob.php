<?php

namespace App\Jobs;

use App\Models\EmailAutomation;
use App\Models\EmailAutomationStep;
use App\Models\EmailContact;
use App\Services\Email\EmailContactService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExecuteAutomationStepJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $automationId,
        public int $stepId,
        public ?int $contactId = null,
        public array $context = []
    ) {
        $this->queue = config('queue.connections.'.config('queue.default').'.queue', 'default');
    }

    public function handle(EmailContactService $contactService): void
    {
        $automation = EmailAutomation::query()->find($this->automationId);
        if (!$automation || $automation->status !== 'active') {
            return;
        }

        $step = EmailAutomationStep::query()->find($this->stepId);
        if (!$step || $step->step_type !== 'send_email' || !$step->template_id) {
            return;
        }

        $contact = $this->contactId
            ? EmailContact::query()->find($this->contactId)
            : null;

        if (!$contact && !empty($this->context)) {
            $contact = $contactService->upsertFromPayload($automation->user_id, $this->context);
        }

        SendEmailMessageJob::dispatch(
            userId: $automation->user_id,
            automationId: $automation->id,
            stepId: $step->id,
            templateId: $step->template_id,
            contactId: $contact?->id,
            context: $this->context
        );
    }
}
