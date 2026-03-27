<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class SectionRegenerationService
{
    protected $agentService;

    public function __construct(AgentService $agentService)
    {
        $this->agentService = $agentService;
    }

    /**
     * Regenerate a specific section based on current page context
     */
    public function regenerate(string $sectionName, array $contextData, ?string $imageUrl = null, ?int $workspaceId = null): array
    {
        if (empty($sectionName)) {
            throw new Exception("Section name is required for regeneration.");
        }

        try {
            $response = $this->agentService->regenerateSection($sectionName, $contextData, $imageUrl, $workspaceId);

            return [
                'status' => 'success',
                'data' => [
                    'id' => $sectionName,
                    'content' => $response['content'] ?? $response
                ]
            ];
        } catch (Exception $e) {
            Log::error("Section Regeneration Failed: " . $e->getMessage());
            throw $e;
        }
    }
}
