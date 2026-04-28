<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessEmailWebhookJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $provider,
        public array $payload = []
    ) {
        $this->queue = config('queue.connections.'.config('queue.default').'.queue', 'default');
    }

    public function handle(): void
    {
        // V1 stub: webhook parsing can be layered in without changing queue contract.
        Log::info('Email webhook received (stub)', [
            'provider' => $this->provider,
            'payload' => $this->payload,
        ]);
    }
}
