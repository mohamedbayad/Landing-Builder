<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AnalyticsService;
use App\Models\Landing;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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

        return response()->json([
            'kpi' => $aggregateStats,
            'charts' => $timeSeries,
            'breakdowns' => $breakdowns,
            'funnel' => $funnel,
            'landing_performance' => $landingPerformance,
        ]);
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
