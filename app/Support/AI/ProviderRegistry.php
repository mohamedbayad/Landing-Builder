<?php

namespace App\Support\AI;

final class ProviderRegistry
{
    public const OPENAI = 'openai';
    public const OPENROUTER = 'openrouter';
    public const ANTHROPIC = 'anthropic';
    public const GEMINI = 'gemini';
    public const CUSTOM = 'custom';

    public const OPENAI_DEFAULT_BASE_URL = 'https://api.openai.com/v1';
    public const OPENROUTER_DEFAULT_BASE_URL = 'https://openrouter.ai/api/v1';
    public const ANTHROPIC_DEFAULT_BASE_URL = 'https://api.anthropic.com/v1';
    public const GEMINI_DEFAULT_BASE_URL = 'https://generativelanguage.googleapis.com/v1beta';
    public const OLLAMA_FALLBACK_BASE_URL = 'https://ollama.com/api/tags';

    /**
     * Canonical provider definitions used by validation, UI, and runtime routing.
     */
    public static function definitions(): array
    {
        return [
            self::OPENAI => [
                'label' => 'OpenAI',
                'default_base_url' => self::OPENAI_DEFAULT_BASE_URL,
                'base_url_placeholder' => self::OPENAI_DEFAULT_BASE_URL,
                'api_key_placeholder' => 'sk-...',
                'autofill_base_url' => false,
                'openai_compatible' => true,
                'requires_api_key' => true,
                'helper_note' => null,
            ],
            self::OPENROUTER => [
                'label' => 'OpenRouter',
                'default_base_url' => self::OPENROUTER_DEFAULT_BASE_URL,
                'base_url_placeholder' => self::OPENROUTER_DEFAULT_BASE_URL,
                'api_key_placeholder' => 'sk-or-v1-...',
                'autofill_base_url' => true,
                'openai_compatible' => true,
                'requires_api_key' => true,
                'helper_note' => 'Use your OpenRouter API key and choose any supported model through OpenRouter.',
            ],
            self::ANTHROPIC => [
                'label' => 'Anthropic (Claude)',
                'default_base_url' => self::ANTHROPIC_DEFAULT_BASE_URL,
                'base_url_placeholder' => self::ANTHROPIC_DEFAULT_BASE_URL,
                'api_key_placeholder' => 'sk-ant-...',
                'autofill_base_url' => false,
                'openai_compatible' => false,
                'requires_api_key' => true,
                'helper_note' => null,
            ],
            self::GEMINI => [
                'label' => 'Google Gemini',
                'default_base_url' => self::GEMINI_DEFAULT_BASE_URL,
                'base_url_placeholder' => self::GEMINI_DEFAULT_BASE_URL,
                'api_key_placeholder' => 'AIza...',
                'autofill_base_url' => false,
                'openai_compatible' => false,
                'requires_api_key' => true,
                'helper_note' => null,
            ],
            self::CUSTOM => [
                'label' => 'Custom / Ollama',
                'default_base_url' => self::ollamaDefaultBaseUrl(),
                'base_url_placeholder' => self::OLLAMA_FALLBACK_BASE_URL,
                'api_key_placeholder' => 'Optional for Ollama',
                'autofill_base_url' => true,
                'openai_compatible' => false,
                'requires_api_key' => false,
                'helper_note' => null,
            ],
        ];
    }

    public static function optionsForSettingsForm(): array
    {
        return self::definitions();
    }

    public static function allowedProviderKeys(): array
    {
        return array_keys(self::definitions());
    }

    public static function labels(): array
    {
        $labels = [];
        foreach (self::definitions() as $key => $definition) {
            $labels[$key] = $definition['label'];
        }

        return $labels;
    }

    public static function labelFor(string $provider): string
    {
        $key = strtolower($provider);
        return self::definitions()[$key]['label'] ?? ucfirst($key);
    }

    public static function defaultBaseUrlFor(string $provider): ?string
    {
        $key = strtolower($provider);
        return self::definitions()[$key]['default_base_url'] ?? null;
    }

    public static function isOpenAICompatible(string $provider): bool
    {
        $key = strtolower($provider);
        return (bool) (self::definitions()[$key]['openai_compatible'] ?? false);
    }

    public static function requiresApiKey(string $provider): bool
    {
        $key = strtolower($provider);
        return (bool) (self::definitions()[$key]['requires_api_key'] ?? true);
    }

    public static function ollamaDefaultBaseUrl(): string
    {
        return self::normalizeOllamaBaseUrl((string) config('services.ollama.base_url', self::OLLAMA_FALLBACK_BASE_URL));
    }

    /**
     * Accepts either base URL or full Ollama endpoint URL and normalizes to base.
     * Examples:
     * - https://ollama.com/api/tags -> https://ollama.com
     * - https://ollama.com/api -> https://ollama.com
     * - https://host:11434 -> https://host:11434
     */
    public static function normalizeOllamaBaseUrl(?string $baseUrl): string
    {
        $normalized = trim((string) $baseUrl);
        if ($normalized === '') {
            $normalized = self::OLLAMA_FALLBACK_BASE_URL;
        }

        $normalized = rtrim($normalized, '/');
        $lower = strtolower($normalized);

        foreach (['/api/tags', '/api/generate', '/api/chat'] as $suffix) {
            if (str_ends_with($lower, $suffix)) {
                $normalized = substr($normalized, 0, -strlen($suffix));
                $lower = strtolower($normalized);
                break;
            }
        }

        if (str_ends_with($lower, '/api')) {
            $normalized = substr($normalized, 0, -4);
        }

        return rtrim($normalized, '/');
    }
}
