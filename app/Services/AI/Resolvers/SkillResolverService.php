<?php

namespace App\Services\AI\Resolvers;

use App\Services\AI\Loaders\SkillLoaderService;
use Illuminate\Support\Facades\Log;

class SkillResolverService
{
    protected SkillLoaderService $skillLoader;

    public function __construct(SkillLoaderService $skillLoader)
    {
        $this->skillLoader = $skillLoader;
    }

    /**
     * Resolve and prioritize skills based on the task role.
     * 
     * @param string $role The AI role/task (e.g. 'landing-page', 'vision', 'seo_audit')
     * @return string Formatted skill block
     */
    public function resolveRelevantSkills(string $role): string
    {
        Log::info("SkillResolver: Selecting skills for role: {$role}");

        // Map roles to mandatory skill categories/tags
        $mandatoryMap = [
            'landing-page' => [
                'frontend-design',
                'conversion-copywriting',
                'seo-fundamentals',
                'product-identity-lock',
                'landing-builder-compatibility',
                'product-page-generator'
            ],
            'product-page' => [
                'product-page-generator',
                'frontend-design',
                'conversion-copywriting',
                'seo-fundamentals',
                'product-identity-lock',
                'landing-builder-compatibility'
            ],
            'vision' => [
                'product-identity-lock',
                'image-safety-workflow'
            ],
            'copywriting' => [
                'conversion-copywriting',
                'seo-meta-optimizer'
            ]
        ];

        $categories = $mandatoryMap[$role] ?? [];
        
        if (empty($categories)) {
            Log::warning("SkillResolver: No mapping found for role '{$role}'. Loading all skills.");
            return $this->skillLoader->loadSkills();
        }

        // Load specific skills and format them with "MANDATORY" emphasis
        $skills = $this->skillLoader->loadSkillList($categories);
        
        // AUDIT: Log which skills were found and which are missing
        $loadedNames = array_column($skills, 'name');
        $missingSkills = array_diff($categories, $loadedNames);
        
        Log::info("SkillResolver: Audit for role '{$role}'", [
            'requested' => $categories,
            'found' => $loadedNames,
            'missing' => array_values($missingSkills),
            'found_count' => count($loadedNames),
            'missing_count' => count($missingSkills),
        ]);

        if (!empty($missingSkills)) {
            Log::warning("SkillResolver: MISSING SKILLS for role '{$role}': " . implode(', ', $missingSkills) . 
                " — these skill folders do not exist in .agent/skills/");
        }

        $output = ["=== MANDATORY EXPERT SKILLS FOR {$role} ===\n"];

        foreach ($skills as $skill) {
            $name = strtoupper(str_replace(['-', '_', '/'], ' ', $skill['name']));
            $cat = strtoupper($skill['category']);
            $output[] = "[MANDATORY SKILL: {$name}]\nCategory: {$cat}\n{$skill['content']}\n";
        }

        return implode("\n", $output);
    }
}
