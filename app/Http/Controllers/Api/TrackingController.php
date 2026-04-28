<?php

namespace App\Http\Controllers\Api;

use App\Events\Email\LeadCreated;
use App\Http\Controllers\Controller;
use App\Models\Landing;
use App\Models\LandingPage;
use App\Models\Lead;
use App\Models\TrackingEvent;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    /**
     * Store a tracking event (page view, click, etc.)
     */
    public function track(Request $request)
    {
        $validated = $request->validate([
            'landing_id' => 'required|exists:landings,id',
            'page_id' => 'nullable|exists:landing_pages,id',
            'type' => 'required|string',
            'data' => 'nullable|array',
        ]);

        $event = TrackingEvent::create([
            'landing_id' => $validated['landing_id'],
            'landing_page_id' => $validated['page_id'] ?? null,
            'type' => $validated['type'],
            'data' => $validated['data'] ?? [],
            'session_id' => $request->input('session_id') ?? $request->ip(), // Fallback to IP if no session provided
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['success' => true, 'id' => $event->id], 201);
    }

    /**
     * Capture a lead form submission
     */
    public function captureLead(Request $request)
    {
        $validated = $request->validate([
            'landing_id' => 'required|exists:landings,id',
            'page_id' => 'nullable|exists:landing_pages,id',
            'email' => 'nullable|email',
            'data' => 'nullable|array',
        ]);

        $lead = Lead::create([
            'landing_id' => $validated['landing_id'],
            'landing_page_id' => $validated['page_id'] ?? null,
            'email' => $validated['email'],
            'data' => $validated['data'] ?? [],
            'ip_address' => $request->ip(),
        ]);

        $landing = Landing::query()->find($validated['landing_id']);
        if ($landing?->workspace?->user_id) {
            event(new LeadCreated(
                userId: $landing->workspace->user_id,
                leadId: $lead->id,
                landingId: $landing->id,
                landingPageId: $lead->landing_page_id,
                email: $lead->email,
                firstName: $lead->first_name,
                lastName: $lead->last_name,
                phone: $lead->phone,
                data: is_array($lead->data) ? $lead->data : []
            ));
        }

        return response()->json(['success' => true, 'id' => $lead->id], 201);
    }
}
