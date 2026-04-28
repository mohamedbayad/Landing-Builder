<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AnalyticsService;
use App\Models\Landing;
use App\Models\AnalyticsSession;
use App\Models\PageVisit;
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

        // Previous period (same length) for real KPI deltas
        $periodDays = max($start->diffInDays($end) + 1, 1);
        $prevStart = $start->copy()->subDays($periodDays);
        $prevEnd = $end->copy()->subDays($periodDays);

        // Fetch Data via Service
        $aggregateStats = $analyticsService->getAggregateStats($landingIds, $start, $end);
        $previousStats = $analyticsService->getAggregateStats($landingIds, $prevStart, $prevEnd);
        $timeSeries = $analyticsService->getTimeSeries($landingIds, $start, $end);
        $breakdowns = $analyticsService->getBreakdowns($landingIds, $start, $end);
        $funnel = $analyticsService->getFunnel($landingIds, $start, $end);
        $landingPerformance = $analyticsService->getLandingPerformance($landingIds, $start, $end);
        $clicksBreakdown = $analyticsService->getClickBreakdown($landingIds, $start, $end);

        $aggregateStats['sessions_change'] = $this->calculatePercentChange($previousStats['sessions'] ?? 0, $aggregateStats['sessions'] ?? 0);
        $aggregateStats['uniques_change'] = $this->calculatePercentChange($previousStats['uniques'] ?? 0, $aggregateStats['uniques'] ?? 0);
        $aggregateStats['leads_change'] = $this->calculatePercentChange($previousStats['leads'] ?? 0, $aggregateStats['leads'] ?? 0);
        $aggregateStats['conversion_change'] = round(($aggregateStats['conversion_rate'] ?? 0) - ($previousStats['conversion_rate'] ?? 0), 2);
        $aggregateStats['period_days'] = $periodDays;

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

        $windowStart = now()->subMinutes(30);

        // Primary source: analytics sessions active in last 30 mins
        $activeSessions = AnalyticsSession::active(30)
            ->whereIn('landing_id', $landingIds)
            ->get(['country', 'last_activity_at']);

        $totalActive = $activeSessions->count();

        // Top countries + minute activity from active sessions
        $countries = collect();

        $startMinute = now()->subMinutes(29)->startOfMinute();
        $minutesMap = [];

        for ($i = 0; $i < 30; $i++) {
            $minuteKey = $startMinute->copy()->addMinutes($i)->format('Y-m-d H:i:00');
            $minutesMap[$minuteKey] = 0;
        }

        if ($totalActive > 0) {
            $countries = $activeSessions
                ->groupBy(function ($session) {
                    $country = trim((string) $session->country);
                    return $country !== '' ? $country : 'Unknown';
                })
                ->map(fn ($sessions, $country) => [
                    'country' => $country,
                    'count' => $sessions->count(),
                ])
                ->sortByDesc('count')
                ->values();

            foreach ($activeSessions as $session) {
                if (!$session->last_activity_at) {
                    continue;
                }

                $minuteKey = $session->last_activity_at->copy()->second(0)->format('Y-m-d H:i:00');
                if (array_key_exists($minuteKey, $minutesMap)) {
                    $minutesMap[$minuteKey]++;
                }
            }
        } else {
            // Fallback source: legacy page visits in the same rolling window.
            // This keeps realtime widgets useful on projects still relying on page_visits.
            $recentVisits = PageVisit::whereIn('landing_id', $landingIds)
                ->where('created_at', '>=', $windowStart)
                ->get(['id', 'ip_hash', 'country', 'created_at']);

            $visitorKeys = $recentVisits
                ->map(fn ($visit) => $visit->ip_hash ?: ('visit_' . $visit->id))
                ->filter()
                ->unique();
            $totalActive = $visitorKeys->count();

            $countries = $recentVisits
                ->groupBy(function ($visit) {
                    $country = trim((string) $visit->country);
                    return $country !== '' ? $country : 'Unknown';
                })
                ->map(function ($visits, $country) {
                    $countryVisitors = $visits
                        ->map(fn ($visit) => $visit->ip_hash ?: ('visit_' . $visit->id))
                        ->filter()
                        ->unique()
                        ->count();

                    return [
                        'country' => $country,
                        'count' => $countryVisitors,
                    ];
                })
                ->filter(fn ($entry) => ($entry['count'] ?? 0) > 0)
                ->sortByDesc('count')
                ->values();

            foreach ($recentVisits as $visit) {
                if (!$visit->created_at) {
                    continue;
                }

                $minuteKey = $visit->created_at->copy()->second(0)->format('Y-m-d H:i:00');
                if (array_key_exists($minuteKey, $minutesMap)) {
                    $minutesMap[$minuteKey]++;
                }
            }
        }

        $minutes = collect($minutesMap)
            ->map(fn ($count, $time) => ['time' => $time, 'count' => $count])
            ->values();

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

    private function calculatePercentChange($old, $new)
    {
        if ((float) $old === 0.0) {
            return (float) $new > 0 ? 100.0 : 0.0;
        }

        return round((($new - $old) / $old) * 100, 1);
    }
}

