<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\RecordingSession;
use App\Models\Landing;

class RecordingDashboardController extends Controller
{
    public function index($landingPageId = null)
    {
        $landingPage = null;
        $query = RecordingSession::query();

        if ($landingPageId) {
            $landingPage = Landing::where('id', $landingPageId)
                ->whereHas('workspace', function($q) {
                    $q->where('user_id', auth()->id());
                })
                ->firstOrFail();
            $query->where('landing_page_id', $landingPageId);
        } else {
            $query->whereHas('landingPage.workspace', function($q) {
                $q->where('user_id', auth()->id());
            });
        }

        $sessions = $query->withCount('pages')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('dashboard.recordings.index', compact('landingPage', 'sessions'));
    }

    public function show($landingPageId, $sessionId)
    {
        $landingPage = Landing::where('id', $landingPageId)
            ->whereHas('workspace', function($q) {
                $q->where('user_id', auth()->id());
            })
            ->firstOrFail();

        $session = RecordingSession::where('session_id', $sessionId)
            ->where('landing_page_id', $landingPageId)
            ->with(['pages' => function($q) {
                $q->orderBy('entered_at');
            }, 'pages.events' => function($q) {
                $q->orderBy('created_at');
            }])
            ->firstOrFail();

        return view('dashboard.recordings.show', compact('landingPage', 'session'));
    }

    public function destroy($sessionId)
    {
        $session = RecordingSession::where('session_id', $sessionId)
            ->whereHas('landingPage.workspace', function($query) {
                $query->where('user_id', auth()->id());
            })
            ->firstOrFail();

        $session->delete();

        return back()->with('success', 'Session deleted successfully.');
    }
}
