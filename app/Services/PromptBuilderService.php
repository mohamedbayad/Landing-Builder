<?php

namespace App\Services;

use App\Services\AI\Loaders\SkillLoaderService;
use App\Services\AI\Loaders\WorkflowLoaderService;
use App\Services\AI\Loaders\PromptLoaderService;
use App\Services\AI\Resolvers\SkillResolverService;
use App\Services\AI\Resolvers\ImageResolverService;
use Illuminate\Support\Facades\Log;

class PromptBuilderService
{
    protected $skillLoader;
    protected $workflowLoader;
    protected $promptLoader;
    protected $skillResolver;
    protected $imageResolver;

    public function __construct(
        SkillLoaderService $skillLoader,
        WorkflowLoaderService $workflowLoader,
        PromptLoaderService $promptLoader,
        SkillResolverService $skillResolver,
        ImageResolverService $imageResolver
    ) {
        $this->skillLoader = $skillLoader;
        $this->workflowLoader = $workflowLoader;
        $this->promptLoader = $promptLoader;
        $this->skillResolver = $skillResolver;
        $this->imageResolver = $imageResolver;
    }

    /**
     * The master directive for the AI agent regarding skills and styling
     */
    const MASTER_SYSTEM_DIRECTIVE = "You are a world-class Landing Page Architect, Senior Copywriter, and Conversion Rate Optimization (CRO) Expert.
You have access to a mandatory repository of EXPERT SKILLS and STRATEGIC WORKFLOWS.

### THE GOLDEN RULE OF ENFORCEMENT ###
Relevant skills are NOT optional. They are mandatory operational directives. 
Ignoring styling, structural, or SEO skills is a critical system failure.

### HIERARCHY OF PRIORITY ###
1. ARCHITECTURAL SAFETY: Tailwind utility classes ONLY. NO inline styles. Valid JSON output.
2. MANDATORY SKILLS: Use loaded skills to drive design hierarchy, conversion storytelling, and SEO.
3. PRODUCT FIDELITY: Preserving the exact product identity and using the provided image URL.
4. QUALITY RUBRIC: Output must be premium, polished, and ready for high-traffic commerce. Generic/weak layouts will be rejected.

### VISUAL DESIGN RUBRIC ###
- Use premium Tailwind spacing (py-24, md:py-32).
- Ensure high contrast and professional typography.
- Implement clear 'Z-pattern' or 'F-pattern' layouts.
- Always include conversion-focused CTAs with hover states.
- Section structure: <section class=\"...\"><div class=\"max-w-6xl mx-auto px-6\">... content ...</div></section>.

### SEO & ACCESSIBILITY ###
- Exactly ONE <h1> per page.
- Meaningful <h2> benefits and <h3> features.
- All images MUST have descriptive alt tags using the product name.";

    /**
     * Build the context string containing skills, workflows, and prompts
     */
    public function buildBaseContext(string $taskType = 'landing-page'): string
    {
        $context = [];

        // 1. Add the Global System Directive (High Authority)
        $context[] = "=== MASTER SYSTEM DIRECTIVE (MANDATORY) ===\n" . self::MASTER_SYSTEM_DIRECTIVE;

        // 2. Load and prioritize relevant Skills
        $skills = $this->skillResolver->resolveRelevantSkills($taskType);
        if (!empty($skills)) {
            $context[] = "=== APPLICABLE EXPERT SKILLS ===\n" . $skills;
        }

        // 3. Load and format Workflows
        $workflows = $this->workflowLoader->loadWorkflows();
        if (!empty($workflows)) {
            $context[] = "=== STRATEGIC WORKFLOWS ===\n" . $workflows;
        }

        return implode("\n\n", $context);
    }

    /**
     * Build the full prompt for Landing Page Generation
     */
    public function buildLandingPagePrompt(array $data, $instructions = '', string $outputFormat = ''): string
    {
        Log::info("PromptBuilder: Building high-enforcement landing page prompt");

        $baseContext = $this->buildBaseContext('landing-page');
        $prompt = $baseContext . "\n\n";
        
        // Product Source of Truth
        $rawImage = $data['user_intent']['image_path'] ?? $data['analysis']['image_url'] ?? null;
        $publicImageUrl = $this->imageResolver->resolvePublicUrl($rawImage);

        $prompt .= "=== PRODUCT SOURCE OF TRUTH (STRICT CONSISTENCY REQUIRED) ===\n";
        $prompt .= "Product_Name: " . ($data['user_intent']['product_name'] ?? $data['analysis']['product_name_guess'] ?? 'N/A') . "\n";
        $prompt .= "Product_Image_URL: " . ($publicImageUrl ?? 'N/A') . "\n";
        
        $desc = $data['user_intent']['extra_notes'] ?? $data['analysis']['marketing_angles'][0] ?? '';
        if ($desc) $prompt .= "Description: " . $desc . "\n";

        // Specific context
        if (!empty($instructions)) {
             $prompt .= "\n=== TASK-SPECIFIC DIRECTIVES ===\n";
             if (is_array($instructions)) {
                 foreach ($instructions as $k => $v) {
                     if (is_string($v)) $prompt .= strtoupper($k) . ": " . $v . "\n";
                 }
             } else {
                 $prompt .= $instructions . "\n";
             }
        }

        if (!empty($outputFormat)) {
            $prompt .= "\n=== OUTPUT CONTRACT (JSON ONLY) ===\n";
            $prompt .= "You must return a valid JSON object matching this structure exactly:\n" . $outputFormat;
        }

        return $prompt;
    }
}
