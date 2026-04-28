<?php

namespace App\Http\Controllers;

use App\Models\Landing;
use App\Models\LandingPage;
use Illuminate\Http\Request;

class PublicLandingController extends Controller
{
    /**
     * Serve the main landing page at root URL /
     */
    public function home()
    {
        // Resolve landing in this order:
        // 1) active custom-domain landing
        // 2) global published main landing (public fallback)
        $mainLanding = $this->resolveMainLandingForRequest();

        if (!$mainLanding) {
            return view('welcome'); // Default Laravel welcome
        }

        // Find the 'index' page of this landing (fallback to first page)
        $page = $mainLanding->pages()->where('type', 'index')->first()
            ?? $mainLanding->pages()->first();

        if (!$page) {
            abort(404);
        }

        $html = $this->renderLandingPage($mainLanding, $page);
        return response($this->injectRecordingSnippet($html, $mainLanding, 'landing'));
    }

    /**
     * Serve a sub-page of the main landing (e.g. /checkout)
     * OR serve a specific landing by its slug (e.g., /lp-copy-3JDCG5)
     */
    public function page($slug)
    {
        $activeLandingPage = app()->has('active_landing_page') 
            ? app('active_landing_page') 
            : null;

        if ($activeLandingPage) {
            $page = $activeLandingPage->pages()->where('slug', $slug)->first();
            if ($page) {
                $data = ['landing' => $activeLandingPage, 'page' => $page];
                if ($page->type === 'checkout') {
                    $data = array_merge($data, $this->getCheckoutData($activeLandingPage));
                }
                
                // Thank You Page - add layout
                if ($page->type === 'thankyou') {
                    $wsSettings = $activeLandingPage->workspace->settings ?? null;
                    $data['thankyouLayout'] = $wsSettings->thankyou_style ?? 'thankyou_1';
                    
                    if (request()->has('lead')) {
                        $leadId = request()->query('lead');
                        $lead = \App\Models\Lead::find($leadId);
                        if ($lead && $lead->landing_id == $activeLandingPage->id) {
                            $data['lead'] = $lead;
                        }
                    }
                }

                $html = $this->renderLandingPage($activeLandingPage, $page, $data);
        $html = $this->normalizePreviewAssets($html);
                $pageTypeMapped = in_array($page->type, ['checkout', 'thankyou']) ? $page->type : 'landing';
                return response($this->injectRecordingSnippet($html, $activeLandingPage, $pageTypeMapped));
            }
        }

        // 1. First, try to find a page under the resolved Main Landing
        // (global published main unless custom-domain binding exists)
        $mainLanding = $this->resolveMainLandingForRequest();

        if ($mainLanding) {
            // Check if slug matches a page under main landing
            $page = $mainLanding->pages()->where('slug', $slug)->first();
            
            if ($page) {
                // Check visibility
                if ($mainLanding->status !== 'published') {
                    if (!request()->user() || request()->user()->id != $mainLanding->workspace->user_id) {
                        abort(404);
                    }
                }

                // Security: Protect Thank You Pages
                if ($page->type === 'thankyou') {
                    if (!request()->hasValidSignature()) {
                        abort(403, 'Unauthorized access to Thank You page.');
                    }
                }

                $data = ['landing' => $mainLanding, 'page' => $page];

                if ($page->type === 'checkout') {
                    $data = array_merge($data, $this->getCheckoutData($mainLanding));
                }
                
                // Thank You Page - add layout
                if ($page->type === 'thankyou') {
                    $wsSettings = $mainLanding->workspace->settings ?? null;
                    $data['thankyouLayout'] = $wsSettings->thankyou_style ?? 'thankyou_1';
                    
                    if (request()->has('lead')) {
                        $leadId = request()->query('lead');
                        $lead = \App\Models\Lead::find($leadId);
                        if ($lead && $lead->landing_id == $mainLanding->id) {
                            $data['lead'] = $lead;
                        }
                    }
                }

                $html = $this->renderLandingPage($mainLanding, $page, $data);
        $html = $this->normalizePreviewAssets($html);
                $pageTypeMapped = in_array($page->type, ['checkout', 'thankyou']) ? $page->type : 'landing';
                return response($this->injectRecordingSnippet($html, $mainLanding, $pageTypeMapped));
            }
        }

        // 2. If not a main landing page, check if slug matches another Landing
        $landing = Landing::where('slug', $slug)->first();

        if ($landing) {
            // Check visibility - must be published for public access
            if ($landing->status !== 'published') {
                if (!request()->user() || request()->user()->id != $landing->workspace->user_id) {
                    abort(404);
                }
            }

            // Find the index page of this landing
            $page = $landing->pages()->where('type', 'index')->first();

            if (!$page) {
                // Fallback to first page if no index
                $page = $landing->pages()->first();
            }

            if ($page) {
                $data = ['landing' => $landing, 'page' => $page];

                if ($page->type === 'checkout') {
                    $data = array_merge($data, $this->getCheckoutData($landing));
                }

                $html = $this->renderLandingPage($landing, $page, $data);
        $html = $this->normalizePreviewAssets($html);
                $pageTypeMapped = in_array($page->type, ['checkout', 'thankyou']) ? $page->type : 'landing';
                return response($this->injectRecordingSnippet($html, $landing, $pageTypeMapped));
            }
        }

        // 3. Nothing found - 404
        abort(404);
    }

