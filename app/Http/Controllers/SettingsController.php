<?php

namespace App\Http\Controllers;

use App\Services\LicenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    public function index()
    {
        $workspace = Auth::user()->workspaces()->first();
        if (!$workspace) {
            $workspace = Auth::user()->workspaces()->create(['name' => Auth::user()->name . "'s Workspace"]);
        }
        
        // Ensure settings exist
        if (!$workspace->settings) {
            $workspace->settings()->create([]);
            $workspace->refresh();
        }

        return view('settings.index', compact('workspace'));
    }

    public function update(Request $request, LicenseService $licenseService)
    {
        $workspace = Auth::user()->workspaces()->first();
        $settings = $workspace->settings;

        // License Activation
        if ($request->has('license_key')) {
            $validated = $request->validate([
                'license_key' => 'required|string',
            ]);

            $result = $licenseService->activate($validated['license_key']);

            if ($result['success']) {
                $settings->update([
                    'license_key' => $validated['license_key'],
                    'license_status' => 'active',
                    'license_data' => $result,
                ]);
                
                // Cache the token for middleware usage
                Cache::put('license_token', $result['token'], now()->addDays(30)); 

                return redirect()->route('settings.index')->with('status', 'license-activated');
            } else {
                // If the user tried to re-activate the CURRENT key and it failed, mark as inactive
                if ($settings->license_key === $validated['license_key']) {
                     $settings->update(['license_status' => 'inactive']);
                     Cache::forget('license_token');
                }

                return redirect()->route('settings.index')
                    ->with('error', $result['message'])
                    ->withInput(); // Keep the key in input
            }
        }
        
        // Manual Deactivation / Removal
        if ($request->has('remove_license')) {
             $settings->update([
                'license_key' => null,
                'license_status' => 'inactive',
                'license_data' => null,
            ]);
            Cache::forget('license_token');
            return redirect()->route('settings.index')->with('status', 'license-removed');
        }

        // Basic Workspace Fields (Payment, Currency)
        if ($request->has('currency')) {
            $validatedWorkspace = $request->validate([
                'currency' => 'required|string|size:3',
                'stripe_publishable_key' => 'nullable|string',
                'stripe_secret_key' => 'nullable|string',
                'paypal_client_id' => 'nullable|string',
                'paypal_secret' => 'nullable|string',
            ]);
            $workspace->update($validatedWorkspace);
        }

        // Theme Settings
        if ($request->has('dashboard_direction')) {
            $validatedTheme = $request->validate([
                'dashboard_direction' => 'required|in:ltr,rtl',
                'dashboard_primary_color' => 'nullable|string',
                'sidebar_bg' => 'nullable|string',
                'sidebar_text' => 'nullable|string',
                'sidebar_active' => 'nullable|string',
                'sidebar_hover' => 'nullable|string',
                'checkout_style' => 'required|string',
                'thankyou_style' => 'required|string',
                'thankyou_show_summary' => 'nullable|in:1,0,on,off',
                'thankyou_show_invoice_btn' => 'nullable|in:1,0,on,off',
            ]);
            
            // Handle Checkboxes
            $validatedTheme['thankyou_show_summary'] = $request->has('thankyou_show_summary');
            $validatedTheme['thankyou_show_invoice_btn'] = $request->has('thankyou_show_invoice_btn');

            $settings->update($validatedTheme);
        }

        // WhatsApp Settings
        if ($request->has('whatsapp_phone_check')) { // specific hidden input to detect tab
             $validatedWA = $request->validate([
                'whatsapp_enabled' => 'nullable|in:1,0,on,off',
                'whatsapp_phone' => 'nullable|string',
                'whatsapp_redirect_enabled' => 'nullable|in:1,0,on,off',
                'whatsapp_redirect_seconds' => 'nullable|integer|min:0|max:60',
                'whatsapp_template_landing' => 'nullable|string',
                'whatsapp_template_thankyou' => 'nullable|string',
            ]);

             // Handle Checkboxes
            $validatedWA['whatsapp_enabled'] = $request->has('whatsapp_enabled');
            $validatedWA['whatsapp_redirect_enabled'] = $request->has('whatsapp_redirect_enabled');

            $settings->update($validatedWA);
        }

        return redirect()->route('settings.index')->with('status', 'settings-updated');
    }
}
