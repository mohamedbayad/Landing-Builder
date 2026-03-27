<?php

namespace App\Services;

use App\Services\AgentService;
use App\Services\AIModelRoleResolverService;
use App\Services\ProductResearchService;
use App\Services\ImageGenerationService;
use App\Services\PromptBuilderService;
use App\Services\AI\Validators\AIOutputValidator;
use Illuminate\Support\Facades\Log;
use Exception;

class LandingPagePipelineService
{
    protected AgentService $agent;
    protected PromptBuilderService $promptBuilder;
    protected ProductResearchService $research;
    protected AIModelRoleResolverService $roleResolver;
    protected ImageGenerationService $imageService;
    protected AIOutputValidator $validator;

    public function __construct(
        AgentService $agent,
        PromptBuilderService $promptBuilder,
        ProductResearchService $research,
        AIModelRoleResolverService $roleResolver,
        ImageGenerationService $imageService,
        AIOutputValidator $validator
    ) {
        $this->agent = $agent;
        $this->promptBuilder = $promptBuilder;
        $this->research = $research;
        $this->roleResolver = $roleResolver;
        $this->imageService = $imageService;
        $this->validator = $validator;
    }

    /**
     * PHASE 1: Research & Identity
     */
    public function runPhase1_Research(array $input, ?int $workspaceId): array
    {
        Log::info("Pipeline [Phase 1]: Research & Identity started.");

        $imagePath = $input['image_path'] ?? null;
        
        $analysisPrompt = "Analyze this product image. Reply ONLY with a JSON object:
{
  \"name\": \"product name\",
  \"cat\": \"category\",
  \"sub\": \"subcategory\",
  \"mat\": \"materials\",
  \"col\": \"colors\",
  \"style\": \"design style\",
  \"audience\": \"target audience\",
  \"uses\": [\"use 1\", \"use 2\"],
  \"feats\": [\"feature 1\", \"feature 2\"],
  \"keywords\": [\"keyword 1\", \"keyword 2\"],
  \"identity_lock\": \"Detailed physical inventory of the product's geometry, branding, and shape.\"
}";

        $rawAnalysis = $this->agent->generateDirect($analysisPrompt, "You are a concise product analyst extracting visual and market data.", '', $imagePath, 'vision', $workspaceId);
        
        $analysis = [
            'product_name_guess' => $rawAnalysis['name'] ?? $input['product_name'] ?? 'Unknown Product',
            'category'           => $rawAnalysis['cat'] ?? 'General',
            'identity_lock'      => $rawAnalysis['identity_lock'] ?? 'N/A',
            'target_audience'    => $rawAnalysis['audience'] ?? $input['audience'] ?? 'Broad',
            'visual_features'    => $rawAnalysis['feats'] ?? [],
        ];

        // Call the research service
        $research = $this->research->research($analysis, $workspaceId);
        $research['identity_lock'] = $analysis['identity_lock']; // Ensure identity lock passes forward
        $research['product_name']  = $analysis['product_name_guess'];
        $research['category']      = $analysis['category'];

        return $research;
    }

    /**
     * PHASE 2: Conversion Strategy Engine
     */
    public function runPhase2_Strategy(array $research, array $input, ?int $workspaceId): array
    {
        Log::info("Pipeline [Phase 2]: Conversion Strategy Engine started.");

        $systemPrompt = "You are the landing-page-conversion-engine. You act as the CENTRAL INTELLIGENCE BRAIN. Return the Conversion Blueprint as a JSON object strictly following the structure:
{
  \"product_analysis\": {\"core_desire\":\"\", \"primary_pain\":\"\"},
  \"conversion_strategy\": {\"primary_angle\":\"\"},
  \"objection_map\": [{\"objection\":\"\", \"reframing_strategy\":\"\"}],
  \"proof_strategy\": {\"primary_proof_type\":\"\"},
  \"offer_engineering\": {\"offer_structure\":\"\", \"risk_reversal_guarantee\":\"\"},
  \"image_directions\": [{\"section_target\":\"Hero\", \"image_purpose\":\"\"}]
}";

        $userPrompt = "Analyze this product profile and construct the Conversion Blueprint.\n" . json_encode($research, JSON_PRETTY_PRINT);

        // Fallback to text_generation or logic if there's no specific 'strategy' role.
        $response = $this->agent->generateDirect($userPrompt, $systemPrompt, "{\n  \"product_analysis\":", null, 'text_generation', $workspaceId);
        
        // Basic parser mitigation if it failed to return valid JSON
        if (isset($response['success']) && $response['success'] === false) {
             throw new Exception("Strategy Phase Failed: " . ($response['error'] ?? 'Unknown AI error'));
        }

        return is_array($response) && isset($response['product_analysis']) ? $response : ['raw' => $response];
    }

