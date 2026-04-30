<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class VerifyPendingDomainsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(\App\Services\DomainVerificationService $service): void
    {
        \App\Models\CustomDomain::where('status', 'pending')
            ->where('created_at', '>=', now()->subDays(7))
            ->chunk(50, function ($domains) use ($service) {
                foreach ($domains as $domain) {
                    $service->verify($domain);
                }
            });
    }
}
