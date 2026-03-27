<?php

namespace App\Services\AI\Resolvers;

class GeminiModelCapabilityResolver implements ModelCapabilityResolverInterface
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

        // Specific media type detection
        if (str_contains($id, 'embedding')) {
            $capabilities['supports_embeddings'] = true;
        } elseif (str_contains($id, 'image')) {
            // Models like gemini-2.5-flash-image
            $capabilities['supports_image_generation'] = true;
            // Depending on Google's API, an image model might also support text prompted generation,
            // but usually a specifically named "image" model is for image output.
            // If they are multimodal in output, we can enable text generation too. 
            // We will default to both, just in case, but prioritize image.
            $capabilities['supports_text_generation'] = true; 
        } elseif (str_contains($id, 'audio') || str_contains($id, 'tts')) {
            $capabilities['supports_audio'] = true;
        } elseif (str_contains($id, 'video')) {
            $capabilities['supports_vision'] = true; // or video generation if you add that capability later
        } else {
            // Standard models (gemini-1.5-pro, gemini-1.5-flash, etc)
            $capabilities['supports_text_generation'] = str_contains($id, 'gemini') || str_contains($id, 'flash') || str_contains($id, 'pro');
        }

        // Multimodal input capabilities (Vision)
        // Gemini 1.5, 2.0, vision models generally support image input
        if (str_contains($id, 'vision') || str_contains($id, '1.5') || str_contains($id, '2.0') || str_contains($id, '2.5')) {
            // Embeddings typically don't act as a standard vision model for generation
            if (!$capabilities['supports_embeddings'] && !$capabilities['supports_audio']) {
                 $capabilities['supports_vision'] = true;
            }
        }

        // Ensure at least text is true if it's a generic "gemini" without specific other flags
        if (str_contains($id, 'gemini') && 
            !$capabilities['supports_image_generation'] && 
            !$capabilities['supports_embeddings'] && 
            !$capabilities['supports_audio'] &&
            !$capabilities['supports_text_generation']) {
                $capabilities['supports_text_generation'] = true;
        }

        return $capabilities;
    }
}
