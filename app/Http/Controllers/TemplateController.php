<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\Landing;
use App\Models\LandingPage;
use App\Models\TemplatePage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Services\LicenseService;

class TemplateController extends Controller
{
    public function index(Request $request, LicenseService $licenseService)
    {
        $remoteTemplates = collect($licenseService->getTemplates());
        $excluded = [];

        $filteredRemote = $remoteTemplates->filter(function ($template) use (&$excluded) {
            $reason = null;
            $include = $this->isRealTemplateRecord($template, $reason);
            if (!$include) {
                $excluded[] = [
                    'id' => $this->extractTemplateField($template, 'id'),
                    'reason' => $reason,
                    'type' => $this->extractTemplateField($template, 'type'),
                    'source' => $this->extractTemplateField($template, 'source'),
                    'is_template' => $this->extractTemplateField($template, 'is_template'),
                    'category' => $this->extractTemplateField($template, 'category'),
                    'status' => $this->extractTemplateField($template, 'status'),
                    'visibility' => $this->extractTemplateField($template, 'visibility'),
                ];
            }
            return $include;
        })->map(function ($t) {
            $t = (array) $t;
            $t['id'] = 'remote-' . ($t['id'] ?? '');
            $t['import_source'] = 'remote';
            return (object) $t;
        })->values();

        $localTemplates = Template::query()
            ->where('is_active', true)
            ->withCount('pages')
            ->latest()
            ->get()
            ->map(function (Template $template) {
                return (object) [
                    'id' => 'local-' . $template->id,
                    'import_source' => 'local',
                    'name' => $template->name,
                    'description' => $template->description,
                    'thumbnail_url' => $template->preview_image_path ? Storage::url($template->preview_image_path) : null,
                ];
            });

        $templates = $localTemplates->concat($filteredRemote)->values();

        Log::info('Templates query executed', [
            'local_count' => $localTemplates->count(),
            'remote_raw_count' => $remoteTemplates->count(),
            'remote_filtered_count' => $filteredRemote->count(),
            'excluded_count' => count($excluded),
            'excluded_samples' => array_slice($excluded, 0, 10),
            'reason' => 'templates_index_filters_applied',
        ]);

        return view('templates.index', compact('templates'));
    }

    public function upload(Request $request)
    {
         // Remote-only implementation - upload disabled for now
         return redirect()->route('templates.index');
    }

    public function import(Request $request, $id, LicenseService $licenseService)
    {
        set_time_limit(0); // Prevent timeout for large templates
        ini_set('memory_limit', '512M');
        
        Log::info("Starting template import for ID: $id");

        $id = (string) $id;

        if (str_starts_with($id, 'local-')) {
            $localId = (int) str_replace('local-', '', $id);
            return $this->importLocalTemplate($localId);
        }

        $remoteId = str_starts_with($id, 'remote-')
            ? str_replace('remote-', '', $id)
            : $id;

        // 1. Fetch Template Data
        $templates = $licenseService->getTemplates();
        Log::info("Fetched templates count: " . count($templates));

        $templateData = collect($templates)->firstWhere('id', $remoteId);

        if ($templateData && !$this->isRealTemplateRecord($templateData, $reason)) {
            Log::warning('Template import blocked by filter', [
                'template_id' => $remoteId,
                'reason' => $reason,
            ]);
            return redirect()->route('templates.index')->with('error', 'This record is not a valid template.');
        }

        if (!$templateData) {
            Log::error("Template $remoteId not found in fetched data.");
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
            'template_id' => null,
            'name' => $landingName,
            'slug' => $slug,
            'status' => 'draft',
            'uuid' => (string) Str::uuid(),
        ]);

        // 3. Process Content (From JSON)
        $structure = $template->structure;
        if (is_string($structure)) {
             $structure = json_decode($structure, true);
        }
        
        $html = $structure['html'] ?? '<h1>Empty Template</h1>';
        $css = $structure['css'] ?? '';
        $js = $structure['js'] ?? '';

