<?php

namespace App\Services\AI\Resolvers;

class OpenAIModelCapabilityResolver implements ModelCapabilityResolverInterface
{
    public function resolveCapabilities(string $modelId, array $rawMetadata = []): array
    {
        $id = strtolower($modelId);
        
        $capabilities = [
            'supports_text_generation' => false,
            'supports_image_generation' => false,
            'supports_vision' => false,
            'supports_embeddings' => false,
            'supports_audio' => false,
        ];

        if (str_contains($id, 'dall-e')) {
            $capabilities['supports_image_generation'] = true;
        } elseif (str_contains($id, 'embedding')) {
            $capabilities['supports_embeddings'] = true;
        } elseif (str_contains($id, 'whisper') || str_contains($id, 'tts')) {
            $capabilities['supports_audio'] = true;
        } else {
            // Standard text models (gpt-4, gpt-3.5, o1, etc)
            $capabilities['supports_text_generation'] = true;
        }

        // Vision capability
        if (str_contains($id, 'vision') || str_contains($id, 'gpt-4o')) {
             // Audio models strictly aren't vision models
             if (!$capabilities['supports_audio'] && !$capabilities['supports_embeddings']) {
                 $capabilities['supports_vision'] = true;
             }
        }

        return $capabilities;
    }
}
