<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImageStorageService
{
    /**
     * Store an uploaded image in a date-based directory structure.
     * 
     * @param UploadedFile $file
     * @param string $disk
     * @return array
     */
    public function store(UploadedFile $file, string $disk = 'public'): array
    {
        // 1. Generate date-based path: ai/uploads/YYYY/MM/DD/HHa
        // Example: ai/uploads/2026/12/06/11pm
        $now = now();
        $folderPath = sprintf(
            'ai/uploads/%s/%s/%s/%s',
            $now->format('Y'),
            $now->format('m'),
            $now->format('d'),
            $now->format('ha')
        );

        // 2. Generate unique filename
        // Clean the original filename to be URL-safe but keep identifiable parts
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = Str::slug($originalName);
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        
        // Final filename: product-name-randomstring.jpg
        $filename = sprintf('%s-%s.%s', $safeName, Str::random(8), $extension);

        try {
            // 3. Perform Validation Check (Extra layer)
            if (!$file->isValid()) {
                throw new \Exception("Uploaded file is not valid: " . $file->getErrorMessage());
            }

            // 4. Store on disk
            $path = $file->storeAs($folderPath, $filename, $disk);

            if (!$path) {
                throw new \Exception("Failed to store file on disk: {$disk}");
            }

            // 5. Build URLs and Paths
            $storagePath = Storage::disk($disk)->path($path);
            $publicUrl = Storage::disk($disk)->url($path);

            // 6. Log full details for debugging (as requested)
            Log::info("ImageStorageService Security/Storage Debug:", [
                'original_filename' => $file->getClientOriginalName(),
                'mime_type'         => $file->getMimeType(),
                'size'              => $file->getSize(),
                'folder_path'       => $folderPath,
                'final_filename'    => $filename,
                'disk'              => $disk,
                'absolute_path'     => $storagePath,
                'public_url'        => $publicUrl,
                'server_time'       => $now->toDateTimeString()
            ]);

            return [
                'success'       => true,
                'path'          => $path,
                'absolute_path' => $storagePath,
                'url'           => $publicUrl,
                'filename'      => $filename
            ];

        } catch (\Exception $e) {
            Log::error("ImageStorageService: Fatal error in upload pipeline.", [
                'error' => $e->getMessage(),
                'file'  => $file->getClientOriginalName(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error'   => $e->getMessage()
            ];
        }
    }
}
