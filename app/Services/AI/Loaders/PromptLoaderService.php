<?php

namespace App\Services\AI\Loaders;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class PromptLoaderService
{
    /**
     * Load all prompt fragments from the .agent/prompts directory
     */
    public function loadPrompts(): string
    {
        $path = base_path('.agent/prompts');
        
        if (!File::exists($path) || !File::isDirectory($path)) {
            // Prompts are optional, so we just return empty seamlessly
            return '';
        }

        $content = [];
        $files = File::allFiles($path);

        // Sort files alphabetically for stable loading order
        usort($files, function ($a, $b) {
            return strcmp($a->getFilename(), $b->getFilename());
        });

        $count = 0;
        foreach ($files as $file) {
            $extension = strtolower($file->getExtension());
            if (in_array($extension, ['md', 'txt']) && !str_starts_with($file->getFilename(), '.')) {
                $content[] = "--- PROMPT FRAGMENT: {$file->getFilename()} ---\n" . $file->getContents() . "\n";
                $count++;
            }
        }

        if ($count > 0) {
            Log::info("Loaded {$count} AI prompt fragments.");
        }

        return implode("\n", $content);
    }
}
