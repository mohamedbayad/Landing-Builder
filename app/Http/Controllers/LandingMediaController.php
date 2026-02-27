<?php

namespace App\Http\Controllers;

use App\Models\Landing;
use App\Models\MediaAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LandingMediaController extends Controller
{
    /**
     * List media for GrapesJS Asset Manager.
     */
    public function index(Landing $landing)
    {
        if ($landing->workspace->user_id != Auth::id()) {
            abort(403);
        }

        // Return media for this landing OR generic user media?
        // GrapesJS usually expects just the assets for the project, but we can return all user media if desired.
        // For now, let's keep it scoped to the landing's media + generic user media?
        // User requested: "List all images for the logged-in user (across all landings/templates)... All images must be unified in one place."
        // But this endpoint is for the BUILDER. Usually builder users want to see *their* library.
        // Let's return all media for the user.
        
        $media = MediaAsset::where('user_id', Auth::id())
            ->latest()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id, // Asset URL as ID or DB ID? GrapesJS typically uses URL as src.
                    'src' => $item->url, // The public URL
                    'name' => $item->filename,
                    'type' => 'image',
                    'height' => $item->height,
                    'width' => $item->width,
                ];
            });

        return response()->json($media);
    }

    /**
     * Upload new media from GrapesJS.
     */
    public function store(Request $request, Landing $landing)
    {
        if ($landing->workspace->user_id != Auth::id()) {
            abort(403);
        }

        // Allow either 'files' (array) or 'file' (single)
        $request->validate([
            'files' => 'required_without:file',
            'file' => 'required_without:files',
        ]);

        // GrapesJS can send multiple files
        $uploaded = [];
        // GrapesJS can send 'files[]' or just 'files' or 'file' depending on config
        $files = $request->file('files') ?? $request->file('file');

        if (!$files) {
            return response()->json(['error' => 'No files uploaded'], 400);
        }
        
        // Normalize single vs array
        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            // STRICT VALIDATION
            if (!$file->isValid()) continue;
            
            // Validate mime type manually (server-side check)
            $mime = $file->getMimeType();
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml', 'image/gif'];
            if (!in_array($mime, $allowedMimes)) {
                continue; // Skip invalid files
            }
            
            // Validate Max Size (10MB)
            if ($file->getSize() > 10 * 1024 * 1024) {
                continue; 
            }

            $filename = $file->getClientOriginalName();
            // Sanitization is good practice here
            $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
            
            // Generate unique filename to prevent overwrite if desired, 
            // but user might want to keep names. Let's append hash if exists?
            // For now, standard overwrite behavior or unique ID prefix is safer.
            // Let's prefix with uniqueid to ensure safety against traversal
            $safeName = uniqid() . '_' . $filename;

            // Path: landings/{uuid}/media/imgs/
            // Maintain this structure for landing-specific uploads
            $relativePath = "landings/{$landing->uuid}/media/imgs";
            
            // Store file
            $path = $file->storeAs($relativePath, $safeName, 'public');

            // Metadata
            $size = $file->getSize();
            // Optional: get dimensions
            $width = null;
            $height = null;

            try {
                if ($mime !== 'image/svg+xml') {
                   $dimensions = @getimagesize($file->getRealPath());
                   $width = $dimensions ? $dimensions[0] : null;
                   $height = $dimensions ? $dimensions[1] : null;
                }
            } catch (\Exception $e) {}

            $record = MediaAsset::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'landing_id' => $landing->id,
                    'filename' => $safeName
                ],
                [
                    'relative_path' => $path,
                    'disk' => 'public',
                    'mime_type' => $mime,
                    'size' => $size,
                    'width' => $width,
                    'height' => $height,
                    'source' => 'grapesjs'
                ]
            );

            // GrapesJS expects specific response format for immediate add
            $uploaded[] = [
                'src' => $record->url,
                'type' => 'image',
                'height' => $height,
                'width' => $width,
                'name' => $safeName
            ];
        }

        // Return array directly for GrapesJS compatibility
        return response()->json($uploaded);
    }

    /**
     * Delete media.
     */
    public function destroy(Landing $landing, MediaAsset $media) // Model binding needs careful check if ID matches
    {
        if ($landing->workspace->user_id != Auth::id()) {
            abort(403);
        }
        
        // Allow deleting if it belongs to the user, even if landing_id is different?
        // Strict: only if media.landing_id matches current landing?
        // User said: "User can only see/manage their own media (user_id scope)."
        // Let's allow if user_id matches.
        
        if ($media->user_id != Auth::id()) {
            abort(403);
        }

        // Delete from storage
        if (Storage::disk($media->disk)->exists($media->relative_path)) {
            Storage::disk($media->disk)->delete($media->relative_path);
        }

        $media->delete();

        return response()->json(['success' => true]);
    }
}
