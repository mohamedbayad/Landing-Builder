<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class ImageGenerationService
{
    protected $agentService;
    protected $storageService;

    public function __construct(AgentService $agentService, GeneratedImageStorageService $storageService)
    {
        $this->agentService = $agentService;
        $this->storageService = $storageService;
    }

    /**
     * Generate an image for a specific section context with visual grounding
     */
    public function generateForSection(string $sectionType, string $productName, string $context, array $analysis = [], ?int $workspaceId = null): string
    {
        Log::info("Generating grounded image for section '{$sectionType}' ({$productName})");

        // Extract visual traits for the prompt
        $visualTraits = "";
        if (!empty($analysis)) {
            $visualTraits = sprintf(
                "Product Visual Details: Material: %s, Colors: %s, Style: %s, Features: %s.",
                $analysis['material'] ?? 'standard',
                $analysis['color'] ?? 'neutral',
                $analysis['style'] ?? 'modern',
                implode(', ', $analysis['visual_features'] ?? [])
            );
        }

        // 1. Generate a high-quality Image Prompt first
        $promptInstructions = "You are a prompt engineering expert for AI image generators.
        Write a highly detailed, photorealistic commercial prompt for a '{$sectionType}' section.
        Product: {$productName}
        {$visualTraits}
        Section Content Context: {$context}
        
        CRITICAL: The image MUST represent the EXACT product described. Do not change the colors or materials.
        The prompt should be in English, focusing on professional photography, studio lighting, and premium commercial aesthetics.";

        try {
            $promptResponse = $this->agentService->generateDirect($promptInstructions, "Generate a single direct image prompt string.", '', null, 'text_generation', $workspaceId);
            $imagePrompt = $promptResponse['prompt'] ?? (is_string($promptResponse) ? $promptResponse : json_encode($promptResponse));

            Log::info("DEBUG [ImageGenerationService]: Prompting for section asset", [
                'section' => $sectionType,
                'prompt_preview' => substr($imagePrompt, 0, 150) . '...'
            ]);

            // 2. Call the real Image Provider via AgentService
            $imageUrl = $this->agentService->generateImage($imagePrompt, [], $workspaceId);

            Log::info("DEBUG [ImageGenerationService]: Provider Response", [
                'provider_url' => $imageUrl,
                'section'      => $sectionType
            ]);

            // 3. PERSIST the image locally
            $requestedFilename = sprintf('%s-%s', Str::slug($sectionType), Str::slug($productName));
            $persistence = $this->storageService->saveGeneratedImage($imageUrl, $requestedFilename);

            if ($persistence['success']) {
                $finalUrl = $persistence['url'];
                
                // FINAL VERIFICATION: Check if the file actually exists on disk
                if (isset($persistence['absolute_path']) && !file_exists($persistence['absolute_path'])) {
                    Log::error("STORAGE MISMATCH: File saved successfully but missing on disk!", [
                        'path' => $persistence['absolute_path'],
                        'url' => $finalUrl
                    ]);
                    $finalUrl = "https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=1000&auto=format&fit=crop";
                } else {
                    Log::info("Image persisted and verified: {$finalUrl}");
                }
            } else {
                Log::warning("Persistence failed for section {$sectionType}. Investigating source type.");
                
                // Fallback Logic: Only use source if it's a valid remote URL
                if (is_string($imageUrl) && str_starts_with($imageUrl, 'http')) {
                    Log::info("Fallback: Source is a remote URL, using as fallback.", ['url' => $imageUrl]);
                    $finalUrl = $imageUrl;
                } else {
                    Log::error("Fallback: Source is NOT a URL (Base64 or Error). Using curated placeholder.", [
                        'type' => gettype($imageUrl),
                        'preview' => is_string($imageUrl) ? substr($imageUrl, 0, 50) : 'n/a'
                    ]);
                    $finalUrl = "https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=1000&auto=format&fit=crop";
                }
            }

            Log::info("Image mapped to {$sectionType} section", ['final_url' => $finalUrl]);
            return $finalUrl;

        } catch (Exception $e) {
            Log::error("Image Generation Pipeline Failed: " . $e->getMessage());
            
            // Graceful degradation: Fallback to high-quality curated placeholder if the API fails
            // This prevents broken img src tags from ruining the layout
            Log::warning("Falling back to placeholder image for {$sectionType}");
            return "https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=1000&auto=format&fit=crop";
        }
    }
}
