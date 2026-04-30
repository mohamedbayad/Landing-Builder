<?php

namespace App\Http\Controllers;

use App\Models\Landing;
use App\Models\LandingPage;
use App\Support\LandingPublicUrl;

class PublicLandingController extends Controller
{
    /**
     * Serve the main landing page at root URL /
     */
    public function home()
    {
        $mainLanding = $this->resolveMainLandingForRequest();

        if (!$mainLanding) {
            return view('welcome'); // Default Laravel welcome
        }

        $page = $mainLanding->pages()->where('type', 'index')->first()
            ?? $mainLanding->pages()->first();

        if (!$page) {
            abort(404);
        }

        return $this->respondWithLandingPage($mainLanding, $page);
    }

    /**
     * Legacy single-segment public route.
     * 1) Custom-domain page slug under active landing
     * 2) Page slug under platform main landing
     * 3) Legacy landing slug for non-platform landings -> redirect to /w/{workspace}/{landing}
     */
    public function page($slug)
    {
        $activeLandingPage = app()->has('active_landing_page')
            ? app('active_landing_page')
            : null;

        if ($activeLandingPage instanceof Landing) {
            $page = $activeLandingPage->pages()->where('slug', $slug)->first();
            if ($page) {
                return $this->respondWithLandingPage($activeLandingPage, $page);
            }
        }

        $mainLanding = $this->resolveMainLandingForRequest();
        if ($mainLanding instanceof Landing) {
            $page = $mainLanding->pages()->where('slug', $slug)->first();
            if ($page) {
                return $this->respondWithLandingPage($mainLanding, $page);
            }
        }

        $landing = Landing::where('slug', $slug)->first();
        if ($landing) {
            $this->assertLandingVisible($landing);

            if (LandingPublicUrl::isPlatformMainLanding($landing)) {
                $indexPage = $landing->pages()->where('type', 'index')->first()
                    ?? $landing->pages()->first();
                if ($indexPage) {
                    return $this->respondWithLandingPage($landing, $indexPage);
                }
            } else {
                return $this->redirectLegacyLandingToWorkspace($landing);
            }
        }

        abort(404);
    }

    public function preview(Landing $landing, LandingPage $page)
    {
        if ($landing->workspace->user_id != request()->user()->id) {
            abort(403);
        }

        if ($page->landing_id != $landing->id) {
            abort(404);
        }

        $data = ['landing' => $landing, 'page' => $page];
        
        if ($page->type === 'checkout') {
            $data = array_merge($data, $this->getCheckoutData($landing));
        }

        // Thank You Page - add layout and lead data
        if ($page->type === 'thankyou') {
            $wsSettings = $landing->workspace->settings ?? null;
            $data['thankyouLayout'] = $wsSettings->thankyou_style ?? 'thankyou_1';
            
            if (request()->has('lead')) {
                $leadId = request()->query('lead');
                $lead = \App\Models\Lead::find($leadId);
                if ($lead && $lead->landing_id == $landing->id) {
                    $data['lead'] = $lead;
                }
            }
        }

        $html = $this->renderLandingPage($landing, $page, $data);
        $html = $this->normalizePreviewAssets($html);
        $pageTypeMapped = in_array($page->type, ['checkout', 'thankyou']) ? $page->type : 'landing';
        return response($this->injectRecordingSnippet($html, $landing, $pageTypeMapped));
    }
    
    private function getCheckoutData(Landing $landing)
    {
        // 1. Fetch Product
        $product = null;
        $productId = request()->query('product');
        
        if ($productId) {
            $product = $landing->products()->find($productId);
        }
        
        // Default to first product if none selected/found
        if (!$product) {
            $product = $landing->products()->first();
        }
        
        // 2. Fetch Checkout Fields
        $checkoutFields = $landing->checkoutFields()->where('is_enabled', true)->get();
        
        // 3. Fetch Layout Style from Workspace Settings
        $workspaceSettings = $landing->workspace->settings ?? null;
        $checkoutLayout = $workspaceSettings->checkout_style ?? 'layout_1';
        $thankyouLayout = $workspaceSettings->thankyou_style ?? 'thankyou_1';
        
        return [
            'product' => $product,
            'checkoutFields' => $checkoutFields,
            'checkoutLayout' => $checkoutLayout,
            'thankyouLayout' => $thankyouLayout,
        ];
    }

