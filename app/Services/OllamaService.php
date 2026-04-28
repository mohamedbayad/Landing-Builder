<?php

namespace App\Services;

use App\Support\AI\ProviderRegistry;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class OllamaService
{
    protected string $baseUrl;
    protected string $model;

    public function __construct()
    {
        // Read directly from services config, which reads from env
        $this->baseUrl = ProviderRegistry::normalizeOllamaBaseUrl((string) config('services.ollama.base_url', ProviderRegistry::OLLAMA_FALLBACK_BASE_URL));
        $this->model = config('services.ollama.model', 'llama3');
    }

    public function setBaseUrl(string $url): self
    {
        $this->baseUrl = ProviderRegistry::normalizeOllamaBaseUrl($url);
        return $this;
    }

    /**
     * Fetch models from Ollama API
     */
    public function listModels(): array
    {
        $url = "{$this->baseUrl}/api/tags";
        Log::info("Ollama: Fetching models from {$url}");

        try {
            $response = Http::timeout(10)->get($url);

            if ($response->failed()) {
                Log::error("Ollama: Failed to fetch models", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return [];
            }

            $raw = $response->json();
            Log::debug("Ollama: Raw models response", ['response' => $raw]);

            $models = $raw['models'] ?? [];

            return collect($models)->map(function ($model) {
                return [
                    'id' => $model['model'] ?? $model['name'] ?? null,
                    'name' => $model['name'] ?? $model['model'] ?? 'unknown',
                    'provider' => 'ollama',
                    'capabilities' => ['text'], // Default for Ollama for now
                    'raw' => $model,
                ];
            })->filter(fn ($model) => !empty($model['id']))->values()->toArray();

        } catch (Exception $e) {
            Log::error("Ollama: Exception during listModels", ['message' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Generate a text completion
     */
    public function generate(string $prompt, array $options = []): string
    {
        $url = "{$this->baseUrl}/api/generate";
        $model = $options['model'] ?? $this->model;
        $apiKey = $options['api_key'] ?? null;
        unset($options['api_key']);
        
        Log::info("Ollama: Generating text", ['model' => $model, 'url' => $url]);

        $response = $this->requestWithOptionalToken((string) $apiKey)
            ->timeout(240)
            ->post($url, [
            'model' => $model,
            'prompt' => $prompt,
            'stream' => false,
            'options' => $options
            ]);

        if ($response->failed()) {
            $this->handleError($response);
        }

        return $response->json('response');
    }

    /**
     * Generate a completion with structured JSON output
     */
    public function generateStructured(string $prompt, array $options = []): array
    {
        $url = "{$this->baseUrl}/api/generate";
        $model = $options['model'] ?? $this->model;
        $apiKey = $options['api_key'] ?? null;
        unset($options['api_key']);

        Log::info("Ollama: Generating structured JSON", ['model' => $model, 'url' => $url]);

        $response = $this->requestWithOptionalToken((string) $apiKey)
            ->timeout(300)
            ->post($url, [
            'model' => $model,
            'prompt' => $prompt,
            'stream' => false,
            'format' => 'json',
            'options' => array_merge([
                'temperature' => 0.7,
            ], $options)
            ]);

        if ($response->failed()) {
            $this->handleError($response);
        }

        $rawBody = (string) $response->body();
        $contentText = $response->json('response');

        $decoded = $this->decodeStructuredPayload($contentText);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Fallback: sometimes providers return JSON directly in body or wrapped text.
        $decodedFromBody = $this->decodeStructuredPayload($rawBody);
        if (is_array($decodedFromBody)) {
            return $decodedFromBody;
        }

        $preview = mb_substr(trim((string) ($contentText ?? $rawBody)), 0, 500);
        Log::error("Ollama: JSON Decoding Failed", [
            'response_field_type' => gettype($contentText),
            'response_field_preview' => mb_substr(trim((string) $contentText), 0, 500),
            'raw_body_preview' => mb_substr(trim($rawBody), 0, 500),
        ]);

        if ($preview === '') {
            throw new Exception("Ollama returned empty response while JSON was expected.");
        }

        throw new Exception("Ollama returned non-JSON response while JSON was expected.");
    }

    /**
     * Start/continue a chat conversation
     */
    public function chat(array $messages, array $options = []): array
    {
        $url = "{$this->baseUrl}/api/chat";
        $model = $options['model'] ?? $this->model;
        $apiKey = $options['api_key'] ?? null;
        unset($options['api_key']);

        Log::info("Ollama: Chat request", ['model' => $model, 'url' => $url]);

        $response = $this->requestWithOptionalToken((string) $apiKey)
            ->timeout(240)
            ->post($url, [
            'model' => $model,
            'messages' => $messages,
            'stream' => false,
            'options' => $options
            ]);

        if ($response->failed()) {
            $this->handleError($response);
        }

        return $response->json('message');
    }

    /**
     * Check if the Ollama service is reachable
     */
    public function health(): array
    {
        try {
            $url = "{$this->baseUrl}/api/tags";
            $response = Http::timeout(5)->get($url);
            return [
                'status' => $response->successful() ? 'healthy' : 'unhealthy',
                'model' => $this->model,
                'connection' => $response->successful(),
                'available_models' => $response->json('models') ?? []
            ];
        } catch (Exception $e) {
            return [
                'status' => 'offline',
                'error' => $e->getMessage(),
                'connection' => false
            ];
        }
    }

    /**
     * Log and throw error for failed requests
     */
    protected function handleError($response)
    {
        $errorBody = $response->body();
        Log::error("Ollama API Request Failed", [
            'url' => $this->baseUrl,
            'status' => $response->status(),
            'body' => $errorBody
        ]);
        
        throw new Exception("Ollama API Error ({$response->status()}): {$errorBody}");
    }

    protected function requestWithOptionalToken(?string $apiKey)
    {
        if (!empty($apiKey)) {
            return Http::withToken($apiKey);
        }

        return Http::acceptJson();
    }

    /**
     * Try to decode a structured JSON payload from raw model output.
     */
    protected function decodeStructuredPayload(mixed $payload): ?array
    {
        if (is_array($payload)) {
            return $payload;
        }

        if (!is_string($payload)) {
            return null;
        }

        $text = trim($payload);
        if ($text === '') {
            return null;
        }

        // Remove markdown code fences if present.
        if (str_starts_with($text, '```')) {
            $text = preg_replace('/^```(?:json)?\s*/i', '', $text) ?? $text;
            $text = preg_replace('/\s*```$/', '', $text) ?? $text;
            $text = trim($text);
        }

        $decoded = json_decode($text, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // Extract first JSON object/array from noisy text.
        if (preg_match('/(\{[\s\S]*\}|\[[\s\S]*\])/', $text, $match) === 1) {
            $decoded = json_decode($match[1], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }
}
