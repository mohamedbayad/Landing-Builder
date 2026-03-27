<?php

namespace App\Services\AI\Resolvers;

class AnthropicModelCapabilityResolver implements ModelCapabilityResolverInterface
{
    public function resolveCapabilities(string $modelId, array $rawMetadata = []): array
    {
        $id = strtolower($modelId);
        
        $capabilities = [
            'supports_text_generation' => true, // Anthropic models are almost universally text generation
            'supports_image_generation' => false,
            'supports_vision' => false,
            'supports_embeddings' => false,
            'supports_audio' => false,
        ];

        // Claude 3 and 3.5 support vision
        if (str_contains($id, 'claude-3')) {
            $capabilities['supports_vision'] = true;
        }

        return $capabilities;
    }
}
