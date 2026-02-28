<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AnalyticsTrackerService;
use App\Models\Landing;
use App\Models\AnalyticsSession;
use Illuminate\Support\Facades\Cookie;

class AnalyticsTrackerController extends Controller
{
    protected $tracker;

    public function __construct(AnalyticsTrackerService $tracker)
    {
        $this->tracker = $tracker;
    }

    public function trackEvent(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'event_name' => 'required|string',
            'event_data' => 'nullable|array',
            'url' => 'required|url',
            'element_label' => 'nullable|string|max:255',
            'element_type' => 'nullable|string|max:50',
            'element_position' => 'nullable|string|max:100',
        ]);

        // Get Session ID from Cookie
        $sessionId = $request->cookie('lb_session_id');
        
        if (!$sessionId) {
            return response()->json(['status' => 'ignored', 'reason' => 'no_session'], 200);
        }

        // Log Event
        $this->tracker->logEvent(
            $sessionId, 
            $validated['event_name'], 
            $validated['event_data'] ?? [],
            parse_url($validated['url'], PHP_URL_PATH),
            $validated['element_label'] ?? null,
            $validated['element_type'] ?? null,
            $validated['element_position'] ?? null
        );

        return response()->json(['status' => 'ok']);
    }
}
