<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\Landing;
use App\Models\LandingPage;
use App\Models\TemplatePage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Services\LicenseService; // Added for remote fetching

class TemplateController extends Controller
{
    public function index(Request $request, LicenseService $licenseService)
    {
        $remoteTemplates = $licenseService->getTemplates();
        
        $templates = collect($remoteTemplates)->map(function ($t) {
            return (object) $t;
        });

        return view('templates.index', compact('templates'));
    }

    public function upload(Request $request)
    {
         // Remote-only implementation - upload disabled for now
         return redirect()->route('templates.index');
    }

    public function import(Request $request, $id, LicenseService $licenseService)
    {
        \Illuminate\Support\Facades\Log::info("Importing template ID: $id");

        // 1. Fetch Template Data
        $templates = $licenseService->getTemplates();
        \Illuminate\Support\Facades\Log::info("Fetched templates count: " . count($templates));

        $templateData = collect($templates)->firstWhere('id', $id);

        if (!$templateData) {
            \Illuminate\Support\Facades\Log::error("Template $id not found in fetched data.");
            return redirect()->route('templates.index')->with('error', 'Template not found or access denied.');
        }

        $template = (object) $templateData;

        // 2. Create Landing
        $user = Auth::user();
        $workspace = $user->workspaces()->first();

        if (!$workspace) {
            $workspace = $user->workspaces()->create(['name' => 'My Workspace']);
        }

        $landingName = $template->name . ' - Copy';
        $slug = Str::slug($landingName) . '-' . Str::random(6);

        $landing = Landing::create([
            'workspace_id' => $workspace->id,
            'template_id' => null, // Remote ID not stored in local DB foreign key
            'name' => $landingName,
            'slug' => $slug,
            'status' => 'draft',
            'uuid' => (string) Str::uuid(),
        ]);

        // 3. Process Content (From JSON)
        $structure = $template->structure; // format: ['html' => ..., 'css' => ...]
        if (is_string($structure)) {
             $structure = json_decode($structure, true);
        }
        
        $html = $structure['html'] ?? '<h1>Empty Template</h1>';
        $css = $structure['css'] ?? '';
        $js = $structure['js'] ?? '';

        // NEW: Process Remote Assets (Download & Index)
        $processed = $this->processRemoteAssets($html, $landing);
        $html = $processed['html'];

        // NEW: Process Head Assets (CSS/JS)
        $customHead = $structure['custom_head'] ?? '';
        if (!empty($customHead)) {
            $processedHead = $this->processHeadAssets($customHead, $landing);
            
            // Save to Settings
            $landing->settings()->updateOrCreate(
                [],
                ['custom_head_scripts' => $processedHead]
            );
        }

        // 4. Create Landing Pages
        LandingPage::create([
            'landing_id' => $landing->id,
            'type' => 'index',
            'name' => 'Home',
            'slug' => 'index',
            'status' => 'draft',
            'html' => $html,
            'css' => $css,
            'js' => $js,
        ]);

        // 5. Create Default Pages
        $this->createDefaultPages($landing);

        return redirect()->route('landings.show', $landing)->with('success', 'Template imported successfully.');
    }

