<?php

namespace App\Http\Controllers;

use App\Models\MediaAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaAssetController extends Controller
{
    /**
     * Display the Media Library page.
     */
    public function index()
    {
        return view('media.index');
    }

    /**
     * Get media assets for the API grid.
     */
    public function list(Request $request)
    {
        // Include user's assets AND system assets (e.g. templates backfilled without owner)
        $query = MediaAsset::where(function($q) {
            $q->where('user_id', Auth::id())
              ->orWhereNull('user_id');
        })
        ->with(['landing:id,name', 'template:id,name']);

        // Search
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function($q) use ($term) {
                $q->where('filename', 'like', "%{$term}%")
                  ->orWhereHas('landing', fn($q) => $q->where('name', 'like', "%{$term}%"))
                  ->orWhereHas('template', fn($q) => $q->where('name', 'like', "%{$term}%"));
            });
        }

        // Filters
        if ($request->filled('source') && $request->source !== 'all') {
            $query->where('source', $request->source);
        }

        if ($request->filled('landing_id')) {
            $query->where('landing_id', $request->landing_id);
        }
        
        // Date Filter
        if ($request->filled('range')) {
            $range = $request->range;
            if ($range === '7d') $query->where('created_at', '>=', now()->subDays(7));
            if ($range === '30d') $query->where('created_at', '>=', now()->subDays(30));
        }

        return response()->json($query->latest()->paginate(24));
    }

    /**
     * Handle manual upload from Dashboard.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:102400', // 100MB
                'mimes:jpg,jpeg,png,gif,webp,svg,avif,mp4,webm,mov,m4v,avi,mkv,mp3,wav,ogg,glb,gltf,obj,fbx,stl,usdz,json,bin,wasm,woff,woff2,ttf,otf,css,js,mjs,map,pdf,txt',
            ],
        ]);

        $file = $request->file('file');
        $originalName = pathinfo((string) $file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeBase = Str::slug($originalName, '_');
        if ($safeBase === '') {
            $safeBase = 'asset';
        }
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $filename = $safeBase . '-' . Str::random(8) . ($extension ? ".{$extension}" : '');
        
        // Store in a 'manual' folder for the user
        $userId = Auth::id();
        $path = $file->storeAs("users/{$userId}/media", $filename, 'public');

        $mimeType = strtolower((string) $file->getMimeType());
        $isImage = str_starts_with($mimeType, 'image/');
        $dimensions = $isImage ? @getimagesize($file->getRealPath()) : null;

        $asset = MediaAsset::create([
            'user_id' => $userId,
            // 'landing_id' => $request->landing_id, // Optional: tag to specific landing?
            'filename' => $filename,
            'relative_path' => $path,
            'disk' => 'public',
            'mime_type' => $mimeType ?: null,
            'size' => $file->getSize(),
            'width' => $dimensions ? $dimensions[0] : null,
            'height' => $dimensions ? $dimensions[1] : null,
            'source' => 'manual',
        ]);

        return response()->json($asset->fresh());
    }

    /**
     * Delete asset.
     */
    public function destroy(MediaAsset $media)
    {
        if ($media->user_id != Auth::id()) {
            abort(403);
        }

        if (Storage::disk($media->disk)->exists($media->relative_path)) {
            Storage::disk($media->disk)->delete($media->relative_path);
        }

        $media->delete();

        return response()->json(['success' => true]);
    }
}
