<?php

namespace App\Http\Controllers;

use App\Events\Email\FormSubmitted;
use App\Http\Controllers\Controller;
use App\Models\EmailAutomation;
use App\Models\FormEndpoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FormEndpointController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'default_automation_id' => 'nullable|exists:email_automations,id',
        ]);

        $workspace = Auth::user()->workspaces()->first();
        $automationId = $validated['default_automation_id'] ?? null;
        if ($automationId) {
            $exists = EmailAutomation::query()
                ->where('id', $automationId)
                ->where('user_id', Auth::id())
                ->exists();
            if (!$exists) {
                return redirect()->back()->withErrors([
                    'default_automation_id' => 'Selected automation does not belong to your account.',
                ]);
            }
        }

        $workspace->formEndpoints()->create([
            'name' => $validated['name'],
            'default_automation_id' => $automationId,
        ]);

        return redirect()->back()->with('success', 'Form Endpoint created.');
    }

    public function destroy(FormEndpoint $form_endpoint)
    {
        $endpoint = $form_endpoint;
        // Check ownership
        if ($endpoint->workspace->user_id != Auth::id()) {
            abort(403);
        }

        $endpoint->delete();

        return redirect()->back()->with('success', 'Form Endpoint deleted.');
    }

    public function update(Request $request, FormEndpoint $form_endpoint)
    {
        $endpoint = $form_endpoint;
        if ($endpoint->workspace->user_id != Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'default_automation_id' => 'nullable|exists:email_automations,id',
        ]);

        $automationId = $validated['default_automation_id'] ?? null;
        if ($automationId) {
            $exists = EmailAutomation::query()
                ->where('id', $automationId)
                ->where('user_id', Auth::id())
                ->exists();

            if (!$exists) {
                return redirect()->back()->withErrors([
                    'default_automation_id' => 'Selected automation does not belong to your account.',
                ]);
            }
        }

        $endpoint->update([
            'default_automation_id' => $automationId,
        ]);

        return redirect()->back()->with('success', 'Endpoint automation updated.');
    }

    // Public Submission Endpoint
    public function submit(Request $request, $uuid)
    {
        $endpoint = FormEndpoint::where('uuid', $uuid)->firstOrFail();

        // Validate basic headers/data if needed? For now, open.
        
        $data = $request->except(['_token']);

        // Log for debugging
        \Illuminate\Support\Facades\Log::info('Form Submission:', [
            'uuid' => $uuid,
            'data' => $data,
            'ip' => $request->ip()
        ]);

        // Detect common and dynamic field names from landing forms (e.g. field_email_1)
        $email = $this->extractEmailFromPayload($data);
        $landingId = $data['landing_id'] ?? null;

        $form = $endpoint->forms()->create([
            'landing_id' => $landingId,
            'data' => $data,
            'email' => $email,
            'ip_address' => $request->ip(),
        ]);

        if ($endpoint->workspace?->user_id) {
            event(new FormSubmitted(
                userId: $endpoint->workspace->user_id,
                formId: $form->id,
                landingId: $landingId ? (int) $landingId : null,
                formEndpointId: $endpoint->id,
                preferredAutomationId: $endpoint->default_automation_id,
                email: $email,
                firstName: $this->extractFromPayload($data, ['first_name', 'billing_first_name'], ['first_name', 'firstname', 'fname', 'name']),
                lastName: $this->extractFromPayload($data, ['last_name', 'billing_last_name'], ['last_name', 'lastname', 'lname']),
                phone: $this->extractFromPayload($data, ['phone', 'billing_phone'], ['phone', 'mobile', 'whatsapp', 'tel']),
                data: $data
            ));
        }

        // Default behavior: redirect back with success param (or JSON if asked)
        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        // Allow redirect override
        if ($request->has('_redirect')) {
             return redirect($request->input('_redirect'));
        }

        return redirect()->back()->with('success', 'Form submitted successfully.');
    }

    private function extractEmailFromPayload(array $data): ?string
    {
        $candidate = $this->extractFromPayload(
            $data,
            ['email', 'contact_email', 'billing_email'],
            ['email', 'e-mail', 'mail']
        );

        if (!$candidate) {
            return null;
        }

        $normalized = Str::lower(trim($candidate));
        return filter_var($normalized, FILTER_VALIDATE_EMAIL) ? $normalized : null;
    }

    private function extractFromPayload(array $data, array $preferredKeys = [], array $containsHints = []): ?string
    {
        $normalized = [];
        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $normalized[Str::lower((string) $key)] = trim((string) $value);
            }
        }

        foreach ($preferredKeys as $key) {
            $value = $normalized[Str::lower($key)] ?? null;
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        if (empty($containsHints)) {
            return null;
        }

        foreach ($normalized as $key => $value) {
            if ($value === '') {
                continue;
            }

            foreach ($containsHints as $hint) {
                if (str_contains($key, Str::lower($hint))) {
                    return $value;
                }
            }
        }

        return null;
    }
}
