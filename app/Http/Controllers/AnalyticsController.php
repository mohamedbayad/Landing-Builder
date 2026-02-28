<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AnalyticsService;
use App\Models\Landing;
use App\Models\AnalyticsSession;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Services\AnalyticsTrackerService;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function index(Request $request)
    {
        $user = Auth::user();
        $workspace = $user->workspaces()->first();
        
        if (!$workspace) {
            return redirect()->route('dashboard');
        }

        $landings = $workspace->landings;

        return view('analytics.index', compact('landings'));
    }

    public function data(Request $request, AnalyticsService $analyticsService)
    {
        $user = Auth::user();
        $workspace = $user->workspaces()->first();

        if (!$workspace) {
            return response()->json(['error' => 'No workspace found'], 404);
        }

        // Filter by Landing ID (optional)
        $landingId = $request->input('landing_id');
        if ($landingId) {
            // Verify ownership
            $landing = $workspace->landings()->find($landingId);
            if (!$landing) {
                return response()->json(['error' => 'Unauthorized or invalid landing'], 403);
            }
            $landingIds = [$landingId];
        } else {
            $landingIds = $workspace->landings()->pluck('id')->toArray();
        }

        // Date Range
        $range = $request->input('range', '30d');
        [$start, $end] = $this->getDateRange($range, $request->input('start'), $request->input('end'));

        // Fetch Data via Service
        $aggregateStats = $analyticsService->getAggregateStats($landingIds, $start, $end);
        $timeSeries = $analyticsService->getTimeSeries($landingIds, $start, $end);
        $breakdowns = $analyticsService->getBreakdowns($landingIds, $start, $end);
        $funnel = $analyticsService->getFunnel($landingIds, $start, $end);
        $landingPerformance = $analyticsService->getLandingPerformance($landingIds, $start, $end);
        $clicksBreakdown = $analyticsService->getClickBreakdown($landingIds, $start, $end);

        return response()->json([
            'kpi' => $aggregateStats,
            'charts' => $timeSeries,
            'breakdowns' => $breakdowns,
            'funnel' => $funnel,
            'landing_performance' => $landingPerformance,
            'clicks_breakdown' => $clicksBreakdown,
        ]);
    }

    public function realtime(Request $request)
    {
        $user = Auth::user();
        $workspace = $user->workspaces()->first();

        if (!$workspace) {
            return response()->json(['error' => 'No workspace found'], 404);
        }

        $landingId = $request->input('landing_id');
        if ($landingId) {
            $landing = $workspace->landings()->find($landingId);
            if (!$landing) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            $landingIds = [$landingId];
        } else {
            $landingIds = $workspace->landings()->pluck('id')->toArray();
        }

        // Active in last 30 mins
        $activeQuery = AnalyticsSession::active(30)->whereIn('landing_id', $landingIds);

        $totalActive = (clone $activeQuery)->count();

        // Top Countries
        $countries = (clone $activeQuery)
            ->selectRaw('COALESCE(country, "Unknown") as country, count(*) as count')
            ->groupBy('country')
            ->orderByDesc('count')
            ->get();

        // Activity per minute (last 30 minutes)
        // Group by minute of last_activity_at
        $now = now();
        $start = $now->copy()->subMinutes(30);

        // Standardize time grouping based on database dialect (assuming MySQL)
        // Extract minute and hour to group them
        $minuteDataRaw = (clone $activeQuery)
            ->selectRaw('DATE_FORMAT(last_activity_at, "%Y-%m-%d %H:%i:00") as minute, count(*) as count')
            ->groupBy('minute')
            ->orderBy('minute')
            ->get()
            ->keyBy('minute');

        // Fill in empty minutes
        $minutes = [];
        for ($i = 0; $i < 30; $i++) {
            $minKey = $start->copy()->addMinutes($i)->format('Y-m-d H:i:00');
            $minutes[] = [
                'time' => $minKey,
                'count' => isset($minuteDataRaw[$minKey]) ? $minuteDataRaw[$minKey]->count : 0
            ];
        }

        return response()->json([
            'total' => $totalActive,
            'countries' => $countries,
            'minutes' => $minutes,
        ]);
    }

    /**
     * Dedicated CTA click tracking endpoint.
     */
    public function trackClick(Request $request, AnalyticsTrackerService $tracker)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:100',
            'page_url' => 'nullable|url',
        ]);

        $sessionId = $request->cookie('lb_session_id');

        if (!$sessionId) {
            return response()->json(['status' => 'ignored', 'reason' => 'no_session'], 200);
        }

        $tracker->logEvent(
            $sessionId,
            'cta_click',
            [
                'text' => $validated['label'],
                'type' => $validated['type'] ?? 'button',
                'position' => $validated['position'] ?? 'unknown',
            ],
            $validated['page_url'] ? parse_url($validated['page_url'], PHP_URL_PATH) : null,
            $validated['label'],
            $validated['type'] ?? 'button',
            $validated['position'] ?? 'unknown'
        );

        return response()->json(['status' => 'ok']);
    }

    /**
     * CTA clicks breakdown API endpoint.
     */
    public function clicksBreakdown(Request $request, AnalyticsService $analyticsService)
    {
        $user = Auth::user();
        $workspace = $user->workspaces()->first();

        if (!$workspace) {
            return response()->json(['error' => 'No workspace found'], 404);
        }

        $landingId = $request->input('landing_id');
        if ($landingId) {
            $landing = $workspace->landings()->find($landingId);
            if (!$landing) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            $landingIds = [$landingId];
        } else {
            $landingIds = $workspace->landings()->pluck('id')->toArray();
        }

        $range = $request->input('range', '30d');
        [$start, $end] = $this->getDateRange($range, $request->input('start'), $request->input('end'));

        return response()->json($analyticsService->getClickBreakdown($landingIds, $start, $end));
    }

    private function getDateRange($range, $customStart = null, $customEnd = null)
    {
        $end = now()->endOfDay();
        $start = now()->subDays(29)->startOfDay();

        switch ($range) {
            case 'today':
                $start = now()->startOfDay();
                break;
            case '7d':
                $start = now()->subDays(6)->startOfDay();
                break;
            case '30d':
                $start = now()->subDays(29)->startOfDay();
                break;
            case 'this_month':
                $start = now()->startOfMonth();
                break;
            case 'last_month':
                $start = now()->subMonth()->startOfMonth();
                $end = now()->subMonth()->endOfMonth();
                break;
            case 'custom':
                if ($customStart && $customEnd) {
                    $start = Carbon::parse($customStart)->startOfDay();
                    $end = Carbon::parse($customEnd)->endOfDay();
                }
                break;
        }
        return [$start, $end];
    }
}

