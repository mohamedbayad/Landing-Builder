<?php

namespace App\Services\AI\Validators;

use Illuminate\Support\Facades\Log;

class AIOutputValidator
{
    /**
     * Validate the generated AI output against design and quality rubrics.
     * 
     * @param array $response The decoded AI response
     * @param string $taskType The task role
     * @return array ['is_valid' => bool, 'score' => int, 'issues' => array, 'feedback' => string]
     */
    public function validate(array $response, string $taskType = 'landing-page'): array
    {
        Log::info("AIValidator: Starting quality validation for {$taskType}");

        $issues = [];
        $scores = [
            'structure' => 0,
            'seo' => 0,
            'design' => 0,
            'integrity' => 0
        ];

        // 1. Structural Validation
        $sections = $response['sections'] ?? [];
        if (empty($sections)) {
            $issues[] = "Missing 'sections' key in response.";
        } else {
            $scores['structure'] = 10;
        }

        // 2. Formatting & Prohibitions (Tailwind/Inline CSS)
        foreach ($sections as $section) {
            $html = $section['html'] ?? '';
            
            // Check for inline styles
            if (str_contains($html, 'style="') || str_contains($html, "style='")) {
                $issues[] = "Section '{$section['id']}': Contains forbidden inline styles.";
            }
            
            // Check for common Tailwind container patterns
            if (!str_contains($html, 'mx-auto') && !str_contains($html, 'max-w-')) {
                $issues[] = "Section '{$section['id']}': Missing layout containers (mx-auto/max-w). Likely generic structure.";
            }

            // Check for empty or too-short HTML
            if (strlen($html) < 200) {
                 $issues[] = "Section '{$section['id']}': HTML content is too short/generic.";
            }
        }

        // 3. SEO Validation
        $seo = $response['seo'] ?? [];
        if (empty($seo['title'])) $issues[] = "SEO: Missing meta title.";
        if (empty($seo['description'])) $issues[] = "SEO: Missing meta description.";
        
        $fullHtml = implode('', array_column($sections, 'html'));
        $h1Count = substr_count(strtolower($fullHtml), '<h1');
        if ($h1Count !== 1) {
            $issues[] = "SEO: Page has {$h1Count} H1 tags. Exactly one is required.";
        } else {
            $scores['seo'] = 10;
        }

        // 4. Image Integrity
        if (str_contains($fullHtml, 'src=""') || str_contains($fullHtml, 'src="#"')) {
            $issues[] = "Images: Found empty or invalid src attributes.";
        } else {
            $scores['integrity'] = 10;
        }

        // 5. Final Decision
        $isValid = count($issues) === 0;
        $totalScore = array_sum($scores) / count($scores);

        // Generate feedback string for refinement
        $feedback = "";
        if (!$isValid) {
            $feedback = "Your previous output failed validation for the following reasons:\n- " . implode("\n- ", $issues);
            $feedback .= "\n\nPlease correct these issues and regenerate the response with HIGHER QUALITY and strict adherence to the expert skills.";
        }

        Log::info("AIValidator: Result - Valid: " . ($isValid ? 'Yes' : 'No') . ", Score: {$totalScore}");

        return [
            'is_valid' => $isValid,
            'score' => $totalScore,
            'issues' => $issues,
            'feedback' => $feedback
        ];
    }
}
