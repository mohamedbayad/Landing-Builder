<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AISettingsController extends Controller
{
    /**
     * Test the API key against the provider and return available models.
     */
    public function testConnection(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|string',
            'api_key' => 'nullable|string', // Optional for Ollama
            'base_url' => 'nullable|url',
        ]);

        $provider = strtolower($validated['provider']);
        $apiKey = $validated['api_key'];
        
        $models = [];

        try {
            if ($provider === 'openai') {
                $response = Http::withToken($apiKey)->get('https://api.openai.com/v1/models');
                
                if ($response->failed()) {
                    throw new \Exception($response->json('error.message') ?? 'Invalid API key or connection error.');
                }

                // Filter down to chat models
                $allModels = $response->json('data') ?? [];
                foreach ($allModels as $m) {
                    if (str_contains($m['id'], 'gpt')) {
                        $models[] = [
                            'id' => $m['id'],
                            'name' => $m['id']
                        ];
                    }
                }
            } elseif ($provider === 'anthropic' || $provider === 'claude') {
                // Anthropic doesn't have a direct /models listing endpoint right now that is universally accessible without proper headers in the same way,
                // so we hardcode a valid test request or a predefined models list if the test passes.
                $response = Http::withHeaders([
                    'x-api-key' => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => '
                    
                    application/json'
                ])->post('https://api.anthropic.com/v1/messages', [
                    'model' => 'claude-3-haiku-20240307',
                    'max_tokens' => 1,
                    'messages' => [['role' => 'user', 'content' => 'test']]
                ]);

                if ($response->status() === 401) {
                    throw new \Exception('Invalid Anthropic API key.');
                } elseif ($response->status() >= 500) {
                     throw new \Exception('Anthropic server error.');
                }

                // If auth passes or gives a non-auth error (like empty message), standard lists are:
                $models = [
                    ['id' => 'claude-3-5-sonnet-20240620', 'name' => 'Claude 3.5 Sonnet'],
                    ['id' => 'claude-3-opus-20240229', 'name' => 'Claude 3 Opus'],
                    ['id' => 'claude-3-sonnet-20240229', 'name' => 'Claude 3 Sonnet'],
                    ['id' => 'claude-3-haiku-20240307', 'name' => 'Claude 3 Haiku']
                ];

            }  elseif ($provider === 'gemini') {
                 $response = Http::get("https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}");
                 
                 if ($response->failed()) {
                    throw new \Exception($response->json('error.message') ?? 'Invalid Gemini API key.');
                 }

                 $allModels = $response->json('models') ?? [];
                 foreach ($allModels as $m) {
                     if (str_contains($m['name'], 'gemini')) {
                         $id = str_replace('models/', '', $m['name']);
                         $models[] = [
                             'id' => $id,
                             'name' => $m['displayName'] ?? $id
                         ];
                     }
                 }
            } elseif ($provider === 'ollama' || $provider === 'custom') {
                $baseUrl = rtrim($request->base_url ?? 'http://localhost:11434', '/');
                $response = Http::timeout(5)->get("{$baseUrl}/api/tags");

                if ($response->failed()) {
                    throw new \Exception("Could not connect to Ollama at {$baseUrl}.");
                }

                $allModels = $response->json('models') ?? [];
                foreach ($allModels as $m) {
                    $models[] = [
                        'id' => $m['model'] ?? $m['name'],
                        'name' => $m['name'] ?? $m['model']
                    ];
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unsupported AI provider.'
                ], 400);
            }

            // Sort models alphabetically for UX
            usort($models, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            return response()->json([
                'success' => true,
                'message' => 'Connection successful!',
                'models' => $models
            ]);

        } catch (\Exception $e) {
            Log::error('AI Settings Test Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
