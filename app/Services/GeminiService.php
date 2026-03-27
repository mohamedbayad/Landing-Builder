<?php

namespace App\Services;

use App\Services\AI\Providers\GeminiProvider;
use Illuminate\Support\Facades\Log;
use Exception;

class GeminiService
{
    protected GeminiProvider $provider;
    protected ?string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->provider = new GeminiProvider();
        $this->apiKey = config('services.gemini.key');
        $this->model = config('services.gemini.model', 'gemini-2.0-flash');
    }

    /**
     * Specialized method for image analysis to maintain backward compatibility
     */
    public function analyzeImage(string $imagePath): array
    {
        $prompt = "Analyze this product image in detail. Extract niche, target audience, features, and tone.";
        
        return $this->provider->generate($prompt, $this->model, $this->apiKey, $imagePath, [
            'temperature' => 0.4
        ]);
    }

    /**
     * Direct generation
     */
    public function generate(string $prompt, ?string $imageUrl = null): array
    {
        return $this->provider->generate($prompt, $this->model, $this->apiKey, $imageUrl);
    }
}