    protected function processRemoteAssets($html, Landing $landing)
    {
        if (empty($html)) return ['html' => ''];

        // Aggressively remove SCRIPT tags from Body HTML to avoid duplication (since we moved them to Head)
        // This regex removes ALL script tags.
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $html = preg_replace('/\s+on[a-z]+\s*=\s*(?:".*?"|\'.*?\'|[^\s>]+)/i', '', $html);

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $images = $dom->getElementsByTagName('img');
        $baseStoragePath = "landings/{$landing->uuid}"; // Relative to public disk root
        $fullStoragePath = storage_path("app/public/{$baseStoragePath}");

        if (!File::exists($fullStoragePath)) {
            File::makeDirectory($fullStoragePath, 0755, true);
        }

        foreach ($images as $img) {
            $src = $img->getAttribute('src');
            
            // Only process remote URLs (http/https)
            if (preg_match('/^https?:\/\//', $src)) {
                try {
                    // Generate a filename
                    $extension = pathinfo(parse_url($src, PHP_URL_PATH), PATHINFO_EXTENSION);
                    if (!$extension) $extension = 'png'; // Fallback
                    // Clean query params if any
                    $extension = explode('?', $extension)[0];
                    
                    $filename = 'imported_' . uniqid() . '.' . $extension;
                    $relativePath = "{$baseStoragePath}/{$filename}";
                    
                    // Download Content
                    $content = @file_get_contents($src);
                    
                    if ($content !== false) {
                        // Save to Storage
                        Storage::disk('public')->put($relativePath, $content);
                        
                        // Create MediaAsset
                        $size = strlen($content);
                        list($width, $height) = @getimagesizefromstring($content);
                        $mime = 'image/' . ($extension === 'jpg' ? 'jpeg' : $extension); // Rough guess
                        
                        \App\Models\MediaAsset::create([
                            'landing_id' => $landing->id,
                            'user_id' => Auth::id(),
                            'filename' => $filename,
                            'disk' => 'public',
                            'relative_path' => $relativePath,
                            'mime_type' => $mime,
                            'size' => $size,
                            'width' => $width,
                            'height' => $height,
                            'source' => 'import',
                        ]);

                        // Rewrite SRC to local URL
                        $newSrc = Storage::url($relativePath);
                        $img->setAttribute('src', $newSrc);
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("Failed to download asset: $src - " . $e->getMessage());
                }
            }
        }

        // Return modified HTML
        return [
            'html' => $dom->saveHTML()
        ];
    }

    protected function createDefaultPages(Landing $landing) 
    {
         // 7. Create Checkout Page
        LandingPage::create([
            'landing_id' => $landing->id,
            'type' => 'checkout',
            'name' => 'Checkout',
            'slug' => 'checkout',
            'status' => 'draft',
            'html' => '<div class="container mx-auto px-4 py-8"><h1 class="text-3xl font-bold mb-4">Checkout</h1><p>Dynamic Checkout Form will appear here.</p></div>',
            'css' => '',
            'js' => '',
        ]);

        // 8. Create Thank You Page
        LandingPage::create([
            'landing_id' => $landing->id,
            'type' => 'thankyou',
            'name' => 'Thank You',
            'slug' => 'thank-you',
            'status' => 'draft',
            'html' => '<div class="bg-gray-50 min-h-screen flex items-center justify-center"><h1>Thank You</h1></div>',
             'css' => '',
             'js' => '',
        ]);
    }

    protected function processHeadAssets($html, Landing $landing)
    {
        if (empty($html)) return '';

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        // Head often lacks a root, so wrap strictly for parsing then unwrap
        $dom->loadHTML('<?xml encoding="UTF-8"><root>' . $html . '</root>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        
        $baseStoragePath = "landings/{$landing->uuid}/assets"; 
        $fullStoragePath = storage_path("app/public/{$baseStoragePath}");

        if (!File::exists($fullStoragePath)) {
            File::makeDirectory($fullStoragePath, 0755, true);
        }

        // 1. Process Links (CSS)
        foreach ($dom->getElementsByTagName('link') as $link) {
            $href = $link->getAttribute('href');
            if ($href && preg_match('/^https?:\/\//', $href)) {
                 $newUrl = $this->downloadAndStore($href, $fullStoragePath, $baseStoragePath, $landing);
                 if ($newUrl) $link->setAttribute('href', $newUrl);
            }
        }

        // 2. Process Scripts (JS)
        foreach ($dom->getElementsByTagName('script') as $script) {
            $src = $script->getAttribute('src');
            if ($src && preg_match('/^https?:\/\//', $src)) {
                 $newUrl = $this->downloadAndStore($src, $fullStoragePath, $baseStoragePath, $landing);
                 if ($newUrl) $script->setAttribute('src', $newUrl);
            }
        }

        // Unwrap content from <root>
        $output = '';
        $root = $dom->getElementsByTagName('root')->item(0);
        if ($root) {
            foreach ($root->childNodes as $child) {
                $output .= $dom->saveHTML($child);
            }
        }
        return $output;
    }

    protected function downloadAndStore($url, $fullPath, $relativePathBase, $landing)
    {
        try {
            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (!$extension) $extension = 'bin';
            $extension = explode('?', $extension)[0]; // Clean
            
            $filename = md5($url) . '.' . $extension;
            $relativePath = "{$relativePathBase}/{$filename}";
            $absolutePath = $fullPath . '/' . $filename;
            
            // Check if already exists
            if (File::exists($absolutePath)) {
                return Storage::url($relativePath);
            }

            // Use Http facade for better control/logging
            $response = \Illuminate\Support\Facades\Http::timeout(10)->get($url);
            
            if ($response->successful()) {
                Storage::disk('public')->put($relativePath, $response->body());
                return Storage::url($relativePath);
            } else {
                 \Illuminate\Support\Facades\Log::warning("Failed to download asset: $url - Status: " . $response->status());
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to download asset: $url - " . $e->getMessage());
        }
        return null; // Return null to keep original URL on failure
    }
}
