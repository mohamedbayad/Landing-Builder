<?php

namespace App\Services\AI\Resolvers;

class OllamaModelCapabilityResolver implements ModelCapabilityResolverInterface
{
    public function resolveCapabilities(string $modelId, array $rawModelData = []): array
    {
        // By default, assume Ollama models support text generation.
        // We can add logic here later to detect vision-capable models (like llava).
        
        $capabilities = [
            'supports_text_generation' => true,
            'supports_image_generation' => false,
            'supports_vision' => false,
            'supports_embeddings' => false, // Some support it but let's stick to text for copy
            'supports_audio' => false,
        ];

        // Specific detection for vision models
        if (str_contains(strtolower($modelId), 'llava') || str_contains(strtolower($modelId), 'vision')) {
            $capabilities['supports_vision'] = true;
        }

        return $capabilities;
    }
}
