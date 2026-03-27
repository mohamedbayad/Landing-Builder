<?php

namespace App\Services\AI\Providers;

use Illuminate\Support\Facades\Http;
use Exception;

class AnthropicProvider implements AIProviderInterface
{
    public function generate(string $prompt, string $model, string $apiKey, ?string $imageUrl = null, array $options = []): array
    {
        // Scaffold for Claude 3 integration
        $payload = [
            'model' => $model,
            'max_tokens' => $options['max_tokens'] ?? 4000,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ];

        // Ensure this works with Anthropic's specific image input format later if needed

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json'
        ])
        ->timeout(120)
        ->post('https://api.anthropic.com/v1/messages', $payload);

        if ($response->failed()) {
            \Log::error('Anthropic API Request Failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new Exception("AI request failed (Anthropic): " . $response->body());
        }

        $responseData = $response->json();
        $content = $responseData['content'][0]['text'] ?? '{}';
        
        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("AI returned invalid JSON: " . json_last_error_msg());
        }

        return $decoded;
    }
    public function generateImage(string $prompt, string $model, string $apiKey, array $options = []): string
    {
        throw new Exception("Anthropic does not currently support direct image generation via this interface.");
    }
}
