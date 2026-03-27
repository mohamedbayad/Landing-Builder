<?php

namespace App\Services\AI\Resolvers;

interface ModelCapabilityResolverInterface
{
    /**
     * Resolve the capabilities of a specific AI model.
     *
     * @param string $modelId The ID or name of the model.
     * @param array $rawMetadata Any raw metadata returned by the provider API.
     * @return array Associative array of capability flags (e.g., 'supports_text_generation' => true).
     */
    public function resolveCapabilities(string $modelId, array $rawMetadata = []): array;
}
