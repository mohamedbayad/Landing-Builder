<?php

namespace App\Http\Controllers;

use App\Models\Landing;
use App\Models\LandingPage;
use App\Models\WorkspacePlugin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LandingPageController extends Controller
{
    public function edit(Landing $landing, LandingPage $page)
    {
        if ($landing->workspace->user_id != Auth::id()) {
            abort(403);
        }

        if ($page->landing_id != $landing->id) {
            abort(404);
        }

        // --- Logic to Render Dynamic Content for Editor ---
        // If it's a Checkout/Thank You page, we want the editor to see the REAL fields,
        // not just a placeholder. Use the same logic as PublicLandingController.
        
        $initialHtml = $page->html;
        
        // If HTML is empty or just placeholder, try to render dynamic content
        if ($page->type === 'checkout' && (empty($initialHtml) || str_contains($initialHtml, 'Dynamic Checkout Form'))) {
             $product = $landing->products()->first(); // Default to first
             $checkoutFields = $landing->checkoutFields()->where('is_enabled', true)->get();
             
             // Render the view to string
             $initialHtml = view('landings.public.checkout', [
                 'landing' => $landing, 
                 'page' => $page, 
                 'product' => $product,
                 'checkoutFields' => $checkoutFields
             ])->render();
             
             // Temporarily assign for this request (don't save to DB yet)
             $page->html = $initialHtml;
        }

        $extracted = $this->extractEditorAssets((string) $page->html);

        // Keep editor canvas body clean (no head tags mixed into components)
        // and normalize reveal/hidden classes for edit mode visibility.
        $page->html = $this->normalizeEditorClasses($extracted['html']);

        // Ensure scripts embedded in HTML are still executed in editor canvas
        if (!empty($extracted['body_scripts'])) {
            $page->js = trim((string) $page->js . "\n" . $extracted['body_scripts']);
        }

        // Build editor custom head from:
        // 1) local tailwind runtime for utility classes
        // 2) landing-level custom head scripts
        // 3) head assets extracted from page HTML (legacy/imported templates)
        $tailwindHead = '<script src="/js/tailwind.js"></script>'
            . '<script>window.tailwind = window.tailwind || {}; window.tailwind.config = {darkMode: "class", theme: {extend: {colors: {primary: "#5D4037", secondary: "#BC8440"}}}};</script>'
            . '<style>
                .gjs-selected .details-content,
                .product-card.gjs-selected .details-content,
                .details-content.gjs-selected {
                    max-height: none !important;
                    opacity: 1 !important;
                    visibility: visible !important;
                    display: block !important;
                }
                details.gjs-selected > *:not(summary),
                details:has(.gjs-selected) > *:not(summary) {
                    display: block !important;
                    height: auto !important;
                    opacity: 1 !important;
                    visibility: visible !important;
                }
                /* Editor-only: force scroll-reveal/hidden utility blocks visible */
                .reveal,
                [class*="reveal"],
                [class*="fade"],
                [class*="animate-"],
                [data-reveal],
                [data-animate] {
                    opacity: 1 !important;
                    visibility: visible !important;
                    transform: none !important;
                    filter: none !important;
                }
                .hidden,
                .invisible,
                .opacity-0 {
                    opacity: 1 !important;
                    visibility: visible !important;
                }
                body {
                    background: #000 !important;
                    color: #f8fafc !important;
                }
            </style>';

        $landingHead = (string) (optional($landing->settings)->custom_head_scripts ?? '');
        if (!empty($extracted['head_assets']) && !str_contains($landingHead, trim($extracted['head_assets']))) {
            $landingHead = trim(implode("\n", array_filter([
                $landingHead,
                $extracted['head_assets'],
            ])));

            $settings = $landing->settings()->updateOrCreate([], [
                'custom_head_scripts' => $landingHead,
            ]);
            $landing->setRelation('settings', $settings);
        }

        // Replace proxy/remote editor asset URLs with local /storage paths when available.
        $landingHead = $this->normalizeEditorAssetUrls($landingHead);

        // Persist normalized head so future editor loads don't depend on template-asset-proxy.
        if ($landingHead !== (string) (optional($landing->settings)->custom_head_scripts ?? '')) {
            $settings = $landing->settings()->updateOrCreate([], [
                'custom_head_scripts' => $landingHead,
            ]);
            $landing->setRelation('settings', $settings);
        }

        // Normalize page JS script src URLs too (proxy -> local /storage).
        $page->js = $this->normalizeEditorAssetUrls((string) $page->js);

        
                $editorCustomHead = trim(implode("\n", array_filter([
            $tailwindHead,
            $landingHead,
        ])));

        // Some imported templates keep CSS in <link> tags only.
        // Inline local /storage/*.css into editor CSS so GrapesJS renders reliably.
        $inlinedCss = $this->inlineLocalStylesFromHead($editorCustomHead);
        if (!empty($inlinedCss)) {
            $page->css = trim((string) $page->css . "\n\n" . $inlinedCss);
        }

        // Complex imported templates (external JS/importmaps) are safer in HTML mode.
        $forceHtmlMode = !empty((string) $page->js) || str_starts_with((string) $landing->source, 'remote-template:');

        // Editor-safe mode: avoid running heavy module scripts (for example large WebGL scenes)
        // that can pin/hide sections inside GrapesJS iframe.
        $disableModuleScripts = str_starts_with((string) $landing->source, 'remote-template:');

        $activeEditorPlugins = WorkspacePlugin::query()
            ->with('plugin:id,slug,hooks,is_active')
            ->where('workspace_id', $landing->workspace_id)
            ->where('status', 'active')
            ->get()
            ->filter(fn (WorkspacePlugin $workspacePlugin) => $workspacePlugin->plugin && $workspacePlugin->plugin->is_active)
            ->map(function (WorkspacePlugin $workspacePlugin) {
                return [
                    'slug' => $workspacePlugin->plugin->slug,
                    'hooks' => is_array($workspacePlugin->plugin->hooks) ? $workspacePlugin->plugin->hooks : [],
                    'settings' => is_array($workspacePlugin->settings) ? $workspacePlugin->settings : [],
                ];
            })
            ->values();

        return view('editor', compact('landing', 'page', 'editorCustomHead', 'forceHtmlMode', 'disableModuleScripts', 'activeEditorPlugins'));
    }

    public function update(Request $request, Landing $landing, LandingPage $page)
    {
        if ($landing->workspace->user_id != Auth::id()) {
            abort(403);
        }
        if ($page->landing_id != $landing->id) {
            abort(404);
        }

        // We expect JSON payload
        $validated = $request->validate([
            'grapesjs_json' => 'nullable|string',
            'html' => 'nullable|string',
            'css' => 'nullable|string',
            'js' => 'nullable|string',
        ]);

        // Keep saved JS stable:
        // - preserve missing external script src tags from previous save
        // - avoid invalid nested <script> wrappers
        // - dedupe duplicate src scripts
        if (array_key_exists('js', $validated)) {
            $validated['js'] = $this->normalizeSavedPageJs(
                (string) ($validated['js'] ?? ''),
                (string) ($page->js ?? '')
            );
        }

        // 2. Clean up GrapesJS editor style contamination (added by canvas-interaction-control.js)
        if (isset($validated['html'])) {
            $validated['html'] = preg_replace('/opacity:\s*1\s*!important;\s*/i', '', $validated['html']);
            $validated['html'] = preg_replace('/visibility:\s*visible\s*!important;\s*/i', '', $validated['html']);
            $validated['html'] = preg_replace('/transform:\s*none\s*!important;\s*/i', '', $validated['html']);
            $validated['html'] = preg_replace('/filter:\s*none\s*!important;\s*/i', '', $validated['html']);

            // Clean up empty style attributes left behind
            $validated['html'] = str_replace(' style=""', '', $validated['html']);
        }

        $page->update($validated);


        return response()->json(['success' => true]);
    }

    public function duplicate(Landing $landing, LandingPage $page)
    {
        if ($landing->workspace->user_id != Auth::id()) {
            abort(403);
        }
        if ($page->landing_id != $landing->id) {
            abort(404);
        }

        $baseName = trim($page->name . ' Copy');
        $newName = $baseName;
        $nameSuffix = 2;
        while ($landing->pages()->where('name', $newName)->exists()) {
            $newName = $baseName . ' ' . $nameSuffix;
            $nameSuffix++;
        }

        $slugSeed = Str::slug($page->slug ?: $page->name ?: 'page');
        if ($slugSeed === '') {
            $slugSeed = 'page';
        }

        $newSlug = $slugSeed . '-copy';
        $slugSuffix = 2;
        while ($landing->pages()->where('slug', $newSlug)->exists()) {
            $newSlug = $slugSeed . '-copy-' . $slugSuffix;
            $slugSuffix++;
        }

        $duplicate = $page->replicate();
        $duplicate->name = $newName;
        $duplicate->slug = $newSlug;
        $duplicate->status = 'draft';
        $duplicate->save();

        return redirect()
            ->route('landings.pages.edit', [$landing, $duplicate])
            ->with('status', 'Page duplicated successfully.');
    }

    /**
     * Normalize JS payload saved from editor.
     *
     * Prevents invalid nested <script> blocks and keeps external src scripts
     * from previous saves when editor payload omits them.
     */
    protected function normalizeSavedPageJs(string $incomingJs, string $existingJs): string
    {
        $incoming = trim($incomingJs);
        $existing = trim($existingJs);

        // If nothing was sent, keep previous scripts but dedup them.
        if ($incoming === '') {
            return $this->dedupeScriptSrcTags($existing);
        }

        $result = $incoming;

        // If payload is plain JS code (no script tags), wrap once.
        if (!preg_match('/<script\b/i', $result)) {
            $result = "<script>\n{$result}\n</script>";
        }

        // Repair bad saves like: <script><script src=...></script>...</script>
        $result = $this->unwrapNestedScriptBlocks($result);

        // Preserve old external script src tags that might be missing.
        $existingSrcMap = $this->extractScriptSrcTagMap($existing);
        $missing = [];
        foreach ($existingSrcMap as $src => $tag) {
            $srcPattern = preg_quote($src, '/');
            if (!preg_match('/<script\b[^>]*src=["\']' . $srcPattern . '["\'][^>]*><\/script>/i', $result)) {
                $missing[] = $tag;
            }
        }

        if (!empty($missing)) {
            $result = implode("\n", $missing) . "\n" . $result;
        }

        return trim($this->dedupeScriptSrcTags($result));
    }

    /**
     * Unwrap inline script blocks that incorrectly contain other <script> tags.
     */
    protected function unwrapNestedScriptBlocks(string $js): string
    {
        if ($js === '') {
            return '';
        }

        return preg_replace_callback('/<script\b([^>]*)>([\s\S]*?)<\/script>/i', function ($m) {
            $attrs = strtolower($m[1] ?? '');
            $inner = trim($m[2] ?? '');

            // Keep src/importmap scripts as-is.
            if (str_contains($attrs, 'src=')
                || str_contains($attrs, 'type="importmap"')
                || str_contains($attrs, "type='importmap'")) {
                return $m[0];
            }

            // If inline script contains script tags, it was wrapped incorrectly.
            if ($inner !== '' && preg_match('/<script\b/i', $inner)) {
                return $inner;
            }

            return $m[0];
        }, $js) ?? $js;
    }

    /**
     * Extract unique script src tags map: [src => fullTag], preserving first order.
     */
    protected function extractScriptSrcTagMap(string $js): array
    {
        if ($js === '') {
            return [];
        }

        $map = [];
        if (preg_match_all('/<script\b[^>]*src=["\']([^"\']+)["\'][^>]*><\/script>/i', $js, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $src = trim($m[1] ?? '');
                if ($src === '' || isset($map[$src])) {
                    continue;
                }
                $map[$src] = $m[0];
            }
        }

        return $map;
    }

    /**
     * Remove duplicate <script src="..."></script> tags while preserving first one.
     */
    protected function dedupeScriptSrcTags(string $js): string
    {
        if ($js === '') {
            return '';
        }

        $seen = [];
        return preg_replace_callback('/<script\b[^>]*src=["\']([^"\']+)["\'][^>]*><\/script>\s*/i', function ($m) use (&$seen) {
            $src = trim($m[1] ?? '');
            if ($src === '') {
                return $m[0];
            }
            if (isset($seen[$src])) {
                return '';
            }
            $seen[$src] = true;
            return rtrim($m[0]) . "\n";
        }, $js) ?? $js;
    }

    /**
     * Normalize page HTML for GrapesJS editor:
     * - remove XML/DOCTYPE wrappers
     * - extract head-only assets from mixed HTML payloads
     * - extract script tags to be injected via #gjs-js
     */
    protected function extractEditorAssets(string $html): array
    {
        if ($html === '') {
            return [
                'html' => '',
                'head_assets' => '',
                'body_scripts' => '',
            ];
        }

        $normalized = preg_replace('/^\s*<\?xml[^>]*\??>/i', '', $html);
        $normalized = preg_replace('/<!DOCTYPE[^>]*>/i', '', $normalized);

        $headAssets = [];
        $bodyScripts = [];

        if (preg_match_all('/<link\b[^>]*rel\s*=\s*["\']?stylesheet["\']?[^>]*>/i', $normalized, $matches)) {
            $headAssets = array_merge($headAssets, $matches[0]);
            $normalized = preg_replace('/<link\b[^>]*rel\s*=\s*["\']?stylesheet["\']?[^>]*>/i', '', $normalized);
        }

        if (preg_match_all('/<style\b[^>]*>[\s\S]*?<\/style>/i', $normalized, $matches)) {
            $headAssets = array_merge($headAssets, $matches[0]);
            $normalized = preg_replace('/<style\b[^>]*>[\s\S]*?<\/style>/i', '', $normalized);
        }

        if (preg_match_all('/<script\b[^>]*>[\s\S]*?<\/script>/i', $normalized, $matches)) {
            $bodyScripts = array_merge($bodyScripts, $matches[0]);
            $normalized = preg_replace('/<script\b[^>]*>[\s\S]*?<\/script>/i', '', $normalized);
        }

        $normalized = trim($normalized);

        return [
            'html' => $normalized,
            'head_assets' => trim(implode("\n", array_unique(array_map('trim', array_filter($headAssets))))),
            'body_scripts' => trim(implode("\n", array_unique(array_map('trim', array_filter($bodyScripts))))),
        ];
    }

    /**
     * Editor-only class normalization:
     * - ensure .reveal blocks are active (template JS may not run in builder iframe)
     * - remove utility classes that hide elements entirely
     */
    protected function normalizeEditorClasses(string $html): string
    {
        if ($html === '') {
            return '';
        }

        return preg_replace_callback('/\bclass\s*=\s*(["\'])(.*?)\1/i', function ($m) {
            $quote = $m[1];
            $raw = trim($m[2]);
            if ($raw === '') {
                return $m[0];
            }

            $parts = preg_split('/\s+/', $raw) ?: [];
            $parts = array_values(array_filter($parts, fn ($c) => $c !== ''));

            $hasReveal = in_array('reveal', $parts, true);
            if ($hasReveal && !in_array('active', $parts, true)) {
                $parts[] = 'active';
            }

            $parts = array_values(array_filter($parts, function ($c) {
                return !in_array($c, ['hidden', 'invisible', 'opacity-0'], true);
            }));

            return 'class=' . $quote . implode(' ', array_unique($parts)) . $quote;
        }, $html) ?? $html;
    }

    /**
     * Read local /storage/*.css links from head HTML and inline their content.
     * External CDNs are ignored.
     */
    protected function inlineLocalStylesFromHead(string $headHtml): string
    {
        if ($headHtml === '') {
            return '';
        }

        if (!preg_match_all('/<link\b[^>]*>/i', $headHtml, $links)) {
            return '';
        }

        $chunks = [];
        foreach ($links[0] as $tag) {
            if (!preg_match('/\brel\s*=\s*["\']?stylesheet["\']?/i', $tag)) {
                continue;
            }

            if (!preg_match('/\bhref\s*=\s*["\']([^"\']+)["\']/i', $tag, $hrefMatch)) {
                continue;
            }

            $href = $hrefMatch[1];
            $path = $this->resolveLocalStoragePathFromAssetUrl($href) ?? '';
            if ($path === '' || !str_starts_with($path, '/storage/') || !str_ends_with(strtolower($path), '.css')) {
                continue;
            }

            $relative = ltrim(substr($path, strlen('/storage/')), '/');
            $absolute = storage_path('app/public/' . $relative);
            if (!File::exists($absolute)) {
                continue;
            }

            $css = File::get($absolute);
            if (!empty($css)) {
                $chunks[] = "/* inlined: {$path} */\n" . $css;
            }
        }

        return trim(implode("\n\n", array_unique($chunks)));
    }

    /**
     * Normalize head/body script/link URLs for editor mode:
     * - /template-asset-proxy?u=.../storage/... -> /storage/...
     * - absolute URLs with /storage/... path -> /storage/...
     */
    protected function normalizeEditorAssetUrls(string $html): string
    {
        if ($html === '') {
            return '';
        }

        return preg_replace_callback('/\b(href|src)\s*=\s*(["\'])([^"\']+)\2/i', function ($m) {
            $attr = $m[1];
            $quote = $m[2];
            $originalUrl = $m[3];

            $storagePath = $this->resolveLocalStoragePathFromAssetUrl($originalUrl);
            if ($storagePath === null) {
                return $m[0];
            }

            $relative = ltrim(substr($storagePath, strlen('/storage/')), '/');
            $absolute = storage_path('app/public/' . $relative);
            if (!File::exists($absolute)) {
                return $m[0];
            }

            return $attr . '=' . $quote . $storagePath . $quote;
        }, $html) ?? $html;
    }

    /**
     * Resolve local /storage path from direct, absolute, or proxy URL.
     */
    protected function resolveLocalStoragePathFromAssetUrl(string $url): ?string
    {
        $decoded = html_entity_decode(trim($url), ENT_QUOTES | ENT_HTML5);
        if ($decoded === '') {
            return null;
        }

        if (str_starts_with($decoded, '/storage/')) {
            return parse_url($decoded, PHP_URL_PATH) ?: null;
        }

        $parsed = parse_url($decoded);
        $path = $parsed['path'] ?? '';

        // Handle proxy URLs like /template-asset-proxy?u=http://.../storage/landings/...css
        if ($path === '/template-asset-proxy') {
            parse_str($parsed['query'] ?? '', $q);
            if (!empty($q['u']) && is_string($q['u'])) {
                return $this->resolveLocalStoragePathFromAssetUrl($q['u']);
            }
        }

        if (str_starts_with($path, '/storage/')) {
            return $path;
        }

        $pos = stripos($path, '/storage/');
        if ($pos !== false) {
            return substr($path, $pos);
        }

        return null;
    }

}
