<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class ProductResearchService
{
    protected AgentService $agent;

    public function __construct(AgentService $agent)
    {
        $this->agent = $agent;
    }

    /**
     * Conduct product research based on initial analysis.
     * 
     * @param array $analysis Data returned from Gemini analysis
     * @return array
     */
    public function research(array $analysis, ?int $workspaceId = null): array
    {
        $productIdentity = md5(json_encode([
            $analysis['product_name_guess'] ?? 'unknown',
            $analysis['category'] ?? 'general',
            $analysis['subcategory'] ?? 'generic'
        ]));

        $cacheKey = "product_research_v2_{$productIdentity}";

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($analysis, $workspaceId) {
            Log::info("AI Research: Starting synthesized market research phase.");

            $instructions = "You are a professional market researcher and strategist. 
Based on the provided product analysis, synthesize market research data.";

            $prompt = $this->buildResearchPrompt($analysis);

            $outputFormat = '{
  "similar_products": ["Competitor A", "Competitor B"],
  "competitor_patterns": ["Pattern 1", "Pattern 2"],
  "price_range_estimate": "$XX - $YY",
  "product_positioning": "Strategic positioning statement",
  "market_keywords": ["key 1", "key 2"],
  "customer_intent_clues": ["clue 1", "clue 2"]
}';

            try {
                // Use text_generation role for research synthesis
                return $this->agent->generateDirect($prompt, $instructions, $outputFormat, null, 'text_generation', $workspaceId);
            } catch (Exception $e) {
                Log::error("AI Research: Synthesis Failed", ['error' => $e->getMessage()]);
                return $this->getFallbackResearch();
            }
        });
    }

    protected function buildResearchPrompt(array $analysis): string
    {
        return "Product Analysis Data:
" . json_encode($analysis, JSON_PRETTY_PRINT) . "

Identify:
1. Similar products and competitors.
2. Competitor marketing patterns (hooks, USPs).
3. Estimated price range.
4. Optimal product positioning.
5. High-intent market keywords.
6. Customer intent clues (buying triggers).";
    }

    protected function getFallbackResearch(): array
    {
        return [
            "similar_products" => [],
            "competitor_patterns" => ["Generic competition"],
            "price_range_estimate" => "Not available",
            "product_positioning" => "Standard market entry",
            "market_keywords" => [],
            "customer_intent_clues" => ["General interest"]
        ];
    }
}
