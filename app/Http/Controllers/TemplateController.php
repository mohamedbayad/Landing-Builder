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

        // Increase execution time for large files (5 minutes)
        set_time_limit(300);

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
            // Try 'tar' command (available on Windows 10+ and faster than PowerShell)
            // tar -xf source.zip -C destination
            $tarCommand = "tar -xf \"$localZipPath\" -C \"$storagePath\"";
            exec($tarCommand, $tarOutput, $tarReturn);
            
            if ($tarReturn === 0) {
                $extracted = true;
            }
        }

        if (!$extracted) {
            // Fallback: PowerShell Expand-Archive (Slow but reliable)
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
            'storage_path' => $publicPath, // Store relative path for later use
            'is_active' => true,
        ]);

        // Template files are now stored on disk and will be processed when applying to landing
        // No need to create TemplatePage records during upload
        
        return redirect()->route('templates.index')->with('success', 'Template uploaded successfully.');
    }

    public function import(Request $request, Template $template)
    {
        // 1. Create Landing with UUID
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
        ]);

        // 2. Copy Template Files to Landing Storage
        // Get template storage path from database
        $templatePath = storage_path("app/public/{$template->storage_path}");

        if (!$templatePath || !File::exists($templatePath)) {
            return redirect()->route('dashboard')->with('error', 'Template files not found');
        }

        // Create landing directory
        $landingPath = storage_path("app/public/landings/{$landing->uuid}");
        if (!File::exists($landingPath)) {
            File::makeDirectory($landingPath, 0755, true);
        }

        // Check if template has a single root directory (common in ZIPs)
        // If so, copy the CONTENTS of that directory, not the directory itself
        $items = File::directories($templatePath);
        $files = File::files($templatePath);
        
        // Filter out ignored directories (e.g. __MACOSX, .git)
        $realItems = array_filter($items, function($item) {
             $basename = basename($item);
             return !in_array($basename, ['__MACOSX', '.git']);
        });

        // Filter out ignored files (e.g. source.zip which is the upload itself, system files)
        $realFiles = array_filter($files, function($file) {
             $basename = $file->getFilename();
             return !in_array($basename, ['source.zip', '.DS_Store', 'Thumbs.db', 'desktop.ini']);
        });

        if (count($realItems) === 1 && count($realFiles) === 0) {
            // Single root directory - copy its contents
            $rootDir = reset($realItems); // Get the first real directory
            File::copyDirectory($rootDir, $landingPath);
        } else {
            // Multiple items or files at root - copy everything
            File::copyDirectory($templatePath, $landingPath);
        }

        // Cleanup: Remove source.zip from landing if it was copied
        if (File::exists($landingPath . '/source.zip')) {
            File::delete($landingPath . '/source.zip');
        }

        // 3. Process HTML with Service
        $processor = new \App\Services\TemplateZipProcessorService();
        $indexHtmlPath = $landingPath . '/index.html';
        $baseUrl = "/storage/landings/{$landing->uuid}/";

        if (!File::exists($indexHtmlPath)) {
            return redirect()->route('dashboard')->with('error', 'Template index.html not found');
        }

        $parsed = $processor->processHtml($indexHtmlPath, $baseUrl);

        // 4. Create Landing Pages with Processed Content
        LandingPage::create([
            'landing_id' => $landing->id,
            'type' => 'index',
            'name' => 'Home',
            'slug' => 'index',
            'status' => 'draft',
            'html' => $parsed['body_html'],
            'css' => $parsed['css'],
            'js' => '',
        ]);

        // 5. Save HEAD elements to Landing Settings
        $landing->settings()->updateOrCreate([], [
            'meta_title' => $parsed['title'] ?: $landing->name,
            'custom_head_scripts' => $parsed['custom_head'],
        ]);

        // 6. Index Media for Library (Recursive scan of entire landing folder)
        // This ensures images in assets/media/imgs or any other structure are found.
        if (File::exists($landingPath)) {
            $processor->indexMedia($landingPath, $landing->id, Auth::id());
        }

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

        return redirect()->route('dashboard')->with('status', 'Landing created from template successfully!');
    }
}
