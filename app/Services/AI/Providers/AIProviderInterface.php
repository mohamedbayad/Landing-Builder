<?php

namespace App\Services\AI\Providers;

interface AIProviderInterface
{
    /**
     * Generate a response from the AI provider
     *
     * @param string $prompt The assembled text prompt
     * @param string $model The model string identifier
     * @param string $apiKey The API Key for authorization
     * @param string|null $imageUrl Optional image URL for vision context
     * @param array $options Additional configuration
     * @return array The structured JSON response parsed into an array
     */
    public function generate(string $prompt, string $model, string $apiKey, ?string $imageUrl = null, array $options = []): array;
    /**
     * Generate an image from the AI provider
     *
     * @param string $prompt The descriptive image prompt
     * @param string $model The model string identifier (e.g., dall-e-3)
     * @param string $apiKey The API Key for authorization
     * @param array $options Additional configuration (e.g., size, quality)
     * @return string The URL of the generated image
     */
    public function generateImage(string $prompt, string $model, string $apiKey, array $options = []): string;
}
