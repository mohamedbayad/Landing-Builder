<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TrackingEvent;
use App\Models\Lead;
use App\Models\Form;
use App\Models\Landing;
use App\Models\LandingPage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $workspace = $user->workspaces()->first();

        if (!$workspace) {
            return view('dashboard', $this->getEmptyDashboardData());
        }

        $allLandingIds = $workspace->landings()->pluck('id');

        // ===== CURRENT PERIOD STATS =====
        $totalVisits = TrackingEvent::whereIn('landing_id', $allLandingIds)
            ->where('type', 'page_view')
            ->count();

        $totalLeads = Lead::whereIn('landing_id', $allLandingIds)->count();
        $totalPages = LandingPage::whereIn('landing_id', $allLandingIds)->count();

        $checkoutVisits = TrackingEvent::whereIn('landing_id', $allLandingIds)
            ->where('type', 'page_view')
            ->whereHas('page', fn($q) => $q->where('type', 'checkout'))
            ->count();

        // ===== CONVERSION RATE =====
        $conversionRate = $totalVisits > 0 ? round(($totalLeads / $totalVisits) * 100, 1) : 0;

        // ===== PERCENTAGE CHANGES (vs Last Month) =====
        $thisMonthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        // This month counts
        $visitsThisMonth = TrackingEvent::whereIn('landing_id', $allLandingIds)
            ->where('type', 'page_view')
            ->where('created_at', '>=', $thisMonthStart)
            ->count();
        $leadsThisMonth = Lead::whereIn('landing_id', $allLandingIds)
            ->where('created_at', '>=', $thisMonthStart)
            ->count();
        $checkoutsThisMonth = TrackingEvent::whereIn('landing_id', $allLandingIds)
            ->where('type', 'page_view')
            ->whereHas('page', fn($q) => $q->where('type', 'checkout'))
            ->where('created_at', '>=', $thisMonthStart)
            ->count();

        // Last month counts
        $visitsLastMonth = TrackingEvent::whereIn('landing_id', $allLandingIds)
            ->where('type', 'page_view')
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->count();
        $leadsLastMonth = Lead::whereIn('landing_id', $allLandingIds)
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->count();
        $checkoutsLastMonth = TrackingEvent::whereIn('landing_id', $allLandingIds)
            ->where('type', 'page_view')
            ->whereHas('page', fn($q) => $q->where('type', 'checkout'))
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->count();

        $visitsChange = $this->calculatePercentChange($visitsLastMonth, $visitsThisMonth);
        $leadsChange = $this->calculatePercentChange($leadsLastMonth, $leadsThisMonth);
        $checkoutsChange = $this->calculatePercentChange($checkoutsLastMonth, $checkoutsThisMonth);

        // ===== TRAFFIC SOURCES (from utm_source or referrer) =====
        $trafficSources = $this->getTrafficSources($allLandingIds);

        // ===== DEVICE DISTRIBUTION =====
        $deviceDistribution = $this->getDeviceDistribution($allLandingIds);

        // ===== CHART DATA (Combined for multi-line) =====
        $visitsLandingId = $request->input('visits_landing_id');
        $visitsRange = $request->input('visits_range', '7d');
        $visitsStartCustom = $request->input('visits_start');
        $visitsEndCustom = $request->input('visits_end');
        
        $chartLandingIds = $visitsLandingId ? [$visitsLandingId] : $allLandingIds;
        [$chartStart, $chartEnd] = $this->getDateRange($visitsRange, $visitsStartCustom, $visitsEndCustom);

        // Visits data
        $visitsQuery = TrackingEvent::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->whereIn('landing_id', $chartLandingIds)
            ->where('type', 'page_view')
            ->whereBetween('created_at', [$chartStart, $chartEnd])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');

        // Leads data (for same period)
        $leadsQuery = Lead::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->whereIn('landing_id', $chartLandingIds)
            ->whereBetween('created_at', [$chartStart, $chartEnd])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');

        [$chartLabels, $visitsData] = $this->prepareChartData($visitsQuery, $chartStart, $chartEnd);
        [, $leadsData] = $this->prepareChartData($leadsQuery, $chartStart, $chartEnd);

        // ===== TOP PERFORMING LANDINGS =====
        $topLandings = $this->getTopLandings($allLandingIds);

        // ===== RECENT ACTIVITY =====
        $recentActivity = $this->getRecentActivity($allLandingIds);

        // ===== RECENT ORDERS & FORMS (existing) =====
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

        return view('dashboard', compact(
            'totalVisits', 'totalLeads', 'totalPages', 'checkoutVisits',
            'conversionRate', 'visitsChange', 'leadsChange', 'checkoutsChange',
            'trafficSources', 'deviceDistribution',
            'chartLabels', 'visitsData', 'leadsData',
            'topLandings', 'recentActivity',
            'landings', 'recentOrders', 'recentForms'
        ));
    }

    private function getEmptyDashboardData()
    {
        return [
            'totalVisits' => 0, 'totalLeads' => 0, 'totalPages' => 0, 'checkoutVisits' => 0,
            'conversionRate' => 0, 'visitsChange' => 0, 'leadsChange' => 0, 'checkoutsChange' => 0,
            'trafficSources' => ['Direct' => 100, 'Social' => 0, 'Search' => 0, 'Referral' => 0],
            'deviceDistribution' => ['mobile' => 0, 'desktop' => 100],
            'chartLabels' => [], 'visitsData' => [], 'leadsData' => [],
            'topLandings' => collect(), 'recentActivity' => collect(),
            'landings' => collect(), 'recentOrders' => collect(), 'recentForms' => collect(),
        ];
    }

    private function calculatePercentChange($old, $new)
    {
        if ($old == 0) {
            return $new > 0 ? 100 : 0;
        }
        return round((($new - $old) / $old) * 100, 1);
    }

    private function getTrafficSources($landingIds)
    {
        $events = TrackingEvent::whereIn('landing_id', $landingIds)
            ->where('type', 'page_view')
            ->select('referrer', 'utm_source')
            ->get();

        $sources = ['Direct' => 0, 'Social' => 0, 'Search' => 0, 'Referral' => 0];
        $total = $events->count();

        if ($total === 0) {
            return ['Direct' => 100, 'Social' => 0, 'Search' => 0, 'Referral' => 0];
        }

        foreach ($events as $event) {
            $source = $this->categorizeSource($event->utm_source, $event->referrer);
            $sources[$source]++;
        }

        // Convert to percentages
        foreach ($sources as $key => $count) {
            $sources[$key] = round(($count / $total) * 100, 1);
        }

        return $sources;
    }

    private function categorizeSource($utmSource, $referrer)
    {
        if ($utmSource) {
            $utm = strtolower($utmSource);
            if (in_array($utm, ['facebook', 'instagram', 'twitter', 'linkedin', 'tiktok', 'social'])) {
                return 'Social';
            }
            if (in_array($utm, ['google', 'bing', 'yahoo', 'search', 'seo'])) {
                return 'Search';
            }
            return 'Referral';
        }

        if (empty($referrer)) {
            return 'Direct';
        }

        $ref = strtolower($referrer);
        if (str_contains($ref, 'google.') || str_contains($ref, 'bing.') || str_contains($ref, 'yahoo.')) {
            return 'Search';
        }
        if (str_contains($ref, 'facebook.') || str_contains($ref, 'instagram.') || str_contains($ref, 't.co') || str_contains($ref, 'linkedin.')) {
            return 'Social';
        }

        return 'Referral';
    }

    private function getDeviceDistribution($landingIds)
    {
        $events = TrackingEvent::whereIn('landing_id', $landingIds)
            ->where('type', 'page_view')
            ->whereNotNull('user_agent')
            ->pluck('user_agent');

        $mobile = 0;
        $desktop = 0;

        foreach ($events as $ua) {
            if ($this->isMobile($ua)) {
                $mobile++;
            } else {
                $desktop++;
            }
        }

        $total = $mobile + $desktop;
        if ($total === 0) {
            return ['mobile' => 0, 'desktop' => 100];
        }

        return [
            'mobile' => round(($mobile / $total) * 100, 1),
            'desktop' => round(($desktop / $total) * 100, 1),
        ];
    }

    private function isMobile($userAgent)
    {
        $mobileKeywords = ['mobile', 'android', 'iphone', 'ipad', 'ipod', 'blackberry', 'windows phone'];
        $ua = strtolower($userAgent ?? '');
        foreach ($mobileKeywords as $keyword) {
            if (str_contains($ua, $keyword)) {
                return true;
            }
        }
        return false;
    }

    private function getTopLandings($landingIds)
    {
        return Landing::whereIn('id', $landingIds)
            ->withCount([
                'trackingEvents as visits_count' => fn($q) => $q->where('type', 'page_view'),
                'leads as leads_count'
            ])
            ->orderByDesc('visits_count')
            ->take(5)
            ->get()
            ->map(function ($landing) {
                $landing->conversion_rate = $landing->visits_count > 0 
                    ? round(($landing->leads_count / $landing->visits_count) * 100, 1) 
                    : 0;
                return $landing;
            });
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
        $end = now();
        $start = now()->subDays(6);

        switch ($range) {
            case 'today':
                $start = now()->startOfDay();
                $end = now()->endOfDay();
                break;
            case '30d':
                $start = now()->subDays(29);
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
                $start = now()->subDays(6);
                break;
        }
        return [$start, $end];
    }

    private function prepareChartData($data, $start, $end)
    {
        $labels = [];
        $values = [];
        $period = \Carbon\CarbonPeriod::create($start, $end);

        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $labels[] = $date->format('M d');
            $values[] = $data[$formattedDate] ?? 0;
        }
        return [$labels, $values];
    }
}
