<?php

namespace App\Http\Controllers;

use App\Events\Email\FormSubmitted;
use App\Models\EmailAutomation;
use App\Models\Form;
use App\Models\Landing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormController extends Controller
{
    public function index()
    {
        $workspace = Auth::user()->workspaces()->first();

        if (!$workspace) {
            return view('forms.index', [
                'forms' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                'endpoints' => collect(),
                'automations' => collect(),
            ]);
        }
        
        $forms = Form::with(['landing', 'formEndpoint'])
            ->where(function($query) use ($workspace) {
                 $query->whereIn('landing_id', $workspace->landings()->pluck('id'))
                       ->orWhereIn('form_endpoint_id', $workspace->formEndpoints()->pluck('id'));
            })
            ->latest()
            ->paginate(15);

        $endpoints = $workspace->formEndpoints()->with('defaultAutomation')->withCount('forms')->get();
        $automations = EmailAutomation::query()
            ->where('user_id', Auth::id())
            ->where('trigger_type', 'form_submitted')
            ->orderBy('name')
            ->get(['id', 'name', 'status']);

        return view('forms.index', compact('forms', 'endpoints', 'automations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'landing_id' => 'required|exists:landings,id',
            'data' => 'required|string', // JSON string from form builder
            'email' => 'nullable|email',
        ]);

        $formData = json_decode($validated['data'], true);
        $landing = Landing::query()->find($validated['landing_id']);

        $form = Form::create([
            'landing_id' => $validated['landing_id'],
            'email' => $validated['email'] ?? ($formData['email'] ?? null),
            'data' => $formData,
            'ip_address' => $request->ip(),
        ]);

        $userId = $landing->workspace?->user_id;
        if ($userId) {
            event(new FormSubmitted(
                userId: $userId,
                formId: $form->id,
                landingId: $landing->id,
                formEndpointId: null,
                preferredAutomationId: $landing->settings?->form_automation_id,
                email: $form->email,
                firstName: $formData['first_name'] ?? $formData['billing_first_name'] ?? null,
                lastName: $formData['last_name'] ?? $formData['billing_last_name'] ?? null,
                phone: $formData['phone'] ?? $formData['billing_phone'] ?? null,
                data: $formData
            ));
        }

        return response()->json(['success' => true]);
    }
}
