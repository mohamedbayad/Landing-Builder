<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\RecordingSession;
use App\Models\RecordingPage;
use App\Models\RecordingEvent;
use App\Models\LandingPage;
use Illuminate\Support\Str;

class RecordingController extends Controller
{
    public function initSession(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|string|max:64',
            'visitor_id' => 'required|string|max:64',
            'landing_page_id' => 'required|exists:landings,id',
            'screen_width' => 'nullable|integer',
            'screen_height' => 'nullable|integer',
            'referrer' => 'nullable|string|max:500',
            'utm_params' => 'nullable|array',
        ]);

        $deviceType = 'desktop';
        if (isset($validated['screen_width'])) {
            if ($validated['screen_width'] < 768) {
                $deviceType = 'mobile';
            } elseif ($validated['screen_width'] <= 1024) {
                $deviceType = 'tablet';
            }
        }

        $session = RecordingSession::firstOrCreate(
            ['session_id' => $validated['session_id']],
            [
                'id' => (string) Str::uuid(),
                'visitor_id' => $validated['visitor_id'],
                'landing_page_id' => $validated['landing_page_id'],
                'device_type' => $deviceType,
                'screen_width' => $validated['screen_width'] ?? null,
                'screen_height' => $validated['screen_height'] ?? null,
                'referrer' => $validated['referrer'] ?? null,
                'utm_params' => $validated['utm_params'] ?? null,
                'started_at' => now(),
            ]
        );

        return response()->json(['status' => 'ok', 'session_uuid' => $session->id]);
    }

    public function storeEvents(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|exists:recording_sessions,session_id',
            'page_id' => 'required|string|max:64',
            'page_type' => 'required|in:landing,checkout,thankyou',
            'url' => 'required|string|max:1000',
            'events' => 'required|string',
            'events_count' => 'required|integer',
            'entered_at' => 'required|integer',
        ]);

        $page = RecordingPage::firstOrCreate(
            ['id' => $validated['page_id']],
            [
                'session_id' => $validated['session_id'],
                'page_type' => $validated['page_type'],
                'url' => $validated['url'],
                'entered_at' => date('Y-m-d H:i:s', $validated['entered_at'] / 1000),
            ]
        );

        RecordingEvent::create([
            'id' => (string) Str::uuid(),
            'page_id' => $page->id,
            'events_compressed' => $validated['events'],
            'events_count' => $validated['events_count'],
            'size_bytes' => strlen($validated['events']),
        ]);

        return response()->json(['status' => 'ok']);
    }

    public function endSession(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
            'page_id' => 'required|string',
            'exited_at' => 'required|integer',
            'duration_ms' => 'required|integer',
        ]);

        $page = RecordingPage::find($validated['page_id']);
        if ($page) {
            $page->update([
                'exited_at' => date('Y-m-d H:i:s', $validated['exited_at'] / 1000),
                'duration_ms' => $validated['duration_ms'],
            ]);

            $session = RecordingSession::where('session_id', $validated['session_id'])->first();
            if ($session) {
                // Sum all pages duration
                $totalDuration = $session->pages()->sum('duration_ms');
                $session->update([
                    'ended_at' => date('Y-m-d H:i:s', $validated['exited_at'] / 1000),
                    'total_duration_ms' => $totalDuration,
                ]);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    public function markConverted(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
        ]);

        RecordingSession::where('session_id', $validated['session_id'])->update([
            'converted' => true,
        ]);

        return response()->json(['status' => 'ok']);
    }
}