    public function checkoutFlow(Landing $landing)
    {
        $this->assertLandingVisible($landing);

        $page = $landing->pages()->where('type', 'checkout')->first();

        if (!$page) {
            abort(404, 'Checkout page not found.');
        }

        return $this->respondWithLandingPage($landing, $page);
    }

    public function workspaceHome(string $workspaceEndpoint)
    {
        $landing = $this->resolveWorkspaceMainLanding($workspaceEndpoint);
        if (!$landing) {
            abort(404);
        }

        $page = $landing->pages()->where('type', 'index')->first()
            ?? $landing->pages()->first();
        if (!$page) {
            abort(404);
        }

        return $this->respondWithLandingPage($landing, $page);
    }

    public function workspaceLanding(string $workspaceEndpoint, string $landingSlug)
    {
        $landing = $this->resolveWorkspaceLanding($workspaceEndpoint, $landingSlug);
        if (!$landing) {
            abort(404);
        }

        $this->assertLandingVisible($landing);

        $page = $landing->pages()->where('type', 'index')->first()
            ?? $landing->pages()->first();
        if (!$page) {
            abort(404);
        }

        return $this->respondWithLandingPage($landing, $page);
    }

    public function workspaceLandingPage(string $workspaceEndpoint, string $landingSlug, string $pageSlug)
    {
        $landing = $this->resolveWorkspaceLanding($workspaceEndpoint, $landingSlug);
        if (!$landing) {
            abort(404);
        }

        $this->assertLandingVisible($landing);

        $page = $landing->pages()->where('slug', $pageSlug)->first();
        if (!$page) {
            abort(404);
        }

        return $this->respondWithLandingPage($landing, $page);
    }

    /**
     * Legacy sub-page route: /{landingSlug}/{pageSlug}
     */
    public function landingSubPage($landingSlug, $pageSlug)
    {
        $landing = Landing::where('slug', $landingSlug)->first();
        if (!$landing) {
            abort(404);
        }

        $this->assertLandingVisible($landing);

        $page = $landing->pages()->where('slug', $pageSlug)->first();
        if (!$page) {
            abort(404);
        }

        if (LandingPublicUrl::isPlatformMainLanding($landing)) {
            return $this->respondWithLandingPage($landing, $page);
        }

        return $this->redirectLegacyLandingPageToWorkspace($landing, $page);
    }

    /**
     * Resolve which landing should act as "main" for the current request.
     *
     * Priority:
     * 1) Active custom-domain landing (if bound by middleware)
     * 2) Global published main landing owned by admin/super-admin
     */
    private function resolveMainLandingForRequest(): ?Landing
    {
        $activeLandingPage = app()->has('active_landing_page')
            ? app('active_landing_page')
            : null;

        if ($activeLandingPage instanceof Landing) {
            if ($activeLandingPage->status === 'published') {
                return $activeLandingPage;
            }

            return null;
        }

        return Landing::query()
            ->where('is_main', true)
            ->where('status', 'published')
            ->with(['workspace.user.roles'])
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->first(function (Landing $landing) {
                return LandingPublicUrl::isPlatformMainLanding($landing);
            });
    }

    private function resolveWorkspaceMainLanding(string $workspaceEndpoint): ?Landing
    {
        $workspaceEndpoint = strtolower(trim($workspaceEndpoint));
        $baseQuery = Landing::query()
            ->where('status', 'published')
            ->whereHas('workspace.settings', function ($query) use ($workspaceEndpoint) {
                $query->where('workspace_public_endpoint', $workspaceEndpoint);
            })
            ->with('workspace.settings');

        $mainLanding = (clone $baseQuery)
            ->where('is_main', true)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        if ($mainLanding) {
            return $mainLanding;
        }

        return (clone $baseQuery)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();
    }

