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
        $activeLandingPage = app()->has('active_landing_page') 
            ? app('active_landing_page') 
            : null;

        if ($activeLandingPage) {
            $page = $activeLandingPage->pages()->where('type', 'index')->first() ?? $activeLandingPage->pages()->first();
            if ($page) {
                $html = view('landing_page', ['landing' => $activeLandingPage, 'page' => $page])->render();
                return response($this->injectRecordingSnippet($html, $activeLandingPage, 'landing'));
            }
        }

        // Find the "Main" landing
        $mainLanding = Landing::where('is_main', true)->where('status', 'published')->first();

        if (!$mainLanding) {
            return view('welcome'); // Default Laravel welcome
        }

        // Find the 'index' page of this landing
        $page = $mainLanding->pages()->where('type', 'index')->first();

        if (!$page) {
            abort(404);
        }

        $html = view('landing_page', ['landing' => $mainLanding, 'page' => $page])->render();
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

                $html = view('landing_page', $data)->render();
                $pageTypeMapped = in_array($page->type, ['checkout', 'thankyou']) ? $page->type : 'landing';
                return response($this->injectRecordingSnippet($html, $activeLandingPage, $pageTypeMapped));
            }
        }

        // 1. First, try to find a page under the Main Landing
        $mainLanding = Landing::where('is_main', true)->first();

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

                $html = view('landing_page', $data)->render();
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

                $html = view('landing_page', $data)->render();
                $pageTypeMapped = in_array($page->type, ['checkout', 'thankyou']) ? $page->type : 'landing';
                return response($this->injectRecordingSnippet($html, $landing, $pageTypeMapped));
            }
        }

        // 3. Nothing found - 404
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

        $html = view('landing_page', $data)->render();
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

        $html = view('landing_page', $data)->render();
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

        $html = view('landing_page', $data)->render();
        $pageTypeMapped = in_array($page->type, ['checkout', 'thankyou']) ? $page->type : 'landing';
        return response($this->injectRecordingSnippet($html, $landing, $pageTypeMapped));
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
}
