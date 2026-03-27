<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Exception;

class GeneratedImageStorageService
{
    /**
     * Store a generated image in a date-based directory structure.
     * Detects if source is URL, Base64, or Binary.
     */
    public function saveGeneratedImage(mixed $source, string $filename, string $disk = 'public'): array
    {
        try {
            if (empty($source)) {
                Log::error("GeneratedImageStorageService: Received empty source payload");
                throw new Exception("Source image data is empty.");
            }

            // If source is an array (e.g. JSON response), try to extract image field
            if (is_array($source)) {
                Log::info("GeneratedImageStorageService: Source is array, attempting extraction", ['keys' => array_keys($source)]);
                $source = $source['url'] ?? $source['b64_json'] ?? $source['image'] ?? $source['data'][0]['url'] ?? null;
                if (!$source) throw new Exception("Could not extract image URL or data from array response.");
            }

            if (!is_string($source)) {
                throw new Exception("Source payload is not a valid string or array. Type: " . gettype($source));
            }

            $payloadPreview = substr($source, 0, 100);
            Log::info("GeneratedImageStorageService: Processing payload", [
                'length' => strlen($source),
                'preview' => $payloadPreview
            ]);

            // 1. Detect Source Type & Get Content
            if (preg_match('/^https?:\/\//i', $source)) {
                return $this->saveFromUrl($source, $filename, $disk);
            } elseif (preg_match('/^data:image\/(\w+);base64,/', $source)) {
                return $this->saveFromBase64($source, $filename, $disk);
            } elseif ($this->isBase64($source)) {
                return $this->saveFromBase64("data:image/webp;base64," . $source, $filename, $disk); // Assume webp or generic
            } else {
                return $this->saveFromBinary($source, $filename, $disk);
            }

        } catch (Exception $e) {
            Log::error("GeneratedImageStorageService: Failed to save image.", [
                'error' => $e->getMessage(),
                'filename' => $filename,
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Utility to check if a string is valid Base64
     */
    private function isBase64(string $s): bool
    {
        if (strlen($s) < 10) return false;
        return (bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s);
    }

    /**
     * Save image from a remote URL.
     */
    public function saveFromUrl(string $url, string $filename, string $disk = 'public'): array
    {
        Log::info("GeneratedImageStorageService: Saving from URL", ['url' => $url]);

        $response = Http::timeout(30)->get($url);

        if ($response->failed()) {
            throw new Exception("Failed to fetch image from URL: " . $url);
        }

        $binary = $response->body();
        $mime = $response->header('Content-Type') ?? 'image/webp';
        
        // Detect extension from mime
        $ext = $this->getExtensionFromMime($mime);
        if (!Str::endsWith($filename, '.' . $ext)) {
            $filename .= '.' . $ext;
        }

        return $this->saveFromBinary($binary, $filename, $disk);
    }

    /**
     * Save image from Base64 string.
     */
    public function saveFromBase64(string $base64, string $filename, string $disk = 'public'): array
    {
        Log::info("GeneratedImageStorageService: Saving from Base64");

        // data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
            $base64 = substr($base64, strpos($base64, ',') + 1);
            $ext = strtolower($type[1]); // png, jpg, webp, etc.
            
            if (!Str::endsWith($filename, '.' . $ext)) {
                $filename .= '.' . $ext;
            }
        } else {
            throw new Exception("Invalid Base64 format.");
        }

        $binary = base64_decode($base64);

        if ($binary === false) {
            throw new Exception("Base64 decode failed.");
        }

        return $this->saveFromBinary($binary, $filename, $disk);
    }

    /**
     * Save image from binary data.
     */
    public function saveFromBinary(string $binary, string $filename, string $disk = 'public'): array
    {
        // VALIDATION: Is this actual image binary or just text/json?
        if (strlen($binary) < 100) {
            // Check if it's JSON or HTML (likely error message)
            if (str_starts_with(trim($binary), '{') || str_starts_with(trim($binary), '<!DOCTYPE')) {
                Log::error("GeneratedImageStorageService: Binary data appears to be text/error, not an image.", [
                    'content' => $binary
                ]);
                throw new Exception("Invalid image binary content received (detected as text/json/html).");
            }
        }

        // Check for common image signatures (Magical Numbers)
        $isImage = false;
        $signatures = [
            "\xFF\xD8\xFF" => "jpg",
            "\x89PNG\r\n\x1a\n" => "png",
            "GIF87a" => "gif",
            "GIF89a" => "gif",
            "RIFF" => "webp", // Simplified check for WebP
        ];

        foreach ($signatures as $sig => $ext) {
            if (str_starts_with($binary, $sig)) {
                $isImage = true;
                break;
            }
        }

        if (!$isImage && strlen($binary) > 0) {
            Log::warning("GeneratedImageStorageService: Binary data missing common image header. Proceeding with caution.", [
                'first_bytes' => bin2hex(substr($binary, 0, 16))
            ]);
        }

        // 1. Generate path structure: ai/generated/YYYY/MM/DD/HHa
        $now = now();
        $folderPath = sprintf(
            'ai/generated/%s/%s/%s/%s',
            $now->format('Y'),
            $now->format('m'),
            $now->format('d'),
            $now->format('ha')
        );

        // 2. Ensure filename is unique and safe
        $extension = pathinfo($filename, PATHINFO_EXTENSION) ?: 'webp';
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $safeFilename = Str::slug($name) . '-' . Str::random(6) . '.' . $extension;
        
        $path = $folderPath . '/' . $safeFilename;

        // 3. Ensure Directory Exists
        if (!Storage::disk($disk)->exists($folderPath)) {
            Log::debug("GeneratedImageStorageService: Creating directory", ['path' => $folderPath]);
            Storage::disk($disk)->makeDirectory($folderPath);
        }

        // 4. Store file
        try {
            $success = Storage::disk($disk)->put($path, $binary);
        } catch (Exception $e) {
            Log::error("GeneratedImageStorageService: Disk write failed", [
                'error' => $e->getMessage(),
                'path' => $path
            ]);
            throw $e;
        }

        if (!$success) {
            throw new Exception("Disk write failed for path: " . $path);
        }

        // 5. POST-SAVE VALIDATION
        $absolutePath = Storage::disk($disk)->path($path);
        $fileSize = Storage::disk($disk)->size($path);
        
        if ($fileSize === 0) {
            Storage::disk($disk)->delete($path);
            throw new Exception("Saved file is empty (0 bytes). Deleting broken asset.");
        }

        // Verify it's a real image via getimagesize
        $imageInfo = @getimagesize($absolutePath);
        if (!$imageInfo && !str_contains($absolutePath, '.svg')) {
             Log::warning("GeneratedImageStorageService: getimagesize() failed on saved file. The file might still be valid but non-standard.", [
                 'path' => $absolutePath
             ]);
        }

        Log::info("GeneratedImageStorageService: Image persisted and validated", [
            'path' => $path,
            'size' => $fileSize,
            'mime' => $imageInfo['mime'] ?? 'unknown'
        ]);

        return [
            'success'       => true,
            'path'          => $path,
            'absolute_path' => $absolutePath,
            'url'           => Storage::disk($disk)->url($path),
            'filename'      => $safeFilename,
            'size'          => $fileSize,
            'mime'          => $imageInfo['mime'] ?? 'image/unknown'
        ];
    }

    /**
     * Helper to get extension from mime type.
     */
    protected function getExtensionFromMime(string $mime): string
    {
        $mimes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/svg+xml' => 'svg',
        ];

        return $mimes[strtolower($mime)] ?? 'webp';
    }
}
