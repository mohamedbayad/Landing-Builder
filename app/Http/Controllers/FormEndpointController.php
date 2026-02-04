<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FormEndpoint;
use App\Models\Form;

class FormEndpointController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $workspace = Auth::user()->workspaces()->first();

        $workspace->formEndpoints()->create([
            'name' => $validated['name'],
        ]);

        return redirect()->back()->with('success', 'Form Endpoint created.');
    }

    public function destroy(FormEndpoint $endpoint)
    {
        // Check ownership
        if ($endpoint->workspace->user_id !== Auth::id()) {
            abort(403);
        }

        $endpoint->delete();

        return redirect()->back()->with('success', 'Form Endpoint deleted.');
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

        // Simplistic check for email in typical fields
        $email = $data['email'] ?? ($data['contact_email'] ?? null);
        $landingId = $data['landing_id'] ?? null;

        $endpoint->forms()->create([
            'landing_id' => $landingId,
            'data' => $data,
            'email' => $email,
            'ip_address' => $request->ip(),
        ]);

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
}
