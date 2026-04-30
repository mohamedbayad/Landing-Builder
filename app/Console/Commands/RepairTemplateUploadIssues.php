<?php

namespace App\Console\Commands;

use App\Models\Landing;
use App\Models\MediaAsset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class RepairTemplateUploadIssues extends Command
{
    protected $signature = 'templates:repair-upload {--dry-run : Preview changes without saving}';

    protected $description = 'Repair uploaded template pages and backfill missing template assets in media library.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Step 1/2: repairing template page payloads...');
        $exitCode = Artisan::call('templates:repair-assets', $dryRun ? ['--dry-run' => true] : []);
        $this->line(trim((string) Artisan::output()));
        if ($exitCode !== self::SUCCESS) {
            $this->error('templates:repair-assets failed.');
            return $exitCode;
        }

        $this->newLine();
        $this->info('Step 2/2: indexing template assets into media library...');
        $stats = $this->indexTemplateAssets($dryRun);

        $this->newLine();
        $this->info('Upload repair completed.');
        $this->line("Landings scanned: {$stats['landings_scanned']}");
        $this->line("Assets scanned: {$stats['assets_scanned']}");
        $this->line("Created: {$stats['created']}");
        $this->line("Updated: {$stats['updated']}");
        $this->line("Skipped: {$stats['skipped']}");

        if ($dryRun) {
            $this->warn('Dry-run mode enabled: no media records were written.');
        }

        return self::SUCCESS;
    }

    /**
     * @return array{landings_scanned:int,assets_scanned:int,created:int,updated:int,skipped:int}
     */
    protected function indexTemplateAssets(bool $dryRun): array
    {
        $stats = [
            'landings_scanned' => 0,
            'assets_scanned' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
        ];

        Landing::query()
            ->with(['template:id,storage_path', 'workspace:id,user_id'])
            ->whereNotNull('template_id')
            ->orderBy('id')
            ->chunkById(100, function ($landings) use (&$stats, $dryRun): void {
                foreach ($landings as $landing) {
                    $stats['landings_scanned']++;

                    $template = $landing->template;
                    $workspace = $landing->workspace;
                    $userId = (int) ($workspace->user_id ?? 0);
                    $storageDirectory = trim((string) ($template->storage_path ?? ''), '/');

                    if (!$template || $userId <= 0 || $storageDirectory === '') {
                        continue;
                    }

                    $rootPath = storage_path('app/public/' . $storageDirectory);
                    if (!File::isDirectory($rootPath)) {
                        continue;
                    }

                    foreach (File::allFiles($rootPath) as $file) {
                        $absolutePath = $file->getPathname();
                        if (!is_file($absolutePath)) {
                            continue;
                        }

                        $extension = strtolower((string) pathinfo($absolutePath, PATHINFO_EXTENSION));
                        $mimeType = strtolower((string) (File::mimeType($absolutePath) ?? 'application/octet-stream'));

                        $isSupportedAsset = str_starts_with($mimeType, 'image/')
                            || str_starts_with($mimeType, 'video/')
                            || str_starts_with($mimeType, 'audio/')
                            || str_starts_with($mimeType, 'model/')
                            || in_array($extension, [
                                'glb', 'gltf', 'obj', 'fbx', 'stl', 'usdz',
                                'js', 'mjs', 'css', 'json', 'map', 'wasm', 'bin',
                                'woff', 'woff2', 'ttf', 'otf', 'svg', 'avif',
                            ], true);

                        if (!$isSupportedAsset) {
                            continue;
                        }

                        $stats['assets_scanned']++;

                        $relativeFromRoot = ltrim(str_replace('\\', '/', str_replace($rootPath, '', $absolutePath)), '/');
                        if ($relativeFromRoot === '') {
                            $stats['skipped']++;
                            continue;
                        }

                        $relativePath = trim($storageDirectory . '/' . $relativeFromRoot, '/');
                        $size = @filesize($absolutePath) ?: null;
                        $width = null;
                        $height = null;

                        if (str_starts_with($mimeType, 'image/') && $mimeType !== 'image/svg+xml') {
                            $dimensions = @getimagesize($absolutePath);
                            if (is_array($dimensions)) {
                                $width = isset($dimensions[0]) ? (int) $dimensions[0] : null;
                                $height = isset($dimensions[1]) ? (int) $dimensions[1] : null;
                            }
                        }

                        if ($dryRun) {
                            $exists = MediaAsset::query()
                                ->where('landing_id', $landing->id)
                                ->where('relative_path', $relativePath)
                                ->exists();

                            if ($exists) {
                                $stats['updated']++;
                            } else {
                                $stats['created']++;
                            }

                            continue;
                        }

                        $record = MediaAsset::updateOrCreate(
                            [
                                'landing_id' => $landing->id,
                                'relative_path' => $relativePath,
                            ],
                            [
                                'user_id' => $userId,
                                'template_id' => $template->id,
                                'filename' => basename($relativePath),
                                'disk' => 'public',
                                'mime_type' => $mimeType ?: null,
                                'size' => is_int($size) ? $size : null,
                                'width' => $width,
                                'height' => $height,
                                'source' => 'zip',
                            ]
                        );

                        if ($record->wasRecentlyCreated) {
                            $stats['created']++;
                        } elseif ($record->wasChanged()) {
                            $stats['updated']++;
                        } else {
                            $stats['skipped']++;
                        }
                    }
                }
            });

        return $stats;
    }
}

