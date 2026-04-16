<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\Landing;
use App\Models\MediaAsset;
use Illuminate\Support\Str;

class BackfillMediaAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:backfill';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan storage for landing images and create missing MediaAsset records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $landingsPath = storage_path('app/public/landings');

        if (!File::exists($landingsPath)) {
            $this->error("Landings directory not found at: {$landingsPath}");
            return 1;
        }

        $directories = File::directories($landingsPath);
        $this->info("Found " . count($directories) . " landing directories.");

        foreach ($directories as $dir) {
            $uuid = basename($dir);
            
            // Find landing by UUID
            $landing = Landing::where('uuid', $uuid)->first();
            
            if (!$landing) {
                $this->warn("Skipping unknown landing UUID: {$uuid}");
                continue;
            }

            $userId = $landing->workspace->user_id ?? null;
            if (!$userId) {
                $this->warn("Skipping landing {$uuid} (No User ID found via Workspace)");
                continue;
            }

            $this->info("Processing Landing: {$landing->name} ({$uuid})");

            $allFiles = File::allFiles($dir);
            $count = 0;

            foreach ($allFiles as $file) {
                $mime = (string) @mime_content_type($file->getPathname());
                $extension = strtolower((string) pathinfo($file->getFilename(), PATHINFO_EXTENSION));
                $isSupportedAsset = str_starts_with($mime, 'image/')
                    || str_starts_with($mime, 'video/')
                    || str_starts_with($mime, 'audio/')
                    || str_starts_with($mime, 'model/')
                    || in_array($extension, ['glb', 'gltf', 'obj', 'fbx', 'stl', 'usdz', 'json', 'bin', 'wasm', 'woff', 'woff2', 'ttf', 'otf', 'css', 'js', 'mjs', 'pdf'], true);

                if (!$isSupportedAsset) {
                    continue;
                }

                $relativePath = str_replace(storage_path('app/public/'), '', $file->getPathname());
                $relativePath = str_replace('\\', '/', $relativePath); // Normalize

                // Check if exists
                $exists = MediaAsset::where('landing_id', $landing->id)
                    ->where('relative_path', $relativePath)
                    ->exists();

                if ($exists) {
                    continue;
                }

                // Create
                try {
                    $dimensions = str_starts_with($mime, 'image/')
                        ? @getimagesize($file->getPathname())
                        : null;
                    
                    MediaAsset::create([
                        'user_id' => $userId,
                        'landing_id' => $landing->id,
                        'filename' => $file->getFilename(),
                        'relative_path' => $relativePath,
                        'disk' => 'public',
                        'mime_type' => $mime,
                        'size' => $file->getSize(),
                        'width' => $dimensions ? $dimensions[0] : null,
                        'height' => $dimensions ? $dimensions[1] : null,
                        'source' => 'backfill',
                    ]);
                    $count++;
                    $this->line("  + Indexed: {$file->getFilename()}");
                } catch (\Exception $e) {
                    $this->error("  ! Failed to index {$file->getFilename()}: {$e->getMessage()}");
                }
            }

            if ($count > 0) {
                $this->info("  -> Added {$count} new assets.");
            }
        }

        $this->info("Backfill complete.");
        return 0;
    }
}