        // Standardize base storage path for all assets
        $baseStoragePath = "landings/{$landing->uuid}/assets";
        $fullStoragePath = storage_path("app/public/{$baseStoragePath}");
        if (!File::exists($fullStoragePath)) {
            File::makeDirectory($fullStoragePath, 0755, true);
        }

        // Process Remote Assets (Download & Index) — handles ALL resource types + importmaps
        $processed = $this->processRemoteAssets($html, $landing, $fullStoragePath, $baseStoragePath);
        $html = $processed['html'];
        $extractedJs = $processed['body_scripts'] ?? '';

        // Merge extracted body scripts with any existing JS
        if (!empty($extractedJs)) {
            $js = $js . "\n" . $extractedJs;
        }

        // Process Head Assets (CSS/JS from custom_head)
        $customHead = $structure['custom_head'] ?? '';
        if (!empty($customHead)) {
            $processedHead = $this->processHeadAssets($customHead, $landing, $fullStoragePath, $baseStoragePath);
            
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

    protected function importLocalTemplate(int $templateId)
    {
        $template = Template::with('pages')->find($templateId);
        if (!$template || !$template->is_active) {
            return redirect()->route('templates.index')->with('error', 'Template not found.');
        }

        $user = Auth::user();
        $workspace = $user->workspaces()->first();
        if (!$workspace) {
            $workspace = $user->workspaces()->create(['name' => 'My Workspace']);
        }

        $landingName = $template->name . ' - Copy';
        $slug = Str::slug($landingName) . '-' . Str::random(6);

        $landing = Landing::create([
            'workspace_id' => $workspace->id,
            'template_id' => $template->id,
            'name' => $landingName,
            'slug' => $slug,
            'status' => 'draft',
            'uuid' => (string) Str::uuid(),
            'content_type' => 'landing',
            'source' => 'template',
            'is_template' => false,
            'category' => 'imported',
            'visibility' => 'private',
        ]);

        foreach ($template->pages as $page) {
            LandingPage::create([
                'landing_id' => $landing->id,
                'type' => $page->type,
                'name' => $page->name,
                'slug' => $page->slug,
                'status' => 'draft',
                'html' => $page->html,
                'css' => $page->css,
                'js' => $page->js,
                'grapesjs_json' => $page->grapesjs_json,
            ]);
        }

        Log::info('Local template imported as landing', [
            'template_id' => $template->id,
            'landing_id' => $landing->id,
            'reason' => 'explicit_template_import',
        ]);

        return redirect()->route('landings.show', $landing)->with('success', 'Template imported successfully.');
    }

    private function isRealTemplateRecord($template, ?string &$reason = null): bool
    {
        $type = strtolower((string) ($this->extractTemplateField($template, 'type', 'template') ?? 'template'));
        $source = strtolower((string) ($this->extractTemplateField($template, 'source', '') ?? ''));
        $category = strtolower((string) ($this->extractTemplateField($template, 'category', '') ?? ''));
        $isTemplate = $this->extractTemplateField($template, 'is_template', null);
        $status = strtolower((string) ($this->extractTemplateField($template, 'status', '') ?? ''));

        if ($type !== '' && $type !== 'template') {
            $reason = "type={$type}";
            return false;
        }

        if (is_bool($isTemplate) && $isTemplate === false) {
            $reason = "is_template=false";
            return false;
        }

        if (in_array($source, ['ai', 'generated', 'landing'], true)) {
            $reason = "source={$source}";
            return false;
        }

        if (in_array($category, ['ai', 'generated', 'landing'], true)) {
            $reason = "category={$category}";
            return false;
        }

        if (in_array($status, ['draft', 'published'], true) && $type !== 'template') {
            $reason = "landing_status={$status}";
            return false;
        }

        return true;
    }

    private function extractTemplateField($template, string $field, $default = null)
    {
        if (is_array($template)) {
            return $template[$field] ?? $default;
        }
        if (is_object($template)) {
            return $template->{$field} ?? $default;
        }
        return $default;
    }

    /**
     * Process ALL remote assets in body HTML.
     */
    protected function processRemoteAssets($html, Landing $landing, $fullStoragePath, $baseStoragePath)
    {
        if (empty($html)) return ['html' => '', 'body_scripts' => ''];

        // Security: Remove inline event handlers
        $html = preg_replace('/\s+on[a-z]+\s*=\s*(?:".*?"|\'.*?\'|[^\s>]+)/i', '', $html);

        // === Phase 1: Extract & Rewrite <script> tags ===
        $extractedScripts = [];
        $html = preg_replace_callback('/<script\b([^>]*)>(.*?)<\/script>/is', function($match) use (&$extractedScripts, $fullStoragePath, $baseStoragePath, $landing) {
            $attrs = $match[1];
            $content = $match[2];
            
            // 1. Check for importmap (modern templates)
            if (preg_match('/type\s*=\s*["\']importmap["\']/i', $attrs)) {
                Log::info("Found importmap, processing URLs...");
                $importmap = json_decode($content, true);
                if ($importmap && isset($importmap['imports'])) {
                    foreach ($importmap['imports'] as $key => $src) {
                        if (preg_match('/^https?:\/\//', $src)) {
                            // SKIP FOLDER MAPPINGS: If key or src ends in '/', keep it remote.
                            // The browser requires folder mappings to end in '/' on both sides.
                            // Flattening them into a single local file breaks the mapping.
                            if (str_ends_with($key, '/') || str_ends_with($src, '/')) {
                                Log::info("Skipping folder-based importmap entry: $key -> $src (keeping remote)");
                                continue;
                            }

                            $localUrl = $this->downloadAndStore($src, $fullStoragePath, $baseStoragePath, $landing);
                            if ($localUrl) $importmap['imports'][$key] = $localUrl;
                        }
                    }
                    $content = json_encode($importmap, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                }
                $extractedScripts[] = "<script type=\"importmap\">{$content}</script>";
                return '';
            }

            // 2. Check for External Scripts
            if (preg_match('/\bsrc\s*=\s*["\']([^"\']+)["\']/i', $attrs, $srcMatch)) {
                $src = $srcMatch[1];
                if (preg_match('/^https?:\/\//', $src)) {
                    $localUrl = $this->downloadAndStore($src, $fullStoragePath, $baseStoragePath, $landing);
                    if ($localUrl) {
                        $attrs = preg_replace('/\bsrc\s*=\s*["\'][^"\']+["\']/i', 'src="' . $localUrl . '"', $attrs);
                    }
                }
                $extractedScripts[] = "<script{$attrs}></script>";
                return '';
            } 
            
            // 3. Check for Inline Module Scripts (import ... from)
            if (preg_match('/type=["\']module["\']/i', $attrs) && trim($content)) {
                // Rewrite inline imports
                $content = preg_replace_callback('/import\s+.*?\s+from\s+["\'](https?:\/\/[^"\']+)["\']/is', function($imatch) use ($fullStoragePath, $baseStoragePath, $landing) {
                    $localUrl = $this->downloadAndStore($imatch[1], $fullStoragePath, $baseStoragePath, $landing);
                    return $localUrl ? str_replace($imatch[1], $localUrl, $imatch[0]) : $imatch[0];
                }, $content);
            }

            // Preserve inline scripts
            if (trim($content)) {
                $extractedScripts[] = "<script{$attrs}>{$content}</script>";
            }
            
            return '';
        }, $html);

        // === Phase 2: DOM Processing ===
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        // Process <link rel="stylesheet">
        foreach (iterator_to_array($dom->getElementsByTagName('link')) as $link) {
            $rel = $link->getAttribute('rel');
            $href = $link->getAttribute('href');
            if (($rel === 'stylesheet' || $rel === 'preload') && $href && preg_match('/^https?:\/\//', $href)) {
                $localUrl = $this->downloadAndStore($href, $fullStoragePath, $baseStoragePath, $landing, $rel === 'stylesheet');
                if ($localUrl) $link->setAttribute('href', $localUrl);
            }
        }

        // Process <img> Tags
        foreach (iterator_to_array($dom->getElementsByTagName('img')) as $img) {
            foreach (['src', 'data-src'] as $attr) {
                $src = $img->getAttribute($attr);
                if ($src && preg_match('/^https?:\/\//', $src)) {
                    $localUrl = $this->downloadAndStoreImage($src, $baseStoragePath, $landing);
                    if ($localUrl) $img->setAttribute($attr, $localUrl);
                }
            }
        }

        // Process Media Tags
        foreach (['video', 'audio', 'source'] as $tagName) {
            foreach (iterator_to_array($dom->getElementsByTagName($tagName)) as $el) {
                foreach (['src', 'poster'] as $attr) {
                    $val = $el->getAttribute($attr);
                    if ($val && preg_match('/^https?:\/\//', $val)) {
                        $localUrl = $this->downloadAndStore($val, $fullStoragePath, $baseStoragePath, $landing);
                        if ($localUrl) $el->setAttribute($attr, $localUrl);
                    }
                }
            }
        }

        $processedHtml = $dom->saveHTML();
        $processedHtml = preg_replace('/^<\?xml[^>]*\?>/', '', $processedHtml);

        return [
            'html' => trim($processedHtml),
            'body_scripts' => implode("\n", $extractedScripts),
        ];
    }

    /**
     * Download an image, store it, and create a MediaAsset record.
     */
    protected function downloadAndStoreImage($url, $baseStoragePath, Landing $landing)
    {
        try {
            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
            if ($extension) $extension = explode('?', $extension)[0];
            
            $filename = 'imported_' . md5($url) . '.' . ($extension ?: 'png');
            $relativePath = "{$baseStoragePath}/{$filename}";
            $fullPath = storage_path("app/public/{$relativePath}");

            if (File::exists($fullPath)) return Storage::url($relativePath);
            
            $content = $this->downloadWithRetry($url, $mimeType);
            
            if ($content !== null) {
                if (!$extension && $mimeType) {
                    $extension = $this->mimeToExtension($mimeType);
                    $filename = 'imported_' . md5($url) . '.' . $extension;
                    $relativePath = "{$baseStoragePath}/{$filename}";
                }

                Storage::disk('public')->put($relativePath, $content);
                
                \App\Models\MediaAsset::create([
                    'landing_id' => $landing->id,
                    'user_id' => Auth::id(),
                    'filename' => $filename,
                    'disk' => 'public',
                    'relative_path' => $relativePath,
                    'mime_type' => $mimeType ?? 'image/png',
                    'size' => strlen($content),
                    'source' => 'import',
                ]);

                return Storage::url($relativePath);
            }
        } catch (\Exception $e) {
            Log::warning("Failed to download image: $url - " . $e->getMessage());
        }
        return null;
    }

    protected function createDefaultPages(Landing $landing) 
    {
        LandingPage::create([
            'landing_id' => $landing->id, 'type' => 'checkout', 'name' => 'Checkout', 'slug' => 'checkout', 'status' => 'draft',
            'html' => '<div class="container mx-auto px-4 py-8"><h1 class="text-3xl font-bold mb-4">Checkout</h1><p>Dynamic Checkout Form will appear here.</p></div>',
        ]);
        LandingPage::create([
            'landing_id' => $landing->id, 'type' => 'thankyou', 'name' => 'Thank You', 'slug' => 'thank-you', 'status' => 'draft',
            'html' => '<div class="bg-gray-50 min-h-screen flex items-center justify-center"><h1>Thank You</h1></div>',
        ]);
    }

    /**
     * Process head assets (CSS/JS from custom_head).
     */
    protected function processHeadAssets($html, Landing $landing, $fullStoragePath, $baseStoragePath)
    {
        if (empty($html)) return '';

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><root>' . $html . '</root>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        // Process Links (CSS)
        foreach (iterator_to_array($dom->getElementsByTagName('link')) as $link) {
            $href = $link->getAttribute('href');
            if ($href && preg_match('/^https?:\/\//', $href)) {
                 $newUrl = $this->downloadAndStore($href, $fullStoragePath, $baseStoragePath, $landing, true);
                 if ($newUrl) $link->setAttribute('href', $newUrl);
            }
        }

        // Process Scripts (JS)
        foreach (iterator_to_array($dom->getElementsByTagName('script')) as $script) {
            $src = $script->getAttribute('src');
            if ($src && preg_match('/^https?:\/\//', $src)) {
                 $newUrl = $this->downloadAndStore($src, $fullStoragePath, $baseStoragePath, $landing);
                 if ($newUrl) $script->setAttribute('src', $newUrl);
            }
        }

        $output = '';
        $root = $dom->getElementsByTagName('root')->item(0);
        if ($root) {
            foreach ($root->childNodes as $child) {
                $output .= $dom->saveHTML($child);
            }
        }
        return $output;
    }

    /**
     * Download a remote file and store it locally.
     */
    protected function downloadAndStore($url, $fullPath, $relativePathBase, $landing, $isCss = false)
    {
        Log::info("Asset downloading: $url");
        try {
            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
            if ($extension) $extension = explode('?', $extension)[0];
            
            $content = $this->downloadWithRetry($url, $mimeType);
            if ($content === null) return null;

            // CONTENT VALIDATION: Detect 404/Error HTML pages saved as JS/CSS
            if (($isCss || preg_match('/\.js(\?|$)/i', $url)) && preg_match('/^\s*<!DOCTYPE\b/i', $content)) {
                Log::warning("Skipping asset $url: Server returned HTML (likely 404/error) instead of the requested resource.");
                return null;
            }

            if (!$extension || $extension === 'bin') {
                $extension = $this->mimeToExtension($mimeType);
                if (!$extension) $extension = $isCss ? 'css' : (preg_match('/\.js(\?|$)/i', $url) ? 'js' : 'bin');
            }

            $filename = md5($url) . '.' . $extension;
            $relativePath = "{$relativePathBase}/{$filename}";
            $absolutePath = $fullPath . '/' . $filename;
            
            if (File::exists($absolutePath)) return Storage::url($relativePath);

            // Rewrite CSS internal refs
            if ($isCss || strtolower($extension) === 'css') {
                $baseDir = dirname($url);
                $content = preg_replace_callback('/url\(["\']?(?!data:|https?:\/\/|\/\/)([^"\')\s]+)["\']?\)/i', function($match) use ($baseDir, $fullPath, $relativePathBase, $landing) {
                    $assetUrl = $baseDir . '/' . $match[1];
                    $localUrl = $this->downloadAndStore($assetUrl, $fullPath, $relativePathBase, $landing);
                    return $localUrl ? "url('{$localUrl}')" : $match[0];
                }, $content);
            }

            Storage::disk('public')->put($relativePath, $content);
            return Storage::url($relativePath);
        } catch (\Exception $e) {
            Log::warning("Failed to download asset: $url - " . $e->getMessage());
        }
        return null;
    }

    protected function downloadWithRetry($url, &$mimeType = null, $maxRetries = 2)
    {
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(15)->withOptions(['verify' => false])->get($url);
                if ($response->successful()) {
                    $mimeType = $response->header('Content-Type');
                    return $response->body();
                }
            } catch (\Exception $e) { }
            if ($attempt < $maxRetries) usleep(500000);
        }
        return null;
    }

    protected function mimeToExtension($mime)
    {
        $map = [
            'application/javascript' => 'js', 'application/x-javascript' => 'js', 'text/javascript' => 'js', 
            'text/css' => 'css', 'text/plain' => null, // Let fallback decide for text/plain
            'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp',
            'image/svg+xml' => 'svg', 'application/json' => 'json', 
            'font/woff' => 'woff', 'font/woff2' => 'woff2', 'font/ttf' => 'ttf', 'font/otf' => 'otf',
            'application/font-woff' => 'woff', 'application/font-woff2' => 'woff2',
            'application/x-font-ttf' => 'ttf', 'application/x-font-opentype' => 'otf',
        ];
        $baseMime = explode(';', $mime)[0];
        return $map[$baseMime] ?? null;
    }
}
