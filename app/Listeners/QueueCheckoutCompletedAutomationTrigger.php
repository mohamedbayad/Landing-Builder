<?php

namespace App\Listeners;

use App\Events\Email\CheckoutCompleted;
use App\Jobs\ProcessAutomationTriggerJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueueCheckoutCompletedAutomationTrigger implements ShouldQueue
{
    public function handle(CheckoutCompleted $event): void
    {
        $preferred = $event->preferredAutomationId ? [$event->preferredAutomationId] : [];

        ProcessAutomationTriggerJob::dispatch(
            userId: $event->userId,
            triggerType: 'checkout_completed',
            context: [
                'lead_id' => $event->leadId,
                'landing_id' => $event->landingId,
                'landing_page_id' => $event->landingPageId,
                'product_id' => $event->productId,
                'preferred_automation_ids' => $preferred,
                'email' => $event->email,
                'first_name' => $event->firstName,
                'last_name' => $event->lastName,
                'phone' => $event->phone,
                'product_name' => $event->productName,
                'order_total' => $event->orderTotal,
                'data' => $event->data,
                'source' => 'checkout_completed',
            ]
        );
    }
}
