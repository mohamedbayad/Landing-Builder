<?php

namespace App\Services;

use App\Models\AiProvider;
use App\Services\AI\Providers\OpenAIProvider;
use App\Support\AI\ProviderRegistry;
use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Support\Facades\Log;

class AIModelLoaderService
{
    /**
     * Fetch available models directly from the Provider API and sync them locally.
     * 
     * @param int $providerId
     * @return array List of models
     */
    public function fetchAndSyncModels(int $providerId): array
    {
        $provider = AiProvider::findOrFail($providerId);

        if (ProviderRegistry::requiresApiKey((string) $provider->provider) && empty($provider->api_key)) {
            throw new Exception("Cannot load models. The provider has no API Key configured.");
        }

        switch (strtolower($provider->provider)) {
            case 'openai':
            case 'openrouter':
                return $this->fetchOpenAICompatibleModels($provider);
            case 'anthropic':
                return $this->fetchAnthropicModels($provider);
            case 'gemini':
            case 'google':
                return $this->fetchGeminiModels($provider);
            case 'custom':
            case 'ollama':
                return $this->fetchOllamaModels($provider);
            default:
                throw new Exception("Dynamic model loading is not currently supported for this provider type.");
        }
    }

    protected function fetchOllamaModels(AiProvider $provider): array
    {
        $baseUrl = ProviderRegistry::normalizeOllamaBaseUrl((string) ($provider->base_url ?: ProviderRegistry::ollamaDefaultBaseUrl()));
        $url = "{$baseUrl}/api/tags";

        Log::info("AI Loader: Fetching Ollama models from {$url}");

        $request = Http::timeout(10);
        if (!empty($provider->api_key)) {
            $request = $request->withToken($provider->api_key);
        }
        $response = $request->get($url);

        if ($response->failed()) {
            Log::error("AI Loader: Failed to fetch Ollama models", ['body' => $response->body()]);
            throw new Exception("Failed to fetch Ollama models from {$url}. Ensure the server is reachable.");
        }

        $data = $response->json();
        $models = [];
        $resolver = new \App\Services\AI\Resolvers\OllamaModelCapabilityResolver();
        
        $rawModels = $data['models'] ?? [];

        foreach ($rawModels as $modelData) {
            $id = $modelData['model'] ?? $modelData['name'];
            
            $models[] = [
                'id' => $id,
                'name' => $id,
                'capabilities' => $resolver->resolveCapabilities($id, $modelData)
            ];
        }

        Log::info("AI Loader: Successfully fetched " . count($models) . " Ollama models.");

        return $models;
    }

    protected function fetchOpenAICompatibleModels(AiProvider $provider): array
    {
        $providerKey = strtolower($provider->provider);
        $defaultBaseUrl = ProviderRegistry::defaultBaseUrlFor($providerKey) ?? ProviderRegistry::OPENAI_DEFAULT_BASE_URL;

        $openAICompatibleProvider = new OpenAIProvider($defaultBaseUrl);
        if (!empty($provider->base_url)) {
            $openAICompatibleProvider->setBaseUrl($provider->base_url);
        }

        $url = rtrim($openAICompatibleProvider->getBaseUrl(), '/') . '/models';

        $response = Http::withToken($provider->api_key)
            ->timeout(20)
            ->get($url);

        if ($response->failed()) {
            throw new Exception("Failed to fetch {$providerKey} models: " . $response->body());
        }

        $data = $response->json();
        
        // Filter stringently to LLMs for safety if preferred, or return all
        $models = [];
        $resolver = new \App\Services\AI\Resolvers\OpenAIModelCapabilityResolver();
        
        foreach (($data['data'] ?? []) as $modelData) {
            $modelId = $modelData['id'] ?? null;
            if (!$modelId) {
                continue;
            }
            
            $models[] = [
                'id' => $modelId,
                'name' => $modelId, // UI display name
                'capabilities' => $resolver->resolveCapabilities($modelId, $modelData)
            ];
        }

        return $models;
    }

    protected function fetchAnthropicModels(AiProvider $provider): array
    {
        $url = $provider->base_url ?? 'https://api.anthropic.com/v1/models';

        // Anthropic requires specific headers, including anthropic-version
        $response = Http::withHeaders([
            'x-api-key' => $provider->api_key,
            'anthropic-version' => '2023-06-01'
        ])->get($url);

        $resolver = new \App\Services\AI\Resolvers\AnthropicModelCapabilityResolver();

        if ($response->failed()) {
            // As of early 2024, Anthropic api.anthropic.com/v1/models exists but if it fails,
            // we provide a hardcoded fallback of known Claude models since they change infrequently.
            Log::warning("Anthropic models fetch failed, using fallback list. " . $response->body());
            
            $knownModels = ['claude-3-5-sonnet-20240620', 'claude-3-opus-20240229', 'claude-3-haiku-20240307'];
            $models = [];
            foreach ($knownModels as $id) {
                $models[] = [
                    'id' => $id,
                    'name' => $id,
                    'capabilities' => $resolver->resolveCapabilities($id)
                ];
            }
            return $models;
        }

        $data = $response->json();
        $models = [];
        
        if (isset($data['data'])) {
            foreach ($data['data'] as $modelData) {
                $id = $modelData['id'] ?? $modelData['name'];
                $models[] = [
                    'id' => $id,
                    'name' => $modelData['display_name'] ?? $id,
                    'capabilities' => $resolver->resolveCapabilities($id, $modelData)
                ];
            }
        }

        return $models;
    }

    protected function fetchGeminiModels(AiProvider $provider): array
    {
        $url = $provider->base_url ?? "https://generativelanguage.googleapis.com/v1beta/models?key={$provider->api_key}";

        $response = Http::get($url);

        if ($response->failed()) {
            throw new Exception("Failed to fetch Gemini models: " . $response->body());
        }

        $data = $response->json();
        $models = [];
        
        $resolver = new \App\Services\AI\Resolvers\GeminiModelCapabilityResolver();

        foreach ($data['models'] as $modelData) {
            $id = str_replace('models/', '', $modelData['name']);
            
            $models[] = [
                'id' => $id,
                'name' => $modelData['displayName'] ?? $id,
                'capabilities' => $resolver->resolveCapabilities($id, $modelData)
            ];
        }

        return $models;
    }
}
