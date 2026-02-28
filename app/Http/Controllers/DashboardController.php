<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Lead;
use App\Models\Form;
use App\Models\LandingPage;
use App\Models\AnalyticsEvent; // For checkout events if needed
use App\Models\AnalyticsSession;
use App\Models\User;
use App\Services\AnalyticsService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $analytics;

    public function __construct(AnalyticsService $analytics)
    {
        $this->analytics = $analytics;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $workspace = $user->workspaces()->first();

        if (!$workspace) {
            return view('dashboard', $this->getEmptyDashboardData());
        }

        $allLandingIds = $workspace->landings()->pluck('id')->toArray();

        // ===== FILTERING =====
        $visitsLandingId = $request->input('visits_landing_id');
        $visitsRange = $request->input('visits_range', '7d');
        $visitsStartCustom = $request->input('visits_start');
        $visitsEndCustom = $request->input('visits_end');

        // Landing Filter (If specific landing selected, use that; otherwise all)
        $chartLandingIds = $visitsLandingId ? [$visitsLandingId] : $allLandingIds;

        // Date Range
        [$chartStart, $chartEnd] = $this->getDateRange($visitsRange, $visitsStartCustom, $visitsEndCustom);

        // ===== CURRENT PERIOD STATS (Filtered by Range & Landing) =====
        $currentStats = $this->analytics->getAggregateStats($chartLandingIds, $chartStart, $chartEnd);
        
        $totalVisits = $currentStats['sessions'];
        $totalLeads = $currentStats['leads'];
        $conversionRate = $currentStats['conversion_rate'];

        // Total Pages (Static count)
        $totalPages = LandingPage::whereIn('landing_id', $allLandingIds)->count();
            
        // Checkouts (Approximate via event or path)
        // Using AnalyticsEvent for robust tracking if available, or just path match on events
        $checkoutVisits = AnalyticsEvent::whereIn('landing_id', $chartLandingIds)
            ->whereBetween('created_at', [$chartStart, $chartEnd])
            ->where('event_name', 'pageview')
            ->where('url_path', 'like', '%checkout%')
            ->distinct('session_id') // Count unique sessions that hit checkout
            ->count('session_id');

        // ===== PERCENTAGE CHANGES (vs Prev Period of SAME length) =====
        // Calculate previous period
        $daysDiff = $chartStart->diffInDays($chartEnd) + 1;
        $prevStart = $chartStart->copy()->subDays($daysDiff);
        $prevEnd = $chartEnd->copy()->subDays($daysDiff);

        $prevStats = $this->analytics->getAggregateStats($chartLandingIds, $prevStart, $prevEnd);
        
        $prevCheckouts = AnalyticsEvent::whereIn('landing_id', $chartLandingIds)
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->where('event_name', 'pageview')
            ->where('url_path', 'like', '%checkout%')
            ->distinct('session_id')
            ->count('session_id');

        $visitsChange = $this->calculatePercentChange($prevStats['sessions'], $totalVisits);
        $leadsChange = $this->calculatePercentChange($prevStats['leads'], $totalLeads);
        $checkoutsChange = $this->calculatePercentChange($prevCheckouts, $checkoutVisits);

        // ===== TRAFFIC SOURCES & DEVICES (Via Service) =====
        $breakdowns = $this->analytics->getBreakdowns($chartLandingIds, $chartStart, $chartEnd);
        $trafficSources = $breakdowns['sources_pct'];
        $deviceDistribution = $breakdowns['devices'];

        // ===== CHART DATA (Via Service) =====
        $timeSeries = $this->analytics->getTimeSeries($chartLandingIds, $chartStart, $chartEnd);
        $chartLabels = $timeSeries['labels'];
        $visitsData = $timeSeries['sessions'];
        $leadsData = $timeSeries['leads'];

        // ===== TOP PERFORMING LANDINGS =====
        $topLandingsRaw = $this->analytics->getLandingPerformance($chartLandingIds, $chartStart, $chartEnd);
        // Sort by sessions desc, take 5, and cast to object for view compatibility
        $topLandings = $topLandingsRaw->sortByDesc('sessions')->take(5)->map(fn($item) => (object) $item);

        // ===== RECENT ACTIVITY (Global for workspace, keeping existing logic) =====
        $recentActivity = $this->getRecentActivity($allLandingIds);

        // ===== RECENT ORDERS & FORMS (Global) =====
        $landings = $workspace->landings;

        $recentOrders = Lead::whereIn('landing_id', $allLandingIds)
            ->latest()
            ->take(5)
            ->with('landing')
            ->get();

        $recentForms = Form::whereIn('landing_id', $allLandingIds)
            ->latest()
            ->take(5)
            ->with('landing')
            ->get();

        // ===== LIVE LANDING PAGE VISITORS =====
        $onlineUsersCount = AnalyticsSession::active()
            ->whereIn('landing_id', $allLandingIds)
            ->count();
            
        $onlineUsers = AnalyticsSession::active()
            ->whereIn('landing_id', $allLandingIds)
            ->selectRaw('COALESCE(country, "Unknown") as country, COALESCE(city, "Unknown") as city, count(*) as count')
            ->groupBy('country', 'city')
            ->orderByDesc('count')
            ->get();

        return view('dashboard', compact(
            'totalVisits', 'totalLeads', 'totalPages', 'checkoutVisits',
            'conversionRate', 'visitsChange', 'leadsChange', 'checkoutsChange',
            'trafficSources', 'deviceDistribution',
            'chartLabels', 'visitsData', 'leadsData',
            'topLandings', 'recentActivity',
            'landings', 'recentOrders', 'recentForms',
            'onlineUsersCount', 'onlineUsers'
        ));
    }

    public function getOnlineUsers()
    {
        $user = Auth::user();
        $workspace = $user->workspaces()->first();

        if (!$workspace) {
            return response()->json(['count' => 0, 'locations' => []]);
        }

        $allLandingIds = $workspace->landings()->pluck('id')->toArray();

        $onlineUsersCount = AnalyticsSession::active()
            ->whereIn('landing_id', $allLandingIds)
            ->count();
            
        $onlineUsers = AnalyticsSession::active()
            ->whereIn('landing_id', $allLandingIds)
            ->selectRaw('COALESCE(country, "Unknown") as country, COALESCE(city, "Unknown") as city, count(*) as count')
            ->groupBy('country', 'city')
            ->orderByDesc('count')
            ->get();

        return response()->json([
            'count' => $onlineUsersCount,
            'locations' => $onlineUsers
        ]);
    }

    private function getEmptyDashboardData()
    {
        return [
            'totalVisits' => 0, 'totalLeads' => 0, 'totalPages' => 0, 'checkoutVisits' => 0,
            'conversionRate' => 0, 'visitsChange' => 0, 'leadsChange' => 0, 'checkoutsChange' => 0,
            'trafficSources' => ['Direct' => 0, 'Social' => 0, 'Search' => 0, 'Referral' => 0],
            'deviceDistribution' => ['mobile' => 0, 'desktop' => 0],
            'chartLabels' => [], 'visitsData' => [], 'leadsData' => [],
            'topLandings' => collect(), 'recentActivity' => collect(),
            'landings' => collect(), 'recentOrders' => collect(), 'recentForms' => collect(),
            'onlineUsersCount' => 0, 'onlineUsers' => collect(),
        ];
    }

    private function calculatePercentChange($old, $new)
    {
        if ($old == 0) {
            return $new > 0 ? 100 : 0;
        }
        return round((($new - $old) / $old) * 100, 1);
    }

    private function getRecentActivity($landingIds)
    {
        $leads = Lead::whereIn('landing_id', $landingIds)
            ->with('landing')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($l) => [
                'type' => 'lead',
                'message' => "New Lead on '{$l->landing->name}'",
                'time' => $l->created_at,
                'time_ago' => $l->created_at->diffForHumans(),
            ]);

        $forms = Form::whereIn('landing_id', $landingIds)
            ->with('landing')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($f) => [
                'type' => 'form',
                'message' => "Form submitted on '{$f->landing->name}'",
                'time' => $f->created_at,
                'time_ago' => $f->created_at->diffForHumans(),
            ]);

        return $leads->concat($forms)
            ->sortByDesc('time')
            ->take(5)
            ->values();
    }

    private function getDateRange($range, $customStart = null, $customEnd = null)
    {
        $end = now()->endOfDay();
        $start = now()->subDays(6)->startOfDay();

        switch ($range) {
            case 'today':
                $start = now()->startOfDay();
                $end = now()->endOfDay();
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
            case '7d':
            default:
                $start = now()->subDays(6)->startOfDay();
                break;
        }
        return [$start, $end];
    }
}
