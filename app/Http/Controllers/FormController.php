<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Form;
use App\Models\Landing;

class FormController extends Controller
{
    public function index()
    {
        $workspace = Auth::user()->workspaces()->first();

        if (!$workspace) {
            return view('forms.index', [
                'forms' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                'endpoints' => collect(),
            ]);
        }
        
        $forms = Form::with(['landing', 'formEndpoint'])
            ->where(function($query) use ($workspace) {
                 $query->whereIn('landing_id', $workspace->landings()->pluck('id'))
                       ->orWhereIn('form_endpoint_id', $workspace->formEndpoints()->pluck('id'));
            })
            ->latest()
            ->paginate(15);

        $endpoints = $workspace->formEndpoints()->withCount('forms')->get();

        return view('forms.index', compact('forms', 'endpoints'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'landing_id' => 'required|exists:landings,id',
            'data' => 'required|string', // JSON string from form builder
            'email' => 'nullable|email',
        ]);

        $formData = json_decode($validated['data'], true);

        Form::create([
            'landing_id' => $validated['landing_id'],
            'email' => $validated['email'] ?? ($formData['email'] ?? null),
            'data' => $formData,
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['success' => true]);
    }
}
