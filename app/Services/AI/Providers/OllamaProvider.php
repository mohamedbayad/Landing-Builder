<?php

namespace App\Services\AI\Providers;

use App\Services\OllamaService;
use Exception;
use Illuminate\Support\Facades\Log;

class OllamaProvider implements AIProviderInterface
{
    protected OllamaService $ollama;

    public function __construct()
    {
        $this->ollama = app(OllamaService::class);
    }

    public function setBaseUrl(string $url): self
    {
        $this->ollama->setBaseUrl($url);
        return $this;
    }

    /**
     * Generate structured JSON from Ollama
     */
    public function generate(string $prompt, string $model, string $apiKey, ?string $imageUrl = null, array $options = []): array
    {
        Log::info("OllamaProvider: Generating text for model {$model}");
        
        try {
            return $this->ollama->generateStructured(
                $prompt,
                array_merge($options, ['model' => $model, 'api_key' => $apiKey])
            );
        } catch (Exception $e) {
            Log::error("OllamaProvider: Generation failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate an image via Ollama (Not currently supported by Ollama itself)
     */
    public function generateImage(string $prompt, string $model, string $apiKey, array $options = []): string
    {
        throw new Exception("Ollama does not currently support image generation.");
    }
}
