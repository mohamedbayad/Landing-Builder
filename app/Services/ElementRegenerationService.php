<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

class ElementRegenerationService
{
    protected $agentService;

    public function __construct(AgentService $agentService)
    {
        $this->agentService = $agentService;
    }

    /**
     * Regenerate a single element within a section
     */
    public function regenerate(string $elementId, string $type, array $contextData, ?string $comment = null, ?int $workspaceId = null): array
    {
        Log::info("Starting Granular Element Regeneration for element: {$elementId}");

        try {
            // STEP 1: Using AgentService for granular regeneration
            $result = $this->agentService->regenerateElement($elementId, $type, $contextData, $comment, $workspaceId);

            // STEP 2: Return the updated content
            // The AI is expected to return a JSON containing the new content for that element
            return [
                'status' => 'success',
                'element_id' => $elementId,
                'data' => $result
            ];
        } catch (Exception $e) {
            Log::error("Element Regeneration Failed: " . $e->getMessage());
            throw $e;
        }
    }
}
