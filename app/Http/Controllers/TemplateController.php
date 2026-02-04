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

class TemplateController extends Controller
{
    public function index()
    {
        $templates = Template::where('is_active', true)->get();
        return view('templates.index', compact('templates'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'template_zip' => 'required|file|mimes:zip|max:10240', // 10MB max
        ]);

        $zipFile = $request->file('template_zip');
        $uniqueId = time() . '_' . Str::random(8); // Folder name
        $publicPath = 'templates/' . $uniqueId; // Relative to storage/app/public
        $storagePath = storage_path('app/public/' . $publicPath); // Absolute path
        
        // Ensure directory exists
        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
        }

        // Move uploaded file to storage first (avoids temp permission issues)
        $localZipPath = $storagePath . '/source.zip';
        $zipFile->move($storagePath, 'source.zip');

        // 1. Extract ZIP
        $extracted = false;
        
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive;
            if ($zip->open($localZipPath) === TRUE) {
                $zip->extractTo($storagePath);
                $zip->close();
                $extracted = true;
            }
        } 
        
        if (!$extracted) {
            // Fallback: PowerShell Expand-Archive
            // Use local path which we know is safe
            $command = "powershell -Command \"Expand-Archive -Path '$localZipPath' -DestinationPath '$storagePath' -Force\"";
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                 // Clean up
                 File::deleteDirectory($storagePath);
                 return back()->with('error', 'Failed to extract ZIP. Ensure PowerShell is available or install php-zip extension.');
            }
        }

        // 2. Create Template Record
        // Check for preview image (recursive search or specific location?)
        // Let's assume screenshots are at the root of a folder if nested
        $previewPath = null;
        if (File::exists($storagePath . '/screenshot.png')) {
            $previewPath = '/storage/' . $publicPath . '/screenshot.png';
        }

        $template = Template::create([
            'name' => $request->name,
            'description' => 'Uploaded custom template.',
            'preview_image_path' => $previewPath,
            'is_active' => true,
        ]);

        // 3. Process Pages
        // Recursive search for HTML files
        $files = File::allFiles($storagePath);
        $publicUrlBase = '/storage/' . $publicPath . '/'; 

        $pagesFound = 0;
        foreach ($files as $file) {
            if ($file->getExtension() === 'html') {
                $filename = $file->getFilename(); 
                $slug = $file->getFilenameWithoutExtension(); 
                
                // Determine Type
                $type = 'other';
                if ($slug === 'index') $type = 'index';
                if ($slug === 'checkout') $type = 'checkout';
                if ($slug === 'thankyou' || $slug === 'thank-you') $type = 'thankyou';

                $rawContent = File::get($file->getPathname());

                // Parse HTML to extract body and relevant head tags (CSS/JS)
                $bodyContent = $rawContent;
                $extraHead = '';

                // Extract Body
                if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $rawContent, $matches)) {
                    $bodyContent = $matches[1];
                }

                // Extract Head tags (link, script, style) but exclude standard ones (title, meta charset/viewport, tailwind cdn)
                if (preg_match('/<head[^>]*>(.*?)<\/head>/is', $rawContent, $matches)) {
                    $headContent = $matches[1];
                    // Match link, script, style
                    preg_match_all('/<(link|script|style)[^>]*>.*?<\/\1>|<(link|script)[^>]*>/is', $headContent, $tags);
                    if (!empty($tags[0])) {
                        foreach ($tags[0] as $tag) {
                            // Filter out charset and viewport meta tags which are handled by the main layout
                            if (strpos($tag, 'charset') === false && strpos($tag, 'viewport') === false) {
                                $extraHead .= $tag . "\n";
                            }
                        }
                    }
                }

                // Prepend extra head content to body content so it's included in the page
                // Ideally this would go to $css or $js fields, but for simplicity we keep it in html for now
                // or we wrap it in a container? NO, just prepend.
                $content = $extraHead . $bodyContent;

                // 4. Asset Replacement
                // Calculate relative path from this file to the storage root to adjust assets if needed?
                // For simplicity, we assume assets are referenced as "assets/..." in the HTML.
                // We just prefix them with the Public URL Base + (Subdirectory Path if needed)
                // Actually, regardless of where the file is, if it says "assets/img.jpg", and assets is at root...
                // If structure is:
                // root/
                //   index.html
                //   assets/
                // Then index.html -> "assets/..." works if we map "assets/" -> "$publicUrlBase/assets/"
                
                // If structure is:
                // root/
                //   mysite/
                //     index.html
                //     assets/
                // Then index.html -> "assets/..." works if we map "assets/" -> "$publicUrlBase/mysite/assets/"
                
                // We can find the relative path of the file to the storage path
                $relativePath = $file->getRelativePath(); // e.g. "mysite" or "" (empty)
                
                // Update Base URL to include the subdirectory
                $currentBaseUrl = $publicUrlBase . ($relativePath ? $relativePath . '/' : '');

                // Generic Relative Path Fix for href and src
                // Matches href="anything" that doesn't start with http, /, #, or mailto
                $content = preg_replace_callback('/(href|src)=["\']([^"\']+)["\']/i', function($matches) use ($currentBaseUrl) {
                    $attr = $matches[1];
                    $path = $matches[2];
                    
                    // Skip absolute URLs, anchors, or special protocols
                    if (str_starts_with($path, 'http') || str_starts_with($path, '/') || str_starts_with($path, '#') || str_starts_with($path, 'mailto:') || str_starts_with($path, 'tel:')) {
                        return $matches[0];
                    }

                    return $attr . '="' . $currentBaseUrl . $path . '"';
                }, $content);
                
                // Specific fix for template page links (optional, if we want to map them to routes later, but for now keep as is or assume they are static)
                // The regex above will assume they are assets. 
                // We might want to revert .html links if we want to handle them as pages, but for GrapesJS imported blocks, 
                // we mainly care about images and css.
                // Let's explicitly fix common page links if we want them to remain navigable or replaceable.
                // But the user issue is styles. The regex above fixes <link href="style.css"> to <link href=".../style.css">


                TemplatePage::create([
                    'template_id' => $template->id,
                    'type' => $type,
                    'name' => ucfirst($slug),
                    'slug' => $slug,
                    'html' => $content,
                    'css' => '', 
                    'js' => '',
                ]);
                $pagesFound++;
            }
        }
        
        if ($pagesFound === 0) {
             return back()->with('error', 'Uploaded ZIP contained no HTML files.');
        }

        return redirect()->route('templates.index')->with('status', 'Template uploaded successfully! Found ' . $pagesFound . ' pages.');
    }

    public function import(Request $request, Template $template)
    {
        // 1. Create Landing
        // We'll assume the user has a workspace. For MVP we'll grab the first workspace or create one if missing (though M1 ensures it).
        $user = Auth::user();
        $workspace = $user->workspaces()->first();

        // Safety check if no workspace
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
        ]);

        // 2. Clone Pages
        // Strategy: We only want the 'index' page from the template (as per user request).
        // Then we auto-generate 'checkout' and 'thank-you' pages if they don't exist in the template,
        // or effectively we ALWAYS provide "system" checkout/thankyou pages so they are dynamic.
        
        // Find Index Page
        $indexPage = $template->pages()->where('type', 'index')->first();
        
        if (!$indexPage) {
            // Fallback: take the first page if no specific index
             $indexPage = $template->pages()->first();
        }

        if ($indexPage) {
            LandingPage::create([
                'landing_id' => $landing->id,
                'type' => 'index',
                'name' => 'Home', // Standardize name
                'slug' => 'index',
                'status' => 'draft',
                'html' => $indexPage->html,
                'css' => $indexPage->css,
                'js' => $indexPage->js,
                'grapesjs_json' => $indexPage->grapesjs_json,
            ]);
        }
        
        // 3. Auto-Generate Funnel Steps (Checkout & Thank You) if they weren't imported
        // We actually want to FORCE these to be our system pages so they are dynamic.
        // Even if the template had them, we might ignore them to ensure functionality, 
        // OR we could import them but inject our functionality.
        // User Request: "include only the landing page... generate Checkout and Thank You pages automatically"
        
        // Create Checkout Page
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

        // Create Thank You Page
        LandingPage::create([
            'landing_id' => $landing->id,
            'type' => 'thankyou',
            'name' => 'Thank You',
            'slug' => 'thank-you',
            'status' => 'draft',
            'html' => '<div class="bg-gray-50 min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <div class="text-center mb-10">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900">Thank you for your order!</h2>
            <p class="mt-2 text-lg text-gray-600">Your order has been placed successfully.</p>
        </div>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200 flex justify-between items-center">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Order Information</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Details about your purchase.</p>
                </div>
                <div>
                     <span id="crm-status" class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        Completed
                    </span>
                </div>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                <dl class="sm:divide-y sm:divide-gray-200">
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                        <dd id="crm-fullname" class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">John Doe</dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Email address</dt>
                        <dd id="crm-email" class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">john@example.com</dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                         <dt class="text-sm font-medium text-gray-500">Phone</dt>
                         <dd id="crm-phone" class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">+1 234 567 8900</dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
                        <dt class="text-sm font-medium text-gray-500">Shipping Address</dt>
                        <dd id="crm-address" class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            123 Main St, New York 10001, USA
                        </dd>
                    </div>

                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Order ID</dt>
                        <dd id="crm-order-id" class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 font-mono">ORD-12345-XYZ</dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                         <dt class="text-sm font-medium text-gray-500">Date Placed</dt>
                         <dd id="crm-date" class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">January 1, 2026, 12:00 am</dd>
                    </div>
                     <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                         <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
                         <dd id="crm-payment" class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">Credit Card</dd>
                    </div>

                     <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-t border-gray-200">
                        <dt class="text-sm font-medium text-gray-500 self-center">Item Purchased</dt>
                         <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <div class="flex justify-between font-medium">
                                <span id="crm-product">Premium Plan</span>
                                <span id="crm-amount">USD 99.00</span>
                            </div>
                        </dd>
                    </div>
                     <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
                        <dt class="text-base font-bold text-gray-900 self-center">Total Paid</dt>
                        <dd class="mt-1 text-base font-bold text-gray-900 sm:mt-0 sm:col-span-2 flex justify-end" id="crm-total">
                            USD 99.00
                        </dd>
                    </div>

                </dl>
            </div>
             <div class="bg-gray-50 px-4 py-4 sm:px-6 flex justify-end space-x-3">
                 <a id="crm-invoice-btn" href="#" onclick="return false;" class="inline-flex items-center px-4 py-2 border border-blue-300 shadow-sm text-sm font-medium rounded-md text-blue-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="mr-2 -ml-1 h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Download Invoice
                 </a>
                 <a href="/" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Return Home
                 </a>
            </div>
        </div>
    </div>
</div>',
            'css' => '',
            'js' => '',
        ]);

        // Redirect to Landing pages list (M3) or simple success for now
        // For now, redirect back with success message
        return redirect()->route('dashboard')->with('status', 'Funnel created successfully! (Index imported, Checkout & Thank You generated)');
    }
}
