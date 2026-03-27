<?php

namespace App\Services\AI\Resolvers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class ImageResolverService
{
    /**
     * Resolve any image path or ID to a public, absolute URL.
     * 
     * @param string|null $imageIdentifier
     * @return string|null
     */
    public function resolvePublicUrl(?string $imageIdentifier): ?string
    {
        if (empty($imageIdentifier)) {
            Log::warning("ImageResolver: No image identifier provided.");
            return null;
        }

        // 1. If it's already a full URL, return it
        if (filter_var($imageIdentifier, FILTER_VALIDATE_URL)) {
            return $imageIdentifier;
        }

        // 2. Check if it's a path in storage (e.g. products/xyz.jpg)
        if (Storage::disk('public')->exists($imageIdentifier)) {
            return asset('storage/' . $imageIdentifier);
        }

        // 3. Check if it's a relative path starting with storage/
        if (str_starts_with($imageIdentifier, 'storage/')) {
            return asset($imageIdentifier);
        }

        // 4. Handle cases where the path might be absolute on disk but needs public resolution
        // (Assuming storage_path('app/public/...') -> asset('storage/...'))
        $publicPathPrefix = storage_path('app/public/');
        if (str_starts_with($imageIdentifier, $publicPathPrefix)) {
            $relativePath = str_replace($publicPathPrefix, '', $imageIdentifier);
            return asset('storage/' . $relativePath);
        }

        Log::warning("ImageResolver: Could not resolve public URL for: {$imageIdentifier}");
        
        return null;
    }

    /**
     * Get a set of fallback images based on niche if possible.
     */
    public function getFallbackImages(string $niche = 'general'): array
    {
        return [
            'hero' => 'https://via.placeholder.com/1200x800?text=Product+Image',
            'thumbnail' => 'https://via.placeholder.com/400x400?text=Product'
        ];
    }
}
