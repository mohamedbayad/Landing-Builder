<?php

namespace App\Services\AI\Providers;

use Illuminate\Support\Facades\Http;
use Exception;

class OpenAIProvider implements AIProviderInterface
{
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
            ->post('https://api.openai.com/v1/chat/completions', $payload);

        if ($response->failed()) {
            \Log::error('OpenAI API Request Failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new Exception("AI request failed: " . $response->body());
        }

        $responseData = $response->json();
        
        $content = $responseData['choices'][0]['message']['content'] ?? '{}';
        
        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("AI returned invalid JSON: " . json_last_error_msg());
        }

        return $decoded;
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
            ->post('https://api.openai.com/v1/images/generations', $payload);

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
}
