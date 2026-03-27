<?php

namespace App\Services\AI\Loaders;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class WorkflowLoaderService
{
    /**
     * Load all workflows from the .agent/workflows directory
     */
    public function loadWorkflows(): string
    {
        $path = base_path('.agent/workflows');
        
        if (!File::exists($path) || !File::isDirectory($path)) {
            Log::warning('AI Workflows folder not found at ' . $path);
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
                $content[] = "--- WORKFLOW: {$file->getFilename()} ---\n" . $file->getContents() . "\n";
                $count++;
            }
        }

        Log::info("Loaded {$count} AI workflows.");

        return implode("\n", $content);
    }
}