    private function resolveWorkspaceLanding(string $workspaceEndpoint, string $landingSlug): ?Landing
    {
        $workspaceEndpoint = strtolower(trim($workspaceEndpoint));

        return Landing::query()
            ->where('slug', $landingSlug)
            ->whereHas('workspace.settings', function ($query) use ($workspaceEndpoint) {
                $query->where('workspace_public_endpoint', $workspaceEndpoint);
            })
            ->with('workspace.settings')
            ->first();
    }

    private function assertLandingVisible(Landing $landing): void
    {
        if ($landing->status === 'published') {
            return;
        }

        if (!request()->user() || request()->user()->id != $landing->workspace->user_id) {
            abort(404);
        }
    }

    private function buildLandingViewData(Landing $landing, LandingPage $page): array
    {
        $data = ['landing' => $landing, 'page' => $page];

        if ($page->type === 'checkout') {
            $data = array_merge($data, $this->getCheckoutData($landing));
        }

        if ($page->type === 'thankyou') {
            if (!request()->hasValidSignature()) {
                abort(403, 'Unauthorized access to Thank You page.');
            }

            $wsSettings = $landing->workspace->settings ?? null;
            $data['thankyouLayout'] = $wsSettings->thankyou_style ?? 'thankyou_1';

            if (request()->has('lead')) {
                $leadId = request()->query('lead');
                $lead = \App\Models\Lead::find($leadId);
                if ($lead && $lead->landing_id == $landing->id) {
                    $data['lead'] = $lead;
                }
            }
        }

        return $data;
    }

    private function respondWithLandingPage(Landing $landing, LandingPage $page)
    {
        $data = $this->buildLandingViewData($landing, $page);
        $html = $this->renderLandingPage($landing, $page, $data);
        $html = $this->normalizePreviewAssets($html);
        $pageTypeMapped = in_array($page->type, ['checkout', 'thankyou'], true) ? $page->type : 'landing';

        return response($this->injectRecordingSnippet($html, $landing, $pageTypeMapped));
    }

    private function redirectLegacyLandingToWorkspace(Landing $landing)
    {
        $target = LandingPublicUrl::indexUrl($landing);
        $query = request()->getQueryString();
        if ($query) {
            $target .= (str_contains($target, '?') ? '&' : '?') . $query;
        }

        return redirect()->to($target, 301);
    }

    private function redirectLegacyLandingPageToWorkspace(Landing $landing, LandingPage $page)
    {
        $queryParams = request()->query();
        unset($queryParams['signature'], $queryParams['expires']);

        if ($page->type === 'thankyou') {
            if (!request()->hasValidSignature()) {
                abort(403, 'Unauthorized access to Thank You page.');
            }

            $target = LandingPublicUrl::signedPageUrl($landing, $page, $queryParams);
            return redirect()->to($target, 301);
        }

        $target = LandingPublicUrl::pageUrl($landing, $page);
        $query = request()->getQueryString();
        if ($query) {
            $target .= (str_contains($target, '?') ? '&' : '?') . $query;
        }

        return redirect()->to($target, 301);
    }

    private function renderLandingPage(Landing $landing, LandingPage $page, array $data = []): string
    {
        $countryContext = $this->resolveVisitorCountryContext();

        $viewData = array_merge($data, [
            'landing' => $landing,
            'page' => $page,
            'visitorCountryCode' => $countryContext['code'],
            'visitorCountryName' => $countryContext['name'],
        ]);

        return view('landing_page', $viewData)->render();
    }

