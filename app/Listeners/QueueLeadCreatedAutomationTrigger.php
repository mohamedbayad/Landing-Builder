<?php

namespace App\Listeners;

use App\Events\Email\LeadCreated;
use App\Jobs\ProcessAutomationTriggerJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueueLeadCreatedAutomationTrigger implements ShouldQueue
{
    public function handle(LeadCreated $event): void
    {
        ProcessAutomationTriggerJob::dispatch(
            userId: $event->userId,
            triggerType: 'lead_created',
            context: [
                'lead_id' => $event->leadId,
                'landing_id' => $event->landingId,
                'landing_page_id' => $event->landingPageId,
                'email' => $event->email,
                'first_name' => $event->firstName,
                'last_name' => $event->lastName,
                'phone' => $event->phone,
                'data' => $event->data,
                'source' => 'lead_created',
            ]
        );
    }
}
