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
use Illuminate\Support\Facades\DB;
use App\Services\LicenseService;

class TemplateController extends Controller
{
    public function proxyTemplateAsset(Request $request)
    {
        $validated = $request->validate([
            'u' => 'required|url',
        ]);

        $url = (string) $validated['u'];
        $parsed = parse_url($url);
        $host = strtolower((string) ($parsed['host'] ?? ''));
        $path = (string) ($parsed['path'] ?? '');

        // Determine correct content type early so we can fail gracefully without HTML
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $fallbackType = match ($extension) {
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            default => 'text/plain',
        };

        // Helper to fail safely (returns empty content or comment, preventing JS/CSS syntax errors)
        $failSafe = function () use ($fallbackType) {
            $body = str_contains($fallbackType, 'css') || str_contains($fallbackType, 'javascript') 
                ? '/* Asset Proxy Failed or Forbidden */' 
                : '';
                
            return response($body, 200, [
                'Content-Type' => $fallbackType,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ]);
        };

        if ($host === '' || $path === '') {
            return $failSafe();
        }

        // Allow only expected licensing/template hosts (prevents open proxy abuse)
        $allowedHosts = [];
        $licensingBase = (string) env('LICENSING_SERVER_URL', '');
        if ($licensingBase !== '') {
            $licensingHost = parse_url($licensingBase, PHP_URL_HOST);
            if ($licensingHost) {
                $allowedHosts[] = strtolower((string) $licensingHost);
            }
        }

        $extraAllowed = array_filter(array_map('trim', explode(',', (string) env('TEMPLATE_ASSET_PROXY_ALLOWED_HOSTS', ''))));
        foreach ($extraAllowed as $h) {
            $allowedHosts[] = strtolower($h);
        }

        $allowedHosts = array_values(array_unique($allowedHosts));

        $hostAllowed = empty($allowedHosts) || in_array($host, $allowedHosts, true);
        if (!$hostAllowed || !str_contains($path, '/storage/templates/')) {
            return $failSafe();
        }

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->withOptions(['verify' => false])
                ->get($url);

            if (!$response->successful()) {
                return $failSafe();
            }

            $contentType = $response->header('Content-Type') ?: $fallbackType;

            return response($response->body(), 200, [
                'Content-Type' => $contentType,
                'Cache-Control' => 'public, max-age=86400',
            ]);
        } catch (\Exception $e) {
            return $failSafe();
        }
    }

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
        $assetBaseUrl = $template->asset_base_url ?? null;

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
            'content_type' => 'landing',
            'source' => 'remote-template:' . $remoteId,
            'is_template' => false,
            'category' => 'imported',
            'visibility' => 'private',
        ]);

        // 3. Process Content (From JSON)
        $structure = $template->structure;
        if (is_string($structure)) {
             $structure = json_decode($structure, true);
        }
        if (empty($assetBaseUrl)) {
            $assetBaseUrl = $this->inferAssetBaseUrlFromStructure(is_array($structure) ? $structure : []);
        }
        
        $html = $structure['html'] ?? '<h1>Empty Template</h1>';
        $css = $structure['css'] ?? '';
        $js = $structure['js'] ?? '';

        // Standardize base storage path for all assets
        $baseStoragePath = "landings/{$landing->uuid}/assets";
        $fullStoragePath = storage_path("app/public/landings/{$landing->uuid}/assets");
        $baseStoragePath = "landings/{$landing->uuid}/assets";
        File::ensureDirectoryExists($fullStoragePath);

        $processed = $this->processRemoteAssets($structure['html'] ?? '', $landing, $fullStoragePath, $baseStoragePath, $assetBaseUrl);
        $css = $this->processRemoteCss($structure['css'] ?? '', $landing, $fullStoragePath, $baseStoragePath, $assetBaseUrl);
        $html = $processed['html'];
        $extractedJs = $processed['body_scripts'] ?? '';

        // Merge extracted body scripts with any existing JS
        if (!empty($extractedJs)) {
            $js = $js . "\n" . $extractedJs;
        }

        // Process Head Assets (CSS/JS from custom_head)
        $customHead = trim((string) ($structure['custom_head'] ?? ''));
        if (!empty($processed['head_assets'])) {
            $customHead = trim($customHead . "\n" . $processed['head_assets']);
        }
        if (!empty($customHead)) {
            $processedHead = $this->processHeadAssets($customHead, $landing, $fullStoragePath, $baseStoragePath, $assetBaseUrl);
            
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
            'source' => 'local-template:' . $template->id,
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

    public function syncLandingTemplate(Request $request, Landing $landing, LicenseService $licenseService)
    {
        if ($landing->workspace->user_id != Auth::id()) {
            abort(403);
        }

        try {
            if ($landing->template_id) {
                $result = DB::transaction(function () use ($landing) {
                    return $this->syncFromLocalTemplate($landing, (int) $landing->template_id);
                });

                return back()->with('status', "Template synchronized. Updated {$result['updated']} page(s), created {$result['created']} page(s).");
            }

            $remoteId = $this->extractRemoteTemplateId($landing->source);

            if (!$remoteId) {
                $remoteId = $this->guessRemoteTemplateIdFromLanding($landing, $licenseService->getTemplates());
                if ($remoteId) {
                    $landing->update(['source' => 'remote-template:' . $remoteId]);
                }
            }

            if (!$remoteId) {
                return back()->with('error', 'This landing is not linked to a synchronizable source template.');
            }

            $result = DB::transaction(function () use ($landing, $remoteId, $licenseService) {
                return $this->syncFromRemoteTemplate($landing, $remoteId, $licenseService);
            });

            return back()->with('status', "Template synchronized. Updated {$result['updated']} page(s), created {$result['created']} page(s).");
        } catch (\Throwable $e) {
            Log::error('Template sync failed', [
                'landing_id' => $landing->id,
                'template_id' => $landing->template_id,
                'source' => $landing->source,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to synchronize template: ' . $e->getMessage());
        }
    }

    protected function syncFromLocalTemplate(Landing $landing, int $templateId): array
    {
        $template = Template::with('pages')->find($templateId);
        if (!$template || !$template->is_active) {
            throw new \RuntimeException('Local template not found or inactive.');
        }

        $updated = 0;
        $created = 0;

        foreach ($template->pages as $templatePage) {
            $landingPage = $landing->pages()->where('slug', $templatePage->slug)->first();
            if (!$landingPage) {
                $landingPage = $landing->pages()->where('type', $templatePage->type)->first();
            }

            $payload = [
                'html' => $templatePage->html,
                'css' => $templatePage->css,
                'js' => $templatePage->js,
                'grapesjs_json' => $templatePage->grapesjs_json,
            ];

            if ($landingPage) {
                $landingPage->update($payload);
                $updated++;
                continue;
            }

            LandingPage::create(array_merge($payload, [
                'landing_id' => $landing->id,
                'type' => $templatePage->type,
                'name' => $templatePage->name,
                'slug' => $templatePage->slug,
                'status' => 'draft',
            ]));
            $created++;
        }

        if (!str_starts_with((string) $landing->source, 'local-template:')) {
            $landing->update(['source' => 'local-template:' . $templateId]);
        }

        return ['updated' => $updated, 'created' => $created];
    }

    protected function syncFromRemoteTemplate(Landing $landing, string $remoteId, LicenseService $licenseService): array
    {
        $templates = $licenseService->getTemplates();
        $templateData = collect($templates)->firstWhere('id', $remoteId);

        if (!$templateData) {
            throw new \RuntimeException('Remote template not found or access denied.');
        }

        $reason = null;
        if (!$this->isRealTemplateRecord($templateData, $reason)) {
            throw new \RuntimeException("Remote template blocked by filter ({$reason}).");
        }

        $template = (object) $templateData;
        $assetBaseUrl = $template->asset_base_url ?? null;

        $structure = $template->structure;
        if (is_string($structure)) {
            $structure = json_decode($structure, true);
        }
        if (!is_array($structure)) {
            $structure = [];
        }

        if (empty($assetBaseUrl)) {
            $assetBaseUrl = $this->inferAssetBaseUrlFromStructure($structure);
        }

        $fullStoragePath = storage_path("app/public/landings/{$landing->uuid}/assets");
        $baseStoragePath = "landings/{$landing->uuid}/assets";
        File::ensureDirectoryExists($fullStoragePath);

        $processed = $this->processRemoteAssets($structure['html'] ?? '', $landing, $fullStoragePath, $baseStoragePath, $assetBaseUrl);
        $css = $this->processRemoteCss($structure['css'] ?? '', $landing, $fullStoragePath, $baseStoragePath, $assetBaseUrl);
        $js = (string) ($structure['js'] ?? '');
        $extractedJs = $processed['body_scripts'] ?? '';
        if (!empty($extractedJs)) {
            $js = trim($js . "\n" . $extractedJs);
        }

        $customHead = trim((string) ($structure['custom_head'] ?? ''));
        if (!empty($processed['head_assets'])) {
            $customHead = trim($customHead . "\n" . $processed['head_assets']);
        }
        if (!empty($customHead)) {
            $processedHead = $this->processHeadAssets($customHead, $landing, $fullStoragePath, $baseStoragePath, $assetBaseUrl);
            $landing->settings()->updateOrCreate([], ['custom_head_scripts' => $processedHead]);
        }

        $indexPage = $landing->pages()->where('type', 'index')->first();
        $created = 0;
        if (!$indexPage) {
            $indexPage = LandingPage::create([
                'landing_id' => $landing->id,
                'type' => 'index',
                'name' => 'Home',
                'slug' => 'index',
                'status' => 'draft',
            ]);
            $created = 1;
        }

        $indexPage->update([
            'html' => $processed['html'],
            'css' => $css,
            'js' => $js,
            'grapesjs_json' => null,
        ]);

        $landing->update([
            'source' => 'remote-template:' . $remoteId,
            'content_type' => $landing->content_type ?: 'landing',
            'is_template' => false,
            'category' => $landing->category ?: 'imported',
            'visibility' => $landing->visibility ?: 'private',
        ]);

        return ['updated' => 1, 'created' => $created];
    }

    protected function extractRemoteTemplateId(?string $source): ?string
    {
        $source = (string) $source;
        if (!str_starts_with($source, 'remote-template:')) {
            return null;
        }

        $remoteId = trim(substr($source, strlen('remote-template:')));
        return $remoteId !== '' ? $remoteId : null;
    }

    protected function guessRemoteTemplateIdFromLanding(Landing $landing, array $templates): ?string
    {
        $landingName = preg_replace('/\s*-\s*copy$/i', '', (string) $landing->name);

        $matches = collect($templates)->filter(function ($template) use ($landingName) {
            $templateName = trim((string) $this->extractTemplateField($template, 'name', ''));
            return $templateName !== '' && strcasecmp($templateName, trim($landingName)) === 0;
        })->values();

        if ($matches->count() === 1) {
            return (string) $this->extractTemplateField($matches->first(), 'id', '');
        }

        return null;
    }

    private function isRealTemplateRecord($template, ?string &$reason = null): bool
    {
        $type = strtolower((string) ($this->extractTemplateField($template, 'type', 'template') ?? 'template'));
        $source = strtolower((string) ($this->extractTemplateField($template, 'source', '') ?? ''));
        $category = strtolower((string) ($this->extractTemplateField($template, 'category', '') ?? ''));
        $isTemplate = $this->extractTemplateField($template, 'is_template', null);
        $status = strtolower((string) ($this->extractTemplateField($template, 'status', '') ?? ''));

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
    protected function processRemoteAssets($html, Landing $landing, $fullStoragePath, $baseStoragePath, $assetBaseUrl = null)
    {
        if (empty($html)) return ['html' => '', 'body_scripts' => ''];

        // Security: Remove inline event handlers
        $html = preg_replace('/\s+on[a-z]+\s*=\s*(?:".*?"|\'.*?\'|[^\s>]+)/i', '', $html);

        // === Phase 1: Extract & Rewrite <script> tags ===
        $extractedScripts = [];
        $html = preg_replace_callback('/<script\b([^>]*)>(.*?)<\/script>/is', function($match) use (&$extractedScripts, $fullStoragePath, $baseStoragePath, $landing, $assetBaseUrl) {
            $attrs = $match[1];
            $content = $match[2];
            
            // 1. Check for importmap (modern templates)
            if (preg_match('/type\s*=\s*["\']importmap["\']/i', $attrs)) {
                Log::info("Found importmap, processing URLs...");
                $importmap = json_decode($content, true);
                if ($importmap && isset($importmap['imports'])) {
                    foreach ($importmap['imports'] as $key => $src) {
                        if (preg_match('/^https?:\/\//', $src) || !empty($assetBaseUrl)) {
                            $assetUrl = $this->resolveAssetReference($src, $assetBaseUrl, $assetBaseUrl);
                            // SKIP FOLDER MAPPINGS: If key or src ends in '/', keep it remote.
                            if (str_ends_with($key, '/') || str_ends_with($src, '/')) {
                                Log::info("Skipping folder-based importmap entry: $key -> $src (keeping remote)");
                                continue;
                            }

                            $localUrl = $this->downloadAndStore($assetUrl, $fullStoragePath, $baseStoragePath, $landing, false, $assetBaseUrl);
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
                if (preg_match('/^https?:\/\//', $src) || !empty($assetBaseUrl)) {
                    $assetUrl = $this->resolveAssetReference($src, $assetBaseUrl, $assetBaseUrl);
                    $localUrl = $this->downloadAndStore($assetUrl, $fullStoragePath, $baseStoragePath, $landing, false, $assetBaseUrl);
                    if ($localUrl) {
                        $attrs = preg_replace('/\bsrc\s*=\s*["\'][^"\']+["\']/i', 'src="' . $localUrl . '"', $attrs);
                    } elseif (!empty($assetUrl)) {
                        $attrs = preg_replace('/\bsrc\s*=\s*["\'][^"\']+["\']/i', 'src="' . $this->buildSameOriginFallbackUrl($assetUrl) . '"', $attrs);
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

        // Extract stylesheet/style tags from mixed HTML payloads and return them
        // separately so editor components remain body-only and render reliably.
        $extractedHeadAssets = [];
        $xpath = new \DOMXPath($dom);
        $headLikeNodes = $xpath->query(
            '//link[translate(@rel,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz")="stylesheet"'
            . ' or translate(@rel,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz")="preload"]'
            . ' | //style'
        );

        foreach (iterator_to_array($headLikeNodes) as $headNode) {
            $extractedHeadAssets[] = $dom->saveHTML($headNode);
            if ($headNode->parentNode) {
                $headNode->parentNode->removeChild($headNode);
            }
        }

        $processedHtml = $dom->saveHTML();
        $processedHtml = preg_replace('/^\s*<\?xml[^>]*\??>/i', '', $processedHtml);

        return [
            'html' => trim($processedHtml),
            'body_scripts' => implode("\n", $extractedScripts),
            'head_assets' => trim(implode("\n", array_filter(array_map('trim', $extractedHeadAssets)))),
        ];
    }

    /**
     * Download an image, store it, and create a MediaAsset record.
     */
    protected function processRemoteCss($css, Landing $landing, $fullPath, $relativePathBase, $assetBaseUrl = null)
    {
        if (empty($css)) return '';

        return preg_replace_callback('/url\(["\']?(?!data:|https?:\/\/|\/\/)([^"\')\s]+)["\']?\)/i', function($match) use ($landing, $fullPath, $relativePathBase, $assetBaseUrl) {
            $assetUrl = $this->resolveAssetReference($match[1], $assetBaseUrl, $assetBaseUrl);
            $localUrl = $this->downloadAndStore($assetUrl, $fullPath, $relativePathBase, $landing, false, $assetBaseUrl);
            if ($localUrl) {
                return "url('{$localUrl}')";
            }
            if (!empty($assetUrl)) {
                return "url('" . $this->buildSameOriginFallbackUrl($assetUrl) . "')";
            }
            return $match[0];
        }, $css);
    }

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
    protected function processHeadAssets($html, Landing $landing, $fullStoragePath, $baseStoragePath, $assetBaseUrl = null)
    {
        if (empty($html)) return '';

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><root>' . $html . '</root>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        // Process Links (CSS)
        foreach (iterator_to_array($dom->getElementsByTagName('link')) as $link) {
            $href = $link->getAttribute('href');
            if ($href && (preg_match('/^https?:\/\//', $href) || !empty($assetBaseUrl))) {
                 $assetUrl = $this->resolveAssetReference($href, $assetBaseUrl, $assetBaseUrl);
                 $newUrl = $this->downloadAndStore($assetUrl, $fullStoragePath, $baseStoragePath, $landing, true, $assetBaseUrl);
                 if ($newUrl) {
                     $link->setAttribute('href', $newUrl);
                 } elseif (!empty($assetUrl)) {
                     $link->setAttribute('href', $this->buildSameOriginFallbackUrl($assetUrl));
                 }
            }
        }

        // Process Scripts (JS)
        foreach (iterator_to_array($dom->getElementsByTagName('script')) as $script) {
            $src = $script->getAttribute('src');
            if ($src && (preg_match('/^https?:\/\//', $src) || !empty($assetBaseUrl))) {
                 $assetUrl = $this->resolveAssetReference($src, $assetBaseUrl, $assetBaseUrl);
                 $newUrl = $this->downloadAndStore($assetUrl, $fullStoragePath, $baseStoragePath, $landing, false, $assetBaseUrl);
                 if ($newUrl) {
                     $script->setAttribute('src', $newUrl);
                 } elseif (!empty($assetUrl)) {
                     $script->setAttribute('src', $this->buildSameOriginFallbackUrl($assetUrl));
                 }
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
    protected function downloadAndStore($url, $fullStoragePath, $baseStoragePath, Landing $landing, $isCss = false, $assetBaseUrl = null)
    {
        if (empty($url)) return null;

        Log::info("Attempting to download/localize asset: $url");
        
        try {
            // Check if already localized by checking for hashed filename in storage
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

            // Rewrite CSS internal refs
            if ($isCss || $extension === 'css') {
                $content = $this->processRemoteCss($content, $landing, $fullStoragePath, $baseStoragePath, $assetBaseUrl ?? $url);
            }

            // DEEP ASSET CRAWLING for JS/CSS (Detect both relative and absolute assets)
            if ($extension === 'js' || $extension === 'css') {
                // Aggressive pattern: Catch any string literally starting or containing asset paths ending in model/media extensions
                // It now matches: "./assets/...", "/assets/...", and "https://domain.com/assets/..."
                $pattern = '/(["\'])((?:https?:\/\/[^\/\"\'\s]+)?(?:\.\/|(?:\.\.\/)+|\/)?(?:assets\/)?[^\"\']+\.(?:glb|gltf|json|bin|wasm|mp4|webm|obj|fbx|woff|woff2|ttf|otf))\1/i';
                
                $content = preg_replace_callback($pattern, function($match) use ($url, $fullStoragePath, $baseStoragePath, $landing, $assetBaseUrl) {
                    $assetUrl = $this->resolveAssetReference($match[2], $assetBaseUrl, $url);
                    
                    Log::info("Aggressive Crawler discovered: " . $match[2] . " -> Resolved to: " . $assetUrl);
                    
                    // Only download if it's on the SAME host as the template source OR it's a relative path
                    // We don't want to download every random external link from JS, just template assets.
                    $sourceHost = parse_url($url, PHP_URL_HOST);
                    $assetHost = parse_url($assetUrl, PHP_URL_HOST);
                    
                    if ($sourceHost === $assetHost || !$assetHost || ($assetBaseUrl && parse_url($assetBaseUrl, PHP_URL_HOST) === $assetHost)) {
                        $localUrl = $this->downloadAndStore($assetUrl, $fullStoragePath, $baseStoragePath, $landing, false, $assetBaseUrl);
                        if ($localUrl) {
                            return $match[1] . $localUrl . $match[1];
                        }
                        if (!empty($assetUrl)) {
                            return $match[1] . $this->buildSameOriginFallbackUrl($assetUrl) . $match[1];
                        }
                        return $match[0];
                    }
                    
                    return $match[0];
                }, $content);
            }

            $extension = strtolower($extension);
            $contentHash = md5($content);
            $filename = md5($url . '|' . $contentHash) . '.' . $extension;
            $relativePath = "{$baseStoragePath}/{$filename}";
            $absolutePath = $fullStoragePath . '/' . $filename;

            if (File::exists($absolutePath)) return Storage::url($relativePath);

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
                $response = \Illuminate\Support\Facades\Http::timeout(60)->withOptions(['verify' => false])->get($url);
                if ($response->successful()) {
                    $mimeType = $response->header('Content-Type');
                    Log::info("Download successful ($url): " . strlen($response->body()) . " bytes, Mime: $mimeType");
                    return $response->body();
                }

                Log::warning("Download attempt $attempt failed for $url: Status " . $response->status());

                // Case-Sensitivity / Common Path Attempt for 404s
                if ($response->status() === 404) {
                    $variants = [];

                    // Try lowercase version
                    $lowerUrl = strtolower($url);
                    if ($lowerUrl !== $url) {
                        $variants[] = $lowerUrl;
                    }

                    // Try basename with uppercase first letter (rocket.glb -> Rocket.glb)
                    $parsedPath = parse_url($url, PHP_URL_PATH);
                    if (is_string($parsedPath) && $parsedPath !== '') {
                        $basename = basename($parsedPath);
                        $upperFirst = ucfirst($basename);
                        if ($upperFirst !== $basename) {
                            $upperFirstUrl = $this->replaceUrlPath($url, rtrim(dirname($parsedPath), '/\\') . '/' . $upperFirst);
                            if ($upperFirstUrl !== $url) {
                                $variants[] = $upperFirstUrl;
                            }
                        }
                    }

                    foreach (array_values(array_unique($variants)) as $variantUrl) {
                        Log::info("404 for $url, retrying variant: $variantUrl");
                        $variantResponse = \Illuminate\Support\Facades\Http::timeout(10)->withOptions(['verify' => false])->get($variantUrl);
                        if ($variantResponse->successful()) {
                            $mimeType = $variantResponse->header('Content-Type');
                            return $variantResponse->body();
                        }
                    }
                }
            } catch (\Exception $e) { }
            if ($attempt < $maxRetries) usleep(500000);
        }
        return null;
    }

    protected function replaceUrlPath(string $url, string $newPath): string
    {
        $parts = parse_url($url);
        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
            return $url;
        }

        $scheme = $parts['scheme'];
        $host = $parts['host'];
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        $normalizedPath = '/' . ltrim($newPath, '/');
        return "{$scheme}://{$host}{$port}{$normalizedPath}{$query}{$fragment}";
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
            'model/gltf-binary' => 'glb', 'model/gltf+json' => 'gltf', 
            'application/wasm' => 'wasm', 'video/mp4' => 'mp4', 'video/webm' => 'webm',
        ];
        $baseMime = explode(';', $mime)[0];
        return $map[$baseMime] ?? null;
    }

    /**
     * Resolve a relative URL based on a parent absolute URL.
     */
    protected function resolveUrl($base, $rel)
    {
        if (preg_match('/^https?:\/\//i', $rel) || str_starts_with($rel, '//')) return $rel;
        
        $parse = parse_url($base);
        $root = $parse['scheme'] . "://" . $parse['host'] . (isset($parse['port']) ? ":" . $parse['port'] : "");
        $path = $parse['path'] ?? '/';
        $dir = dirname($path);
        
        if (str_starts_with($rel, '/')) return $root . $rel;
        
        // Handle ../ and ./ relative to dir
        $absolute = $dir . '/' . $rel;
        $parts = explode('/', $absolute);
        $stack = [];
        foreach ($parts as $part) {
            if ($part === '' || $part === '.') continue;
            if ($part === '..') {
                if (count($stack) > 0) array_pop($stack);
                continue;
            }
            $stack[] = $part;
        }
        return $root . '/' . implode('/', $stack);
    }

    protected function inferAssetBaseUrlFromStructure(array $structure): ?string
    {
        $candidates = [
            (string) ($structure['html'] ?? ''),
            (string) ($structure['custom_head'] ?? ''),
            (string) ($structure['css'] ?? ''),
        ];

        foreach ($candidates as $candidate) {
            if ($candidate === '') {
                continue;
            }

            if (preg_match_all('#https?://[^\s"\'()<>]+#i', $candidate, $matches)) {
                foreach ($matches[0] as $url) {
                    if (!str_contains($url, '/storage/templates/') || !str_contains($url, '/assets/')) {
                        continue;
                    }

                    $assetsPos = strpos($url, '/assets/');
                    if ($assetsPos === false) {
                        continue;
                    }

                    $base = rtrim(substr($url, 0, $assetsPos), '/') . '/';
                    return $base;
                }
            }
        }

        return null;
    }

    /**
     * Resolve asset references while supporting template packages that use
     * root-like paths such as "/assets/...".
     */
    protected function resolveAssetReference(string $reference, ?string $assetBaseUrl = null, ?string $contextUrl = null): string
    {
        if (preg_match('/^https?:\/\//i', $reference) || str_starts_with($reference, '//')) {
            return $reference;
        }

        $assetPath = $assetBaseUrl ? (parse_url($assetBaseUrl, PHP_URL_PATH) ?? '') : '';
        $isTemplateBase = !empty($assetPath) && str_contains($assetPath, '/storage/templates/');

        if ($isTemplateBase && str_starts_with($reference, '/')) {
            return rtrim($assetBaseUrl, '/') . '/' . ltrim($reference, '/');
        }

        // Fallback: infer template base from the current asset URL when the
        // API did not provide asset_base_url (e.g., stale cached template data).
        if ($contextUrl && str_starts_with($reference, '/')
            && str_contains($contextUrl, '/storage/templates/')
            && str_contains($contextUrl, '/assets/')) {
            $assetsPos = strpos($contextUrl, '/assets/');
            if ($assetsPos !== false) {
                $inferredBase = rtrim(substr($contextUrl, 0, $assetsPos), '/') . '/';
                return rtrim($inferredBase, '/') . '/' . ltrim($reference, '/');
            }
        }

        $base = $contextUrl ?: ($assetBaseUrl ?? '');
        return $this->resolveUrl($base, $reference);
    }

    protected function buildSameOriginFallbackUrl(string $assetUrl): string
    {
        if (!preg_match('/^https?:\/\//i', $assetUrl)) {
            return $assetUrl;
        }

        $assetHost = strtolower((string) (parse_url($assetUrl, PHP_URL_HOST) ?? ''));
        $appHost = strtolower((string) (parse_url((string) config('app.url'), PHP_URL_HOST) ?? ''));

        if ($assetHost !== '' && $appHost !== '' && $assetHost === $appHost) {
            return $assetUrl;
        }

        return url('/template-asset-proxy') . '?u=' . rawurlencode($assetUrl);
    }
}
