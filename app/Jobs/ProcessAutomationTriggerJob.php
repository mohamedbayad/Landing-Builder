<?php

namespace App\Jobs;

use App\Services\Email\AutomationEngineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAutomationTriggerJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $userId,
        public string $triggerType,
        public array $context = []
    ) {
        $this->queue = config('queue.connections.'.config('queue.default').'.queue', 'default');
    }

    public function handle(AutomationEngineService $engine): void
    {
        $engine->queueTrigger(
            userId: $this->userId,
            triggerType: $this->triggerType,
            context: $this->context
        );
    }
}
