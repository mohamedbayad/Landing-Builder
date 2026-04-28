<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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

        $workspaceLandings = $workspace->landings()
            ->select(['id', 'name', 'slug', 'status'])
            ->orderByDesc('id')
            ->limit(150)
            ->get();

        return view('settings.index', compact('workspace', 'workspaceLandings'));
    }

    public function update(Request $request)
    {
        $workspace = Auth::user()->workspaces()->first();
        $settings = $workspace->settings;

        // Basic Workspace Fields (Payment, Currency)
        $activeTab = 'theme';

        if ($request->has('currency')) {
            $activeTab = 'payment';
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
            $activeTab = 'theme';
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
            $activeTab = 'whatsapp';
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

        // AI Settings
        if ($request->has('ai_provider')) {
            $activeTab = 'ai_settings';
            $validatedAI = $request->validate([
                'ai_provider' => 'required|string',
                'ai_api_key' => 'nullable|string',
                'ai_model' => 'nullable|string',
            ]);

            if (empty($validatedAI['ai_model'])) {
                unset($validatedAI['ai_model']);
            }
            if (empty($validatedAI['ai_api_key'])) {
                unset($validatedAI['ai_api_key']);
            }

            $settings->update($validatedAI);
        }

        // Subscriber Chatbot CTA Settings
        if ($request->has('chatbot_cta_settings_check')) {
            $activeTab = 'ai_settings';
            $validatedChatbotCta = $request->validate([
                'chatbot_custom_cta_enabled' => 'nullable|in:1,0,on,off',
                'chatbot_custom_cta_text' => 'nullable|string|max:120',
                'chatbot_custom_cta_type' => ['nullable', 'string', Rule::in(['form', 'whatsapp', 'instagram', 'custom_link', 'custom_phone'])],
                'chatbot_custom_cta_target' => 'nullable|string|max:255',
                'chatbot_custom_cta_landing_id' => [
                    'nullable',
                    'integer',
                    Rule::exists('landings', 'id')->where(fn ($query) => $query->where('workspace_id', $workspace->id)),
                ],
            ]);

            $isEnabled = $request->has('chatbot_custom_cta_enabled');
            $ctaType = (string) ($validatedChatbotCta['chatbot_custom_cta_type'] ?? 'form');
            $ctaText = trim((string) ($validatedChatbotCta['chatbot_custom_cta_text'] ?? ''));
            $ctaTarget = trim((string) ($validatedChatbotCta['chatbot_custom_cta_target'] ?? ''));
            $landingId = $validatedChatbotCta['chatbot_custom_cta_landing_id'] ?? null;

            if ($ctaType === 'whatsapp' || $ctaType === 'custom_phone') {
                $normalizedPhone = preg_replace('/[^0-9+]/', '', $ctaTarget) ?? '';
                if ($isEnabled && $normalizedPhone === '') {
                    return redirect()
                        ->route('settings.index')
                        ->withErrors(['chatbot_custom_cta_target' => 'Please enter a valid phone number for this CTA type.'])
                        ->withInput()
                        ->with(['activeTab' => 'ai_settings']);
                }
                $ctaTarget = $normalizedPhone;
            }

            if ($ctaType === 'instagram' || $ctaType === 'custom_link') {
                $isValidUrl = $ctaTarget === '' ? false : filter_var($ctaTarget, FILTER_VALIDATE_URL) !== false;
                if ($isEnabled && !$isValidUrl) {
                    return redirect()
                        ->route('settings.index')
                        ->withErrors(['chatbot_custom_cta_target' => 'Please enter a valid URL for this CTA type.'])
                        ->withInput()
                        ->with(['activeTab' => 'ai_settings']);
                }
            }

            $settings->update([
                'chatbot_custom_cta_enabled' => $isEnabled,
                'chatbot_custom_cta_text' => $ctaText !== '' ? $ctaText : null,
                'chatbot_custom_cta_type' => $ctaType,
                'chatbot_custom_cta_target' => $ctaTarget !== '' ? $ctaTarget : null,
                'chatbot_custom_cta_landing_id' => $landingId,
            ]);
        }

        return redirect()->route('settings.index')->with(['status' => 'settings-updated', 'activeTab' => $activeTab]);
    }
}
