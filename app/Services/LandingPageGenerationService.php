<?php

namespace App\Services;

// ProductResearchService and ImageGenerationService are in the same namespace
use Exception;
use Illuminate\Support\Facades\Log;

class LandingPageGenerationService
{
    protected $agentService;
    protected $researchService;
    protected $imageService; // Added this line

    public function __construct(
        AgentService $agentService,
        ProductResearchService $researchService,
        ImageGenerationService $imageService // Modified this line
    ) {
        $this->agentService = $agentService;
        $this->researchService = $researchService;
        $this->imageService = $imageService; // Added this line
    }

    /**
     * Generate a full landing page structure mapped for GrapesJS/HTML
     */
    public function generate(array $data, ?string $imageUrl = null): array
    {
        if (empty($data['product_name'])) {
            throw new Exception("Product name is required for generation.");
        }
 
        try {
            // STEP 1: Perform Product Research
            $researchData = $this->researchService->research($data, $imageUrl);
            
            // STEP 2: Enrich generation data with research insights
            $data['research'] = $researchData;

            // STEP 3: Generate the Landing Page
            $response = $this->agentService->generateLandingPage($data, $imageUrl);
 
            Log::info("RAW AI JSON PAYLOAD DUMP:");
            Log::info(json_encode($response, JSON_PRETTY_PRINT));
 
            $sections = $this->validateAndTransformResponse($response);
 
            // STEP 4: Image Mapping and Generation for key sections
            foreach ($sections as &$section) {
                if (in_array(strtolower($section['type']), ['hero', 'features', 'benefits', 'product'])) {
                    
                    // Detect empty src, 'placeholder', or generic placeholder domains
                    if (preg_match('/src=[\'"](.*placeholder.*|)?[\'"]/i', $section['html']) || strpos($section['html'], 'src=""') !== false) {
                        
                        $assignedImageUrl = null;
                        
                        // Prioritize the uploaded product image for Hero and Product sections
                        if (in_array(strtolower($section['type']), ['hero', 'product']) && !empty($imageUrl)) {
                            $assignedImageUrl = $imageUrl;
                            Log::info("Mapping uploaded image to section: " . $section['type']);
                        } else {
                            // Generate custom image for benefits/features
                            $assignedImageUrl = $this->imageService->generateForSection(
                                $section['type'],
                                $data['product_name'],
                                $section['html']
                            );
                        }

                        if ($assignedImageUrl) {
                            // Surgical replacement of the placeholder src attribute
                            $section['html'] = preg_replace('/src=[\'"](.*placeholder.*|)?[\'"]/i', 'src="' . $assignedImageUrl . '"', $section['html']);
                            // Also catch exactly empty src="" just in case preg_replace missed it
                            $section['html'] = str_replace(['src=""', "src=''"], 'src="' . $assignedImageUrl . '"', $section['html']);
                        }
                    }
                }
            }

            // Extract SEO data if provided
            $seo = $response['seo'] ?? [
                'title' => $data['product_name'] ?? 'Landing Page',
                'description' => 'A high-converting landing page.',
                'schema' => ''
            ];

            // Structure the response exactly how the frontend expects it
            return [
                'status' => 'success',
                'data' => [
                    'language' => $data['language'] ?? 'English',
                    'seo' => $seo,
                    'sections' => $sections
                ]
            ];
        } catch (Exception $e) {
            Log::error("Landing Page Generation Failed: " . $e->getMessage());
            throw $e;
        }
    }
 
    /**
     * Map alternative AI structures into the preferred preview format
     */
    private function validateAndTransformResponse(array $response): array
    {
        $sections = [];
 
        // 1. Try to find the sections array using various possible keys
        if (isset($response['sections']) && is_array($response['sections'])) {
            $sections = $response['sections'];
        } elseif (isset($response['landing_builder_data']['sections'])) {
            $sections = $response['landing_builder_data']['sections'];
        } elseif (isset($response['html_content']) && is_array($response['html_content'])) {
            $sections = $response['html_content'];
        } elseif (isset($response['html_css']['sections'])) {
            $sections = $response['html_css']['sections'];
        } else {
            // Search all keys for an array that looks like sections (contains 'html' or 'type')
            foreach ($response as $value) {
                if (is_array($value) && !empty($value) && (isset($value[0]['html']) || isset($value[0]['type']))) {
                    $sections = $value;
                    break;
                }
            }
        }
 
        // 2. Fallback: if the entire response is a flat array of sections
        if (empty($sections) && isset($response[0]) && (isset($response[0]['html']) || isset($response[0]['type']))) {
            $sections = $response;
        }
 
        // 3. Last resort check
        if (empty($sections)) {
            $keys = implode(', ', array_keys($response));
            Log::error("AI Output Validation Failed. Keys found: {$keys}");
            throw new Exception("The AI generated strategy data but failed to provide renderable HTML sections. Keys found: {$keys}");
        }
 
        // 4. Ensure each section is normalized for the preview
        foreach ($sections as $index => &$section) {
            if (!is_array($section)) {
                $section = ['id' => "sec_$index", 'type' => 'content', 'html' => (string)$section];
                continue;
            }
 
            // Map common HTML keys
            if (empty($section['html'])) {
                $section['html'] = $section['content']['html'] ?? $section['html_content'] ?? $section['html_css'] ?? '';
            }
 
            if (empty($section['id'])) {
                $section['id'] = "section_" . $index;
            }
 
            if (empty($section['type'])) {
                $section['type'] = 'custom';
            }
        }
 
        return $sections;
    }
}
