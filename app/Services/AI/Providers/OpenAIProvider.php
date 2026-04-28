<?php

namespace App\Services\AI\Providers;

use Illuminate\Support\Facades\Http;
use Exception;

class OpenAIProvider implements AIProviderInterface
{
    protected string $baseUrl;

    public function __construct(?string $baseUrl = null)
    {
        $this->baseUrl = $this->normalizeBaseUrl($baseUrl);
    }

    public function setBaseUrl(?string $baseUrl): self
    {
        $this->baseUrl = $this->normalizeBaseUrl($baseUrl);
        return $this;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function generate(string $prompt, string $model, string $apiKey, ?string $imageUrl = null, array $options = []): array
    {
        $messages = [];

        // Build the User message. If there's an image, use the array content format
        if ($imageUrl) {
            $messages[] = [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $prompt
                    ],
                    [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => $imageUrl
                        ]
                    ]
                ]
            ];
        } else {
            $messages[] = [
                'role' => 'user',
                'content' => $prompt
            ];
        }

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'response_format' => ['type' => 'json_object'], // Enforce JSON for structured app data
            'temperature' => $options['temperature'] ?? 0.7,
        ];

        $response = Http::withToken($apiKey)
            ->timeout(120) // LLM Generation might take time
            ->post($this->buildEndpoint('chat/completions'), $payload);

        if ($response->failed()) {
            \Log::error('OpenAI API Request Failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new Exception("AI request failed: " . $response->body());
        }

        $responseData = $response->json();
        $content = $responseData['choices'][0]['message']['content'] ?? '{}';

        $decoded = $this->decodeStructuredPayload($content);
        if (is_array($decoded)) {
            return $decoded;
        }

        \Log::error('OpenAI/OpenRouter JSON decode failed', [
            'model' => $model,
            'base_url' => $this->baseUrl,
            'content_type' => gettype($content),
            'content_preview' => mb_substr(is_string($content) ? $content : json_encode($content), 0, 600),
        ]);

        throw new Exception("AI returned invalid JSON: " . json_last_error_msg());
    }
    public function generateImage(string $prompt, string $model, string $apiKey, array $options = []): string
    {
        $payload = [
            'model' => $model,
            'prompt' => $prompt,
            'n' => 1,
            'size' => $options['size'] ?? '1024x1024',
            'quality' => $options['quality'] ?? 'standard',
        ];

        $response = Http::withToken($apiKey)
            ->timeout(120) // Image generation takes time
            ->post($this->buildEndpoint('images/generations'), $payload);

        if ($response->failed()) {
            \Log::error('OpenAI Image Generation API Request Failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new Exception("OpenAI Image generation failed: " . $response->body());
        }

        $responseData = $response->json();
        
        $imageUrl = $responseData['data'][0]['url'] ?? null;

        if (!$imageUrl) {
            throw new Exception("OpenAI did not return a valid image URL in the response.");
        }

        return $imageUrl;
    }

    protected function buildEndpoint(string $path): string
    {
        return rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');
    }

    protected function normalizeBaseUrl(?string $baseUrl): string
    {
        $normalized = trim((string) $baseUrl);
        if ($normalized === '') {
            $normalized = 'https://api.openai.com/v1';
        }

        $normalized = rtrim($normalized, '/');
        $lower = strtolower($normalized);

        foreach (['/chat/completions', '/images/generations', '/models'] as $suffix) {
            if (str_ends_with($lower, $suffix)) {
                $normalized = substr($normalized, 0, -strlen($suffix));
                break;
            }
        }

        return rtrim($normalized, '/');
    }

    /**
     * Decode structured payload with common LLM-output recovery strategies.
     */
    protected function decodeStructuredPayload(mixed $payload): ?array
    {
        if (is_array($payload)) {
            // OpenAI responses may return content parts array; extract text chunks.
            $textParts = [];
            foreach ($payload as $part) {
                if (is_array($part) && isset($part['text']) && is_string($part['text'])) {
                    $textParts[] = $part['text'];
                } elseif (is_string($part)) {
                    $textParts[] = $part;
                }
            }
            $payload = implode("\n", $textParts);
        }

        if (!is_string($payload)) {
            return null;
        }

        $attempts = [];
        $text = trim($payload);
        $attempts[] = $text;

        // Remove markdown code fences.
        if (str_starts_with($text, '```')) {
            $noFence = preg_replace('/^```(?:json)?\s*/i', '', $text) ?? $text;
            $noFence = preg_replace('/\s*```$/', '', $noFence) ?? $noFence;
            $attempts[] = trim($noFence);
        }

        // Extract first JSON object/array from noisy text.
        if (preg_match('/(\{[\s\S]*\}|\[[\s\S]*\])/', $text, $match) === 1) {
            $attempts[] = trim($match[1]);
        }

        // Sanitize control chars that often break json_decode.
        $sanitized = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', ' ', $text) ?? $text;
        $attempts[] = $sanitized;
        $attempts[] = str_replace(["\r", "\n", "\t"], ' ', $sanitized);

        foreach ($attempts as $candidate) {
            if (!is_string($candidate) || trim($candidate) === '') {
                continue;
            }

            $decoded = json_decode($candidate, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }
}