    /**
     * PHASE 3: Structure & Copy
     */
    public function runPhase3_StructureAndCopy(array $blueprint, array $research, array $input, ?int $workspaceId): array
    {
        Log::info("Pipeline [Phase 3]: Structure & Copy started.");

        $unifiedContext = [
            'strategy_blueprint' => $blueprint,
            'research'           => $research,
            'user_intent'        => [
                'tone' => $input['tone'] ?? 'Professional',
                'product_name' => $input['product_name'] ?? $research['product_name'] ?? 'Product',
            ]
        ];

        // We use the existing prompt builder but pass the blueprint instead of just analysis
        $prompt = $this->promptBuilder->buildLandingPagePrompt($unifiedContext, $input);
        
        $outputFormat = '{
  "sections": [
    {"id": "hero", "type": "hero", "html": "<section class=\'py-24 px-6 bg-white text-center\'><div class=\'max-w-6xl mx-auto\'>...</div></section>"}
  ]
}';

        $instructions = "You are generating a HIGH-CONVERTING PRODUCT LANDING PAGE.
CRITICAL RULES:
1. Return VALID JSON ONLY with a top-level 'sections' key.
2. You MUST strictly follow the attached Conversion Strategy Blueprint for messaging, objections, and offer.
3. NEVER use inline styles. ALWAYS use TailwindCSS classes.
4. Output must be immediately renderable and compatible with GrapesJS.
5. Apply the specific copy strategies dictated by the Blueprint.";

        $maxAttempts = 2;
        $attempt = 0;
        $lastResponse = null;
        $currentPrompt = $prompt;

        while ($attempt < $maxAttempts) {
            $attempt++;
            $response = $this->agent->generateDirect($currentPrompt, $instructions, $outputFormat, null, 'text_generation', $workspaceId);
            
            if (isset($response['success']) && $response['success'] === false) {
                throw new Exception("Structure Phase Failed: " . ($response['error'] ?? 'Unknown error'));
            }

            $validation = $this->validator->validate($response, 'landing-page');
            
            if ($validation['is_valid'] || $attempt === $maxAttempts) {
                $lastResponse = $response;
                break;
            }
            $currentPrompt = $prompt . "\n\n=== REFINEMENT REQUEST ===\n" . $validation['feedback'];
        }

