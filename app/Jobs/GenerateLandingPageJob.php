<?php

namespace App\Jobs;

use App\Models\AiGenerationTask;
use App\Services\LandingPagePipelineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class GenerateLandingPageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes total for 6 phases
    public $tries = 1;

    protected int $taskId;

    public function __construct(int $taskId)
    {
        $this->taskId = $taskId;
    }

    public function handle(LandingPagePipelineService $pipeline): void
    {
        $task = AiGenerationTask::find($this->taskId);

        if (!$task) {
            Log::warning("GenerateLandingPageJob: Task #{$this->taskId} not found.");
            return;
        }

        // Start Processing
        if ($task->status === 'pending') {
            $task->update([
                'status'   => 'processing',
                'progress' => 0,
            ]);
        }

        Log::info("Job: Resuming/Starting Workflow Task #{$task->id} (UUID: {$task->uuid}) at Phase: {$task->current_phase}");

        try {
            $input = $task->input_data;
            $workspaceId = $task->workspace_id;

            // Phase 1: Input & Research
            if (empty($task->product_identity)) {
                $this->updateProgress($task, 15, 'research');
                $researchData = $pipeline->runPhase1_Research($input, $workspaceId);
                $task->update(['product_identity' => $researchData]);
            }

            // Phase 2: Strategy Engine
            if (empty($task->conversion_blueprint)) {
                $this->updateProgress($task, 35, 'strategy');
                // Ensure Phase 1 output is loaded
                $researchData = $task->product_identity; 
                $blueprint = $pipeline->runPhase2_Strategy($researchData, $input, $workspaceId);
                $task->update(['conversion_blueprint' => $blueprint]);
            }

            // Phase 3: Structure & Copy
            if (empty($task->page_structure)) {
                $this->updateProgress($task, 55, 'copywriting');
                $researchData = $task->product_identity;
                $blueprint = $task->conversion_blueprint;
                $structure = $pipeline->runPhase3_StructureAndCopy($blueprint, $researchData, $input, $workspaceId);
                $task->update(['page_structure' => $structure]);
            }

            // Phase 4: Visual Generation
            if (empty($task->generated_images)) {
                $this->updateProgress($task, 80, 'visuals');
                $structure = $task->page_structure;
                $researchData = $task->product_identity;
                $blueprint = $task->conversion_blueprint;
                
                $visualOutput = $pipeline->runPhase4_Visuals($structure, $researchData, $blueprint, $input, $workspaceId);
                // Save the updated HTML sections + the asset paths map
                $task->update([
                    'page_structure' => $visualOutput['sections'],
                    'generated_images' => $visualOutput['images_map']
                ]);
            }

            // Phase 5 & 6: Compile & Validate
            if (empty($task->builder_payload)) {
                $this->updateProgress($task, 95, 'compile');
                // Use the updated structure that contains the visual SRC replacements
                $sectionsWithVisuals = ['sections' => $task->page_structure];
                $payload = $pipeline->runPhase5_Compile($sectionsWithVisuals, $input);
                $task->update(['builder_payload' => $payload]);
            }

            // Final Completion state
            $this->updateProgress($task, 100, 'completed');
            
            // Re-package exactly what the frontend expects for preview and builder hooks
            $frontendResultData = [
                'data' => [
                    'sections' => $task->page_structure,
                    'html' => $task->builder_payload['html'] ?? '',
                    'css'  => $task->builder_payload['css'] ?? '',
                    'js'   => $task->builder_payload['js'] ?? '',
                    'custom_head' => $task->builder_payload['custom_head'] ?? ''
                ]
            ];

            $task->update([
                'status'        => 'completed',
                'result_data'   => $frontendResultData, 
            ]);

            // Cleanup temp image if it was uploaded locally
            if (isset($input['is_temporary']) && $input['is_temporary'] && !empty($input['image_path']) && file_exists($input['image_path'])) {
                @unlink($input['image_path']);
            }

            Log::info("Job: Finalized AI Generation Task #{$task->id}. Complete.");

        } catch (Exception $e) {
            $msg = $e->getMessage();
            $task->update([
                'status'        => 'failed',
                'error'         => $msg,
                'error_message' => $msg,
            ]);

            Log::error("Job: AI Generation Task #{$task->id} failed in phase '{$task->current_phase}'", [
                'error' => $msg,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    private function updateProgress(AiGenerationTask $task, int $pct, string $phaseName): void
    {
        $task->update([
            'progress'      => $pct,
            'current_phase' => $phaseName,
        ]);
        Log::info("Job Progress [{$task->uuid}]: {$pct}% — {$phaseName}");
    }
}
