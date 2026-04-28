<?php

namespace App\Providers;

use App\Events\Email\CheckoutCompleted;
use App\Events\Email\FormSubmitted;
use App\Events\Email\LeadCreated;
use App\Listeners\QueueCheckoutCompletedAutomationTrigger;
use App\Listeners\QueueFormSubmittedAutomationTrigger;
use App\Listeners\QueueLeadCreatedAutomationTrigger;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        FormSubmitted::class => [
            QueueFormSubmittedAutomationTrigger::class,
        ],
        CheckoutCompleted::class => [
            QueueCheckoutCompletedAutomationTrigger::class,
        ],
        LeadCreated::class => [
            QueueLeadCreatedAutomationTrigger::class,
        ],
    ];
}

