<?php

namespace App\Services\AI\Loaders;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SkillLoaderService
{
    /**
     * Load all skills from the .agent/skills directory
     * 
     * @param array $filter Optional array of skill names or categories to load
     * @return array Array of skill objects ['name' => ..., 'category' => ..., 'content' => ...]
     */
    public function loadSkillList(array $filter = []): array
    {
        $path = base_path('.agent/skills');
        
        if (!File::exists($path) || !File::isDirectory($path)) {
            Log::warning("AI Skill Loader: folder not found at {$path}");
            return [];
        }

        $skills = [];
        $allFiles = File::allFiles($path);
        
        // Sort files by path for deterministic loading order
        usort($allFiles, function ($a, $b) {
            return strcmp($a->getPathname(), $b->getPathname());
        });

        foreach ($allFiles as $file) {
            $filename = $file->getFilename();
            $relativePath = $file->getRelativePathname();
            $extension = strtolower($file->getExtension());

            // Only process md, txt, json
            if (!in_array($extension, ['md', 'txt', 'json']) || str_starts_with($filename, '.')) {
                continue;
            }

            // Category is the top-level directory name
            $parts = explode(DIRECTORY_SEPARATOR, $relativePath);
            $category = count($parts) > 1 ? $parts[0] : 'general';
            $skillName = str_replace(['/SKILL.md', '\\SKILL.md', '.md', '.txt', '.json'], '', $relativePath);

            // Filtering
            if (!empty($filter)) {
                if (!in_array($category, $filter) && !in_array($skillName, $filter)) {
                    continue;
                }
            }

            try {
                $skills[] = [
                    'name' => $skillName,
                    'category' => $category,
                    'content' => $file->getContents(),
                    'path' => $relativePath
                ];
            } catch (\Exception $e) {
                Log::error("Skill Loader: Failed to load {$relativePath}: " . $e->getMessage());
            }
        }

        return $skills;
    }

    /**
     * Legacy support: Load skills as a combined string
     */
    public function loadSkills(array $filter = []): string
    {
        $skills = $this->loadSkillList($filter);
        $content = [];

        foreach ($skills as $skill) {
            $name = strtoupper(str_replace(['-', '_', '/'], ' ', $skill['name']));
            $category = strtoupper($skill['category']);
            $header = "### SKILL [{$category}]: {$name} ###";
            $content[] = "{$header}\n{$skill['content']}\n";
        }

        return implode("\n", $content);
    }
}
