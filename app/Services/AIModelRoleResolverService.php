<?php

namespace App\Services;

use App\Models\AiModel;
use Exception;
use Illuminate\Support\Facades\Log;

class AIModelRoleResolverService
{
    /**
     * Resolve the active AI Provider and Model for a specific role.
     * Roles: text_generation, image_generation, vision, embeddings, audio
     * 
     * @return array ['provider' => '...', 'model' => '...', 'apiKey' => '...', 'baseUrl' => '...']
     */
    public function resolveByRole(string $role, ?int $workspaceId = null): array
    {
        if (!$workspaceId && auth()->check()) {
            $workspace = auth()->user()->workspaces()->first();
            $workspaceId = $workspace ? $workspace->id : null;
        }

        if (!$workspaceId) {
            Log::error("AI Role Resolution Failed: workspaceId is null. Auth check: " . (auth()->check() ? 'true' : 'false'));
            throw new Exception("Cannot resolve AI roles: No active workspace context found (Trace: " . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)[2]['function'] . ")");
        }

        $workspace = \App\Models\Workspace::with('settings')->find($workspaceId);
        $settings = $workspace->settings;
        $assignments = $settings ? $settings->ai_role_assignments : null;

        // Task Name Mapping (Role requested => JSON key)
        $jsonKeyMap = [
            'text_generation' => 'text_generation',
            'image_generation' => 'image_generation',
            'vision' => 'vision_analysis',
            'vision_analysis' => 'vision_analysis',
            'embeddings' => 'embeddings',
            'audio' => 'audio',
        ];

        $jsonKey = $jsonKeyMap[$role] ?? $role;

        // Try to resolve from JSON first
        if ($assignments && isset($assignments[$jsonKey])) {
            $assignment = $assignments[$jsonKey];
            
            // Optimization: Find the model by name and provider_id if available
            $activeModel = AiModel::where('name', $assignment['model'])
                ->where('ai_provider_id', $assignment['provider_id'] ?? null)
                ->with('provider')
                ->first();
            
            if ($activeModel && $activeModel->provider->is_active) {
                return [
                    'provider' => $activeModel->provider->provider,
                    'model' => $activeModel->name,
                    'apiKey' => $activeModel->provider->api_key,
                    'baseUrl' => $activeModel->provider->base_url,
                ];
            }
        }

        // Fallback to legacy boolean columns if JSON not found or model missing
        $roleFieldMap = [
            'text_generation' => 'is_default_text_generation',
            'image_generation' => 'is_default_image_generation',
            'vision' => 'is_default_vision',
            'vision_analysis' => 'is_default_vision',
            'embeddings' => 'is_default_embeddings',
            'audio' => 'is_default_audio',
        ];

        $booleanColumn = $roleFieldMap[$role] ?? 'is_default_text_generation';

        // Find the single chosen default model for this specific role
        $activeModel = AiModel::where($booleanColumn, true)
            ->whereHas('provider', function ($query) use ($workspaceId) {
                $query->where('workspace_id', $workspaceId)
                      ->where('is_active', true);
            })
            ->with('provider')
            ->first();

        if (!$activeModel) {
            // Check fallback to completely legacy `.env` config
            Log::warning("No active default AI Model found for role: {$role}. Falling back to default ENV configs.");
            
            $provider = config('services.ai.provider');
            $model = config('services.ai.model');
            $apiKey = config('services.ai.key');

            if (empty($provider) || empty($model) || empty($apiKey)) {
                 throw new Exception("No active AI model found for role: '{$role}'. Please configure a default model in Settings.");
            }

            return [
                'provider' => $provider,
                'model' => $model,
                'apiKey' => $apiKey,
                'baseUrl' => null,
            ];
        }

        // Pull the credentials from the parent provider
        $providerConfig = $activeModel->provider;

        return [
            'provider' => $providerConfig->provider,
            'model' => $activeModel->name,
            'apiKey' => $providerConfig->api_key,
            'baseUrl' => $providerConfig->base_url,
        ];
    }
}