    /**
     * Resolve which landing should act as "main" for the current request.
     *
     * Priority:
     * 1) Active custom-domain landing (if bound by middleware)
     * 2) Global published main landing
     */
    private function resolveMainLandingForRequest(): ?Landing
    {
        $activeLandingPage = app()->has('active_landing_page')
            ? app('active_landing_page')
            : null;

        if ($activeLandingPage instanceof Landing) {
            return $activeLandingPage;
        }

        return Landing::where('is_main', true)
            ->where('status', 'published')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();
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
        // Visibility check
        if ($landing->status !== 'published') {
            if (!request()->user() || request()->user()->id != $landing->workspace->user_id) {
                abort(404);
            }
        }

        $page = $landing->pages()->where('type', 'checkout')->first();

        if (!$page) {
            abort(404, 'Checkout page not found.');
        }

        $data = ['landing' => $landing, 'page' => $page];
        $data = array_merge($data, $this->getCheckoutData($landing));

        $html = $this->renderLandingPage($landing, $page, $data);
        $html = $this->normalizePreviewAssets($html);
        return response($this->injectRecordingSnippet($html, $landing, 'checkout'));
    }

    /**
     * Serve a sub-page of a specific landing (e.g., /lp-copy-3JDCG5/checkout)
     */
    public function landingSubPage($landingSlug, $pageSlug)
    {
        $landing = Landing::where('slug', $landingSlug)->first();

        if (!$landing) {
            abort(404);
        }

        // Check visibility
        if ($landing->status !== 'published') {
            if (!request()->user() || request()->user()->id != $landing->workspace->user_id) {
                abort(404);
            }
        }

        // Find the page by slug
        $page = $landing->pages()->where('slug', $pageSlug)->first();

        if (!$page) {
            abort(404);
        }

        // Security: Protect Thank You Pages
        if ($page->type === 'thankyou') {
            if (!request()->hasValidSignature()) {
                abort(403, 'Unauthorized access to Thank You page.');
            }
        }

        $data = ['landing' => $landing, 'page' => $page];

        if ($page->type === 'checkout') {
            $data = array_merge($data, $this->getCheckoutData($landing));
        }

        // Thank You Page - add layout
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
            return parse_url($decoded, PHP_URL_PATH) ?: null;
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
            return $path;
        }

        $pos = stripos($path, '/storage/');
        if ($pos !== false) {
            return substr($path, $pos);
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
