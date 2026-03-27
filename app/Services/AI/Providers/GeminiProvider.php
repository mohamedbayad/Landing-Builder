<?php

namespace App\Services\AI\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GeminiProvider implements AIProviderInterface
{
    /**
     * Entry point for generation
     */
    public function generate(string $prompt, string $model, string $apiKey, ?string $imageUrl = null, array $options = []): array
    {
        try {
            $url = $this->buildApiUrl($model, $apiKey);
            $payload = $this->buildGenerationPayload($prompt, $imageUrl, $options);

            $this->logPayloadAudit($model, $payload);

            $response = Http::timeout(600)->post($url, $payload);

            if ($response->failed()) {
                $this->handleApiError($response);
            }

            return $this->parseJsonResponse($response->json(), $model);

        } catch (Exception $e) {
            Log::error("GeminiProvider: Exception during generation", [
                'model' => $model,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected function buildApiUrl(string $model, string $apiKey): string
    {
        return "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
    }

    /**
     * Build the multimodal structured payload
     */
    protected function buildGenerationPayload(string $prompt, ?string $imageUrl = null, array $options = []): array
    {
        $parts = [];
        
        // 1. Instruction Part (Text)
        $instructionPrompt = $prompt . "\n\nIMPORTANT: Return valid JSON ONLY. Do not include markdown code blocks, no intro, no outro, and no explanation.";
        $parts[] = ['text' => $instructionPrompt];

        // 2. Media Part (Image) if present
        if ($imageUrl) {
            $parts[] = $this->buildImagePart($imageUrl);
        }
// ///////////////////.  
        return [
            'contents' => [
                [
                    'parts' => $parts
                ]
            ],
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.7,
            ]
        ];
    }

    protected function buildImagePart(string $imageUrl): array
    {
        if (file_exists($imageUrl)) {
            $imageData = base64_encode(file_get_contents($imageUrl));
            $mimeType = mime_content_type($imageUrl);
        } else {
            try {
                // Assuming it's a URL reachable via file_get_contents or we might use Http::get here
                $imageData = base64_encode(file_get_contents($imageUrl));
                $mimeType = 'image/jpeg'; // Default fallback
            } catch (Exception $e) {
                Log::error("GeminiProvider: Failed to load image from {$imageUrl}");
                throw new Exception("Failed to load image for multimodal processing.");
            }
        }

        return [
            'inline_data' => [
                'mime_type' => $mimeType,
                'data' => $imageData
            ]
        ];
    }

    /**
     * Parse and clean the JSON response
     */
    protected function parseJsonResponse(array $responseData, string $model): array
    {
        $content = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
        
        $cleanedContent = $this->cleanJson($content);
        $decoded = json_decode($cleanedContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("GeminiProvider: JSON Decode Error", [
                'model' => $model,
                'json_error' => json_last_error_msg(),
                'raw_preview' => substr($cleanedContent, 0, 500)
            ]);
            
            // Return a structured error fallback instead of crashing
            return [
                'error' => 'Invalid JSON returned from AI',
                'raw_content' => $content,
                'success' => false
            ];
        }

        return $decoded;
    }

    /**
     * Strip markdown code blocks and noise
     */
    protected function cleanJson(string $json): string
    {
        $json = trim($json);
        
        // Match the largest outer JSON structure ({...} or [...])
        // This handles cases where the AI includes a preamble or markdown fences.
        $firstCurly = strpos($json, '{');
        $lastCurly = strrpos($json, '}');
        
        $firstSquare = strpos($json, '[');
        $lastSquare = strrpos($json, ']');

        // Determine which one starts and ends effectively
        $start = false;
        $end = false;

        if ($firstCurly !== false && ($firstSquare === false || $firstCurly < $firstSquare)) {
            $start = $firstCurly;
            $end = $lastCurly;
        } elseif ($firstSquare !== false) {
            $start = $firstSquare;
            $end = $lastSquare;
        }

        if ($start !== false && $end !== false && $end > $start) {
            return substr($json, $start, $end - $start + 1);
        }
        
        // Fallback for markdown blocks if indices are weird
        if (preg_match('/```(?:json)?\s*(.*?)\s*```/is', $json, $matches)) {
            return trim($matches[1]);
        }
        
        return $json;
    }

    /**
     * Log structured payload audit without the massive base64 chunk
     */
    protected function logPayloadAudit(string $model, array $payload): void
    {
        $sanitizedPayload = $payload;
        // Strip out binary data for logging
        if (isset($sanitizedPayload['contents'][0]['parts'])) {
            foreach ($sanitizedPayload['contents'][0]['parts'] as &$part) {
                if (isset($part['inline_data'])) {
                    $part['inline_data']['data'] = '[REDACTED BINARY DATA (' . strlen($part['inline_data']['data']) . ' characters)]';
                }
            }
        }

        Log::info("GeminiProvider: Outgoing Request Audit", [
            'model' => $model,
            'payload_structure' => $sanitizedPayload
        ]);
    }

    protected function handleApiError($response): void
    {
        $error = $response->json('error.message') ?? $response->body();
        Log::error('GeminiProvider: API Error', [
            'status' => $response->status(),
            'message' => $error
        ]);
        throw new Exception("Gemini AI API Error: {$error}");
    }

    /**
     * Generate an image using Gemini's native image generation capability.
     * Uses the generateContent endpoint with responseModalities: ["Image"].
     * Returns a data URL string (data:image/png;base64,...) for downstream persistence.
     */
    public function generateImage(string $prompt, string $model, string $apiKey, array $options = []): string
    {
        Log::info("GeminiProvider: Starting image generation", [
            'model' => $model,
            'prompt_preview' => substr($prompt, 0, 150)
        ]);

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'responseModalities' => ['Image'],
            ]
        ];

        try {
            $response = Http::timeout(120)->post($url, $payload);

            if ($response->failed()) {
                $errorBody = $response->json('error.message') ?? $response->body();
                Log::error("GeminiProvider: Image generation API error", [
                    'status' => $response->status(),
                    'error' => $errorBody
                ]);
                throw new Exception("Gemini image generation failed: " . $errorBody);
            }

            $responseData = $response->json();

            // Parse the response: look for inline_data in parts
            $candidates = $responseData['candidates'] ?? [];
            if (empty($candidates)) {
                Log::error("GeminiProvider: No candidates in image generation response", [
                    'response_keys' => array_keys($responseData)
                ]);
                throw new Exception("Gemini returned no candidates for image generation.");
            }

            $parts = $candidates[0]['content']['parts'] ?? [];
            
            // Find the image part (inline_data)
            $imageData = null;
            $mimeType = 'image/png';

            foreach ($parts as $part) {
                if (isset($part['inlineData'])) {
                    $imageData = $part['inlineData']['data'] ?? null;
                    $mimeType = $part['inlineData']['mimeType'] ?? 'image/png';
                    break;
                }
                // Also check snake_case variant
                if (isset($part['inline_data'])) {
                    $imageData = $part['inline_data']['data'] ?? null;
                    $mimeType = $part['inline_data']['mimeType'] ?? $part['inline_data']['mime_type'] ?? 'image/png';
                    break;
                }
            }

            if (!$imageData) {
                Log::error("GeminiProvider: No inline image data found in response", [
                    'parts_count' => count($parts),
                    'part_keys' => !empty($parts) ? array_keys($parts[0]) : 'empty'
                ]);
                throw new Exception("Gemini did not return image data in the response.");
            }

            Log::info("GeminiProvider: Image generated successfully", [
                'mime_type' => $mimeType,
                'base64_length' => strlen($imageData)
            ]);

            // Return as a data URL so GeneratedImageStorageService can detect and persist it
            return "data:{$mimeType};base64,{$imageData}";

        } catch (Exception $e) {
            Log::error("GeminiProvider: Image generation exception", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