        return $this->validateAndTransformResponse($lastResponse);
    }

    /**
     * PHASE 4: Visual Generation
     */
    public function runPhase4_Visuals(array $sections, array $research, array $blueprint, array $input, ?int $workspaceId): array
    {
        Log::info("Pipeline [Phase 4]: Visual Generation started.");

        $originalProductImageUrl = $input['image_url'] ?? null;
        $generatedImagesMap = [];

        foreach ($sections as &$section) {
            $sectionType = strtolower($section['type'] ?? 'custom');
            $pattern = '/src\s*=\s*["\']([^"\']*)["\']\s*/i';
            
            if (preg_match_all($pattern, $section['html'], $matches)) {
                foreach ($matches[0] as $index => $fullMatch) {
                    $originalTagMatch = $fullMatch;
                    $assignedImageUrl = null;

                    // Hero & Basic Product sections always use original image for safety
                    if (in_array($sectionType, ['hero', 'product']) && !empty($originalProductImageUrl)) {
                        $assignedImageUrl = $originalProductImageUrl;
                    } else {
                        // For generic blocks, call image service
                        $assignedImageUrl = $this->imageService->generateForSection(
                            $section['type'],
                            $research['product_name'],
                            $section['html'],
                            $research,
                            $workspaceId
                        );
                    }

                    if ($assignedImageUrl) {
                        $generatedImagesMap[$section['id'] ?? 'img_' . uniqid()] = $assignedImageUrl;

                        $cleanUrl = $assignedImageUrl;
                        if (str_contains($assignedImageUrl, 'localhost') && str_contains($assignedImageUrl, 'http')) {
                            $urlParts = parse_url($assignedImageUrl);
                            $cleanUrl = ($urlParts['path'] ?? '') . (isset($urlParts['query']) ? '?' . $urlParts['query'] : '');
                        }

                        $newTag = 'src="' . $cleanUrl . '"';
                        $section['html'] = str_replace($originalTagMatch, $newTag, $section['html']);
                    }
                }
            }
            
            // Final fallback for empty src tags
            if (str_contains($section['html'], 'src=""') || str_contains($section['html'], 'src=\'\'')) {
                 $section['html'] = str_replace(['src=""', 'src=\'\''], 'src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=1000&auto=format&fit=crop"', $section['html']);
            }
        }

        return [
            'sections' => $sections,
            'images_map' => $generatedImagesMap
        ];
    }

    /**
     * PHASE 5 & 6: Compile & Validate
     */
    public function runPhase5_Compile(array $sectionsWithVisuals, array $input): array
    {
        Log::info("Pipeline [Phase 5/6]: Compile & Validate started.");

        $sections = $sectionsWithVisuals['sections'];

        // Compile HTML
        $compiledHtml = '';
        foreach ($sections as $section) {
            $compiledHtml .= $section['html'] . "\n";
        }

        // Default or generated Tailwind styling info
        $css = "/* Dynamic theme styles */\n:root {\n  --accent: #4f46e5;\n}\n";
        
        return [
            'html' => $compiledHtml,
            'css' => $css,
            'js' => "/* Init */",
            'custom_head' => "<!-- Fonts/Meta -->",
            'raw_sections' => $sections // kept for legacy reference or builder breakdown
        ];
    }

    // --- Helpers ---

    private function validateAndTransformResponse(array $response): array
    {
        $sections = [];
        
        if (isset($response['sections']) && is_array($response['sections'])) {
            $sections = $response['sections'];
        } elseif (isset($response['data']['sections']) && is_array($response['data']['sections'])) {
            $sections = $response['data']['sections'];
        } else {
            foreach ($response as $key => $value) {
                if (is_array($value) && !empty($value)) {
                    $firstItem = reset($value);
                    if (is_array($firstItem) && (isset($firstItem['html']) || isset($firstItem['type']) || isset($firstItem['content']))) {
                        $sections = $value;
                        break;
                    }
                }
            }
        }

        if (empty($sections) && isset($response['html']) && is_string($response['html'])) {
             $sections = [['id' => 'hero', 'type' => 'hero', 'html' => $response['html']]];
        }

        if (empty($sections)) {
            throw new Exception("AI failed to provide renderable HTML sections.");
        }

        $normalized = [];
        foreach ($sections as $index => $section) {
            if (!is_array($section)) {
                $normalized[] = [
                    'id' => "section_{$index}",
                    'type' => 'content',
                    'html' => (string)$section
                ];
                continue;
            }

            $html = $section['html'] ?? $section['content'] ?? $section['html_content'] ?? $section['body'] ?? '';
            
            if (empty($html)) continue;

            $normalized[] = [
                'id' => $section['id'] ?? $section['name'] ?? "section_{$index}",
                'type' => $section['type'] ?? $section['category'] ?? 'custom',
                'html' => $html
            ];
        }

        if (empty($normalized)) {
            throw new Exception("AI provided a sections array, but every section was missing 'html' content.");
        }

        return $normalized;
    }
}
