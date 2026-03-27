<?php

namespace App\Services;

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
        $this->baseUrl = rtrim(config('services.ollama.base_url', 'http://localhost:11434'), '/');
        $this->model = config('services.ollama.model', 'llama3');
    }

    public function setBaseUrl(string $url): self
    {
        $this->baseUrl = rtrim($url, '/');
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
        
        Log::info("Ollama: Generating text", ['model' => $model, 'url' => $url]);

        $response = Http::timeout(240)->post($url, [
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

        Log::info("Ollama: Generating structured JSON", ['model' => $model, 'url' => $url]);

        $response = Http::timeout(300)->post($url, [
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

        $contentText = $response->json('response');
        $decoded = json_decode($contentText, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("Ollama: JSON Decoding Failed", ['content' => $contentText]);
            throw new Exception("Ollama returned invalid JSON: " . json_last_error_msg());
        }

        return $decoded;
    }

    /**
     * Start/continue a chat conversation
     */
    public function chat(array $messages, array $options = []): array
    {
        $url = "{$this->baseUrl}/api/chat";
        $model = $options['model'] ?? $this->model;

        Log::info("Ollama: Chat request", ['model' => $model, 'url' => $url]);

        $response = Http::timeout(240)->post($url, [
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
}
