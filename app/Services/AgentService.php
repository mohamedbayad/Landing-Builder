<?php

namespace App\Services;

use App\Services\AI\Providers\AIProviderInterface;
use App\Services\AI\Providers\OpenAIProvider;
use App\Services\AI\Providers\AnthropicProvider;
use App\Services\AI\Providers\GeminiProvider;
use App\Services\AIModelRoleResolverService;
use Exception;
use Illuminate\Support\Facades\Log;

class AgentService
{
    protected $promptBuilder;
    protected $roleResolver;

    public function __construct(PromptBuilderService $promptBuilder, AIModelRoleResolverService $roleResolver)
    {
        $this->promptBuilder = $promptBuilder;
        $this->roleResolver = $roleResolver;
    }

    /**
     * Resolve the active provider class based on the name
     */
    protected function resolveProvider(string $providerName, ?string $baseUrl = null): AIProviderInterface
    {
        // Future proofing: Pass baseUrl to providers if they implement custom endpoints
        return match (strtolower($providerName)) {
            'openai' => new OpenAIProvider(),
            'anthropic', 'claude' => new AnthropicProvider(),
            'gemini', 'google' => new GeminiProvider(),
            'custom', 'ollama' => $this->createOllamaProvider($baseUrl),
            default => throw new Exception("Unsupported AI provider configured: {$providerName}"),
        };
    }

    protected function createOllamaProvider(?string $baseUrl): \App\Services\AI\Providers\OllamaProvider
    {
        $provider = new \App\Services\AI\Providers\OllamaProvider();
        if ($baseUrl) {
            $provider->setBaseUrl($baseUrl);
        }
        return $provider;
    }

    /**
     * Resolve the AI Configuration based on a specific role
     */
    protected function resolveRoleConfiguration(string $role, ?int $workspaceId = null): array
    {
        return $this->roleResolver->resolveByRole($role, $workspaceId);
    }

    /**
     * Generate a complete landing page JSON structure
     */
    public function generateLandingPage(array $data, ?string $imageUrl = null, ?int $workspaceId = null): array
    {
        $config = $this->resolveRoleConfiguration('text_generation', $workspaceId);
        $providerInstance = $this->resolveProvider($config['provider'], $config['baseUrl']);

        $instructions = "You are an expert AI conversion copywriter and landing page designer. 
        IMPORTANT: Your final output MUST be a valid JSON object containing both a 'sections' array and an 'seo' object.
        1. 'seo': MUST contain 'title', 'description', and 'schema' (a valid Schema.org Product JSON-LD string).
        2. 'sections': Each section MUST have 'id', 'type', and 'html' (string containing the full renderable HTML for that section).
        You may include other metadata like 'product_understanding' or 'seo_plan' for context, but 'sections' and 'seo' are mandatory.";
        
        $outputFormat = '{
  "seo": {
    "title": "Optimized Page Title",
    "description": "Engaging meta description",
    "schema": "<script type=\"application/ld+json\">{\"@context\": \"https://schema.org\", \"@type\": \"Product\"}</script>"
  },
  "sections": [
    {
      "id": "hero",
      "type": "hero",
      "html": "<section class=\'hero\'>...</section>"
    }
  ]
}';
        
        // Build the compiled prompt using the loaders
        $prompt = $this->promptBuilder->buildLandingPagePrompt($data, $instructions, $outputFormat);

        Log::info("Generating Landing Page via {$config['provider']} ({$config['model']})");
        Log::info("Prompt length: " . strlen($prompt) . " characters");

        $startTime = microtime(true);
        $result = $providerInstance->generate($prompt, $config['model'], $config['apiKey'], $imageUrl);
        $duration = microtime(true) - $startTime;

        Log::info("Generation completed in " . round($duration, 2) . " seconds");

        return $result;
    }

    /**
     * Regenerate a specific section
     */
    public function regenerateSection(string $sectionName, array $contextData, ?string $imageUrl = null, ?int $workspaceId = null): array
    {
        $config = $this->resolveRoleConfiguration('text_generation', $workspaceId);
        $providerInstance = $this->resolveProvider($config['provider'], $config['baseUrl']);

        $instructions = "You are focused solely on regenerating a single specific section of an ongoing landing page while maintaining the global context. Return the updated JSON representation of only the requested section.";

        $prompt = $this->promptBuilder->buildRegenerationPrompt($sectionName, $contextData, $instructions);

        Log::info("Regenerating section '{$sectionName}' via {$config['provider']} ({$config['model']})");

        return $providerInstance->generate($prompt, $config['model'], $config['apiKey'], $imageUrl);
    }

    /**
     * Regenerate a specific granular element (headline, button, etc.)
     */
    public function regenerateElement(string $elementId, string $type, array $contextData, ?string $comment = null, ?int $workspaceId = null): array
    {
        $config = $this->resolveRoleConfiguration('text_generation', $workspaceId);
        $providerInstance = $this->resolveProvider($config['provider'], $config['baseUrl']);

        $instructions = "You are an expert editor. You must regenerate ONLY the content for the element with ID: {$elementId} (Type: {$type}).";
        if ($comment) {
            $instructions .= "\nUSER SPECIFIC INSTRUCTION: {$comment}";
        }
        $instructions .= "\nReturn the updated JSON only for this element.";

        $prompt = $this->promptBuilder->buildRegenerationPrompt($elementId, $contextData, $instructions);

        Log::info("Regenerating element '{$elementId}' via {$config['provider']}");

        return $providerInstance->generate($prompt, $config['model'], $config['apiKey']);
    }

    /**
     * Direct generation for strategic tasks (Research, Image Prompts, etc.)
     * This now includes global context (skills/workflows) by default.
     */
    public function generateDirect(string $prompt, string $instructions = '', string $outputFormat = '', ?string $imageUrl = null, string $role = 'text_generation', ?int $workspaceId = null): array
    {
        $config = $this->resolveRoleConfiguration($role, $workspaceId);
        $providerInstance = $this->resolveProvider($config['provider'], $config['baseUrl']);

        // Load Global Context (Skills, Workflows, Prompts) based on role
        $globalContext = $this->promptBuilder->buildBaseContext($role === 'vision' ? 'vision' : 'landing-page');

        $fullPrompt = $globalContext . "\n\n";
        
        if (!empty($instructions)) {
            $fullPrompt .= "=== TASK INSTRUCTIONS ===\n" . $instructions . "\n\n";
        }
        
        $fullPrompt .= "=== SPECIFIC INPUT/PROMPT ===\n" . $prompt;
        
        if ($outputFormat) {
            $fullPrompt .= "\n\n=== REQUIRED OUTPUT FORMAT (JSON) ===\n" . $outputFormat;
        }

        return $providerInstance->generate($fullPrompt, $config['model'], $config['apiKey'], $imageUrl);
    }

    /**
     * Generate an image via the configured AI provider
     */
    public function generateImage(string $prompt, array $options = [], ?int $workspaceId = null): string
    {
        // Hardcode role requirement to image_generation
        $config = $this->resolveRoleConfiguration('image_generation', $workspaceId);
        
        $model = $options['model'] ?? $config['model']; // Favor the role-resolved model if none specified
        $providerInstance = $this->resolveProvider($config['provider'], $config['baseUrl']);

        Log::info("Requesting image generation from {$config['provider']} (Model: {$model})");

        return $providerInstance->generateImage($prompt, $model, $config['apiKey'], $options);
    }
}
