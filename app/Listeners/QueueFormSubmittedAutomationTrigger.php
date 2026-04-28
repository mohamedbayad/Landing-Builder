<?php

namespace App\Listeners;

use App\Events\Email\FormSubmitted;
use App\Jobs\ProcessAutomationTriggerJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueueFormSubmittedAutomationTrigger implements ShouldQueue
{
    public function handle(FormSubmitted $event): void
    {
        $preferred = $event->preferredAutomationId ? [$event->preferredAutomationId] : [];

        ProcessAutomationTriggerJob::dispatch(
            userId: $event->userId,
            triggerType: 'form_submitted',
            context: [
                'form_id' => $event->formId,
                'landing_id' => $event->landingId,
                'form_endpoint_id' => $event->formEndpointId,
                'preferred_automation_ids' => $preferred,
                'email' => $event->email,
                'first_name' => $event->firstName,
                'last_name' => $event->lastName,
                'phone' => $event->phone,
                'data' => $event->data,
                'source' => 'form_submitted',
            ]
        );
    }
}