    /**
     * @return array{code:string,name:string}
     */
    private function resolveVisitorCountryContext(): array
    {
        $ip = (string) request()->ip();
        $countryCode = 'XX';
        $countryName = 'Unknown';

        $isLocalIp = in_array($ip, ['127.0.0.1', '::1'], true)
            || str_starts_with($ip, '192.168.')
            || str_starts_with($ip, '10.')
            || str_starts_with($ip, '172.');

        if ($isLocalIp) {
            return ['code' => 'MA', 'name' => 'Morocco'];
        }

        try {
            if (class_exists(\Stevebauman\Location\Facades\Location::class)) {
                $position = \Stevebauman\Location\Facades\Location::get($ip);
                if ($position) {
                    $resolvedName = trim((string) ($position->countryName ?? ''));
                    $resolvedCode = strtoupper(trim((string) ($position->countryCode ?? '')));

                    if ($resolvedName !== '') {
                        $countryName = $resolvedName;
                    }

                    if ($resolvedCode !== '') {
                        $countryCode = $resolvedCode;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silent fallback to unknown.
        }

        if ($countryCode === 'XX' && $countryName !== 'Unknown') {
            $countryCode = $this->mapCountryNameToCode($countryName);
        }

        if ($countryName === 'Unknown' && $countryCode !== 'XX') {
            $countryName = $this->mapCountryCodeToName($countryCode);
        }

        return ['code' => $countryCode, 'name' => $countryName];
    }

    private function mapCountryNameToCode(string $countryName): string
    {
        $lookup = [
            'morocco' => 'MA',
            'france' => 'FR',
            'spain' => 'ES',
            'united states' => 'US',
            'united kingdom' => 'GB',
            'germany' => 'DE',
            'italy' => 'IT',
            'united arab emirates' => 'AE',
            'saudi arabia' => 'SA',
            'egypt' => 'EG',
        ];

        $key = strtolower(trim($countryName));
        return $lookup[$key] ?? 'XX';
    }

    private function mapCountryCodeToName(string $countryCode): string
    {
        $lookup = [
            'MA' => 'Morocco',
            'FR' => 'France',
            'ES' => 'Spain',
            'US' => 'United States',
            'GB' => 'United Kingdom',
            'DE' => 'Germany',
            'IT' => 'Italy',
            'AE' => 'United Arab Emirates',
            'SA' => 'Saudi Arabia',
            'EG' => 'Egypt',
        ];

        return $lookup[strtoupper(trim($countryCode))] ?? 'Unknown';
    }

    private function injectRecordingSnippet(string $html, Landing $landingPage, string $pageType): string
    {
        $snippetPath = resource_path('js/recording-snippet.js');
        if (!file_exists($snippetPath)) {
            return $html;
        }
        
        $snippet = file_get_contents($snippetPath);
        
        $snippet = str_replace([
            '{{PAGE_TYPE}}',
            '{{LANDING_PAGE_ID}}',
            '{{API_BASE_URL}}',
        ], [
            $pageType,
            $landingPage->id,
            '/api/rec',
        ], $snippet);
        
        $lzScript = '<script src="https://cdn.jsdelivr.net/npm/lz-string@1.5.0/libs/lz-string.min.js"></script>';
        $injectCode = $lzScript . "\n<script>\n" . $snippet . "\n</script>";
        
        // Inject before </body>
        return str_replace('</body>', $injectCode . '</body>', $html);
    }

    /**
     * Resolves local storage path from asset URL (proxy or direct)
     */
    protected function resolveLocalStoragePathFromAssetUrl(string $url): ?string
    {
        $decoded = html_entity_decode(trim($url), ENT_QUOTES | ENT_HTML5);
        if ($decoded === '') {
            return null;
        }

        if (str_starts_with($decoded, '/storage/')) {
            $directPath = parse_url($decoded, PHP_URL_PATH) ?: null;
            return is_string($directPath) ? $this->resolveExistingStoragePath($directPath) : null;
        }

        $parsed = parse_url($decoded);
        $path = $parsed['path'] ?? '';

        if ($path === '/template-asset-proxy') {
            parse_str($parsed['query'] ?? '', $q);
            if (!empty($q['u']) && is_string($q['u'])) {
                return $this->resolveLocalStoragePathFromAssetUrl($q['u']);
            }
        }

        if (str_starts_with($path, '/storage/')) {
            return $this->resolveExistingStoragePath($path);
        }

        $pos = stripos($path, '/storage/');
        if ($pos !== false) {
            return $this->resolveExistingStoragePath(substr($path, $pos));
        }

        return null;
    }

    /**
     * Resolve a /storage asset path and repair common missing-segment paths.
     */
    protected function resolveExistingStoragePath(string $storagePath): ?string
    {
        if (!str_starts_with($storagePath, '/storage/')) {
            return null;
        }

        $normalizedPath = parse_url($storagePath, PHP_URL_PATH) ?: $storagePath;
        $relative = ltrim(substr($normalizedPath, strlen('/storage/')), '/');
        $absolute = storage_path('app/public/' . $relative);

        if (\Illuminate\Support\Facades\File::exists($absolute)) {
            return '/storage/' . $relative;
        }

        // Repair malformed template paths:
        // /storage/builder-templates/{token}/assets/...  ->
        // /storage/builder-templates/{token}/{subfolder}/assets/...
        if (!preg_match('#^builder-templates/([^/]+)/(.+)$#', $relative, $matches)) {
            return null;
        }

        $templateToken = $matches[1];
        $tail = ltrim($matches[2], '/');
        $templateBase = storage_path('app/public/builder-templates/' . $templateToken);

        if (!\Illuminate\Support\Facades\File::isDirectory($templateBase)) {
            return null;
        }

        $subfolders = \Illuminate\Support\Facades\File::directories($templateBase);
        foreach ($subfolders as $folder) {
            $candidateAbsolute = rtrim($folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $tail);
            if (\Illuminate\Support\Facades\File::exists($candidateAbsolute)) {
                $folderName = basename($folder);
                return '/storage/builder-templates/' . $templateToken . '/' . $folderName . '/' . $tail;
            }
        }

        return null;
    }

    /**
     * Aggressive asset inlining and cleaning for preview/live pages.
     * Prevents 404 proxy files or missing local files from causing CSS/JS syntax errors.
     */
    protected function normalizePreviewAssets(string $html): string
    {
        $html = $this->injectMaterialIconsAssets($html);

        // 1. Process <link rel="stylesheet">
        if (preg_match_all('/<link\b[^>]*>/i', $html, $links)) {
            foreach ($links[0] as $tag) {
                if (!preg_match('/\brel\s*=\s*["\']?stylesheet["\']?/i', $tag) || preg_match('/\bhref\s*=\s*["\']?(https?:\/\/(fonts|cdn|unpkg|cdnjs|kit\.fontawesome)[^"\']*)["\']?/i', $tag)) {
                    continue;
                }

                if (!preg_match('/\bhref\s*=\s*["\']([^"\']+)["\']/i', $tag, $hrefMatch)) {
                    continue;
                }

                $href = $hrefMatch[1];
                $path = $this->resolveLocalStoragePathFromAssetUrl($href) ?? '';
                
                if ($path === '') {
                    continue;
                }

                $relative = ltrim(substr($path, strlen('/storage/')), '/');
                $absolute = storage_path('app/public/' . $relative);
                
                if (\Illuminate\Support\Facades\File::exists($absolute)) {
                    $css = \Illuminate\Support\Facades\File::get($absolute);
                    if (!empty($css)) {
                        $inlineTag = "<style>\n/* Inlined: {$path} */\n" . $css . "\n</style>";
                        $html = str_replace($tag, $inlineTag, $html);
                    } else {
                        $html = str_replace($tag, '', $html); // empty
                    }
                } else {
                    // Dead link (e.g. proxy failing). Strip it to prevent HTML syntax crash.
                    $html = str_replace($tag, '', $html);
                }
            }
        }

        // 2. Process <script src="...">
        if (preg_match_all('/<script\b[^>]*src=["\']([^"\']+)["\'][^>]*><\/script>/i', $html, $scripts, PREG_SET_ORDER)) {
            foreach ($scripts as $scriptMatch) {
                $tag = $scriptMatch[0];
                $src = $scriptMatch[1];
                
                if (preg_match('/^(https?:)?\/\/(cdn|unpkg|cdnjs|www\.google)/i', $src)) {
                    continue; // Skip external CDNs
                }

                $path = $this->resolveLocalStoragePathFromAssetUrl($src) ?? '';
                if ($path === '') {
                    continue;
                }

                $relative = ltrim(substr($path, strlen('/storage/')), '/');
                $absolute = storage_path('app/public/' . $relative);
                
                if (\Illuminate\Support\Facades\File::exists($absolute)) {
                    $js = \Illuminate\Support\Facades\File::get($absolute);
                    if (!empty($js)) {
                        $js = $this->rewriteTemplateAssetUrlsInScript($js, $path);
                        preg_match('/type=["\']([^"\']+)["\']/', $tag, $typeMatch);
                        $typeObj = !empty($typeMatch) ? ' type="' . $typeMatch[1] . '"' : '';
                        
                        $inlineTag = "<script{$typeObj}>\n/* Inlined: {$path} */\n" . $js . "\n</script>";
                        $html = str_replace($tag, $inlineTag, $html);
                    } else {
                        $html = str_replace($tag, '', $html);
                    }
                } else {
                    $html = str_replace($tag, '', $html);
                }
            }
        }

        return $html;
    }

    /**
     * Rewrite hardcoded root asset URLs in template scripts to their real
     * /storage builder-template location.
     */
    protected function rewriteTemplateAssetUrlsInScript(string $js, string $scriptStoragePath): string
    {
        if ($js === '' || $scriptStoragePath === '') {
            return $js;
        }

        if (!preg_match('#^/storage/builder-templates/([^/]+)/([^/]+)/assets/js/#', $scriptStoragePath, $m)) {
            return $js;
        }

        $baseAssets = '/storage/builder-templates/' . $m[1] . '/' . $m[2] . '/assets';

        // Common hardcoded pattern in exported templates.
        return preg_replace(
            '#([\'"])\/assets\/3d-object\/([^\'"]+)\1#',
            '$1' . $baseAssets . '/3d-object/$2$1',
            $js
        ) ?? $js;
    }

    /**
     * Inject Material Icons/Symbols font links into preview/live HTML when
     * icon classes are present in page markup.
     */
    protected function injectMaterialIconsAssets(string $html): string
    {
        if ($html === '') {
            return $html;
        }

        $iconFonts = [
            'material-symbols-outlined' => 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0',
            'material-symbols-rounded' => 'https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0',
            'material-symbols-sharp' => 'https://fonts.googleapis.com/css2?family=Material+Symbols+Sharp:opsz,wght,FILL,GRAD@24,400,0,0',
            'material-icons' => 'https://fonts.googleapis.com/icon?family=Material+Icons',
            'material-icons-outlined' => 'https://fonts.googleapis.com/icon?family=Material+Icons+Outlined',
            'material-icons-round' => 'https://fonts.googleapis.com/icon?family=Material+Icons+Round',
            'material-icons-sharp' => 'https://fonts.googleapis.com/icon?family=Material+Icons+Sharp',
        ];

        $requiredHrefs = [];
        foreach ($iconFonts as $className => $href) {
            if (preg_match('/\b' . preg_quote($className, '/') . '\b/i', $html)) {
                $requiredHrefs[] = $href;
            }
        }

        if (empty($requiredHrefs)) {
            return $html;
        }

        $injections = [];
        foreach (array_unique($requiredHrefs) as $href) {
            if (stripos($html, $href) === false) {
                $injections[] = '<link rel="stylesheet" href="' . $href . '">';
            }
        }

        if (empty($injections)) {
            return $html;
        }

        $payload = implode("\n", $injections) . "\n";

        if (stripos($html, '<head') !== false) {
            return preg_replace('/<head\b[^>]*>/i', '$0' . "\n" . $payload, $html, 1) ?? $html;
        }

        return $payload . $html;
    }
}
