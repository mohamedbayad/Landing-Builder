<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AiProvider;
use App\Models\AiModel;
use App\Services\AIModelLoaderService;
use Exception;
use Illuminate\Support\Facades\Log;

class AiConfigurationController extends Controller
{
    /**
     * Store a new AI Provider
     */
    public function storeProvider(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'required|string|in:openai,anthropic,gemini,custom',
            'api_key' => 'required|string',
            'base_url' => 'nullable|url'
        ]);

        $workspace = auth()->user()->workspaces()->first();

        AiProvider::create([
            'workspace_id' => $workspace->id,
            'name' => $request->name,
            'provider' => $request->provider,
            'api_key' => $request->api_key,
            'base_url' => $request->base_url,
            'is_active' => true,
        ]);

        return back()->with('success', 'AI Provider added successfully.');
    }

    /**
     * Delete an AI Provider
     */
    public function destroyProvider(AiProvider $provider)
    {
        // Simple authorization check
        $workspace = auth()->user()->workspaces()->first();
        if ($provider->workspace_id !== $workspace->id) {
            abort(403);
        }

        $provider->delete();

        return back()->with('success', 'AI Provider removed.');
    }

    /**
     * Trigger dynamic model loading for a specific provider
     */
    public function loadModels(AiProvider $provider, AIModelLoaderService $loaderService)
    {
        $workspace = auth()->user()->workspaces()->first();
        if ($provider->workspace_id !== $workspace->id) {
            abort(403);
        }

        try {
            $fetchedModels = $loaderService->fetchAndSyncModels($provider->id);

            // Sync with DB
            foreach ($fetchedModels as $modelData) {
                AiModel::updateOrCreate(
                    [
                        'ai_provider_id' => $provider->id,
                        'name' => $modelData['id'],
                    ],
                    [
                        'supports_text_generation' => $modelData['capabilities']['supports_text_generation'],
                        'supports_image_generation' => $modelData['capabilities']['supports_image_generation'],
                        'supports_vision' => $modelData['capabilities']['supports_vision'],
                        'supports_embeddings' => $modelData['capabilities']['supports_embeddings'],
                        'supports_audio' => $modelData['capabilities']['supports_audio'],
                    ]
                );
            }

            return back()->with('success', count($fetchedModels) . ' models loaded and synced successfully.');
        } catch (Exception $e) {
            Log::error("Model loading failed: " . $e->getMessage());
            return back()->with('error', 'Failed to load models: ' . $e->getMessage());
        }
    }

    /**
     * Update model role assignments (set defaults)
     */
    public function updateModelRoles(Request $request)
    {
        $request->validate([
            'roles' => 'required|array',
        ]);

        $workspace = auth()->user()->workspaces()->first();
        $settings = $workspace->settings;

        if (!$settings) {
            $settings = $workspace->settings()->create([]);
        }

        // Current assignments
        $assignments = $settings->ai_role_assignments ?? [];

        // Role mapping (internal key to user-friendly key for JSON)
        $roleMap = [
            'text_generation' => 'text_generation',
            'image_generation' => 'image_generation',
            'vision' => 'vision_analysis', // User specifically asked for 'vision_analysis'
            'embeddings' => 'embeddings',
            'audio' => 'audio',
        ];

        $workspaceId = $workspace->id;

        foreach ($roleMap as $internalKey => $jsonKey) {
            if (isset($request->roles[$internalKey])) {
                $modelId = $request->roles[$internalKey];
                $model = AiModel::with('provider')->find($modelId);

                if ($model && $model->provider->workspace_id === $workspaceId) {
                    // Update binary flags (optional, but good for backward compatibility)
                    AiModel::whereHas('provider', function ($query) use ($workspaceId) {
                        $query->where('workspace_id', $workspaceId);
                    })->update(['is_default_' . $internalKey => false]);

                    $model->update(['is_default_' . $internalKey => true]);

                    // Update JSON assignments (This is the new source of truth)
                    $assignments[$jsonKey] = [
                        'provider' => $model->provider->provider,
                        'model' => $model->name,
                        'provider_id' => $model->ai_provider_id
                    ];
                }
            }
        }

        $settings->update([
            'ai_role_assignments' => $assignments
        ]);

        return back()->with('success', 'AI Task assignments updated successfully.');
    }
}
