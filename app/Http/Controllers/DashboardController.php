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
use App\Models\Subscription;
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
        $isSuperAdmin = $user?->hasRole('super-admin') ?? false;
        $superAdminStats = $isSuperAdmin ? $this->getSuperAdminStats() : null;
        $workspace = $user->workspaces()->first();

        if (!$workspace) {
            $empty = $this->getEmptyDashboardData();
            $empty['isSuperAdmin'] = $isSuperAdmin;
            $empty['superAdminStats'] = $superAdminStats;
            return view('dashboard', $empty);
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
        $trafficSourcesRaw = $breakdowns['sources_pct'];
        // Dashboard shows a simplified 4-source donut where Paid + Social are combined as "Paid Ads".
        $trafficSources = [
            'Direct' => $trafficSourcesRaw['Direct'] ?? 0,
            'Social' => round(($trafficSourcesRaw['Social'] ?? 0) + ($trafficSourcesRaw['Paid'] ?? 0), 1),
            'Search' => $trafficSourcesRaw['Search'] ?? 0,
            'Referral' => $trafficSourcesRaw['Referral'] ?? 0,
        ];
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
            'onlineUsersCount', 'onlineUsers',
            'isSuperAdmin', 'superAdminStats'
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
            'isSuperAdmin' => false, 'superAdminStats' => null,
        ];
    }

    private function getSuperAdminStats(): array
    {
        $activeStatuses = ['active', 'trial'];

        $activeSubscriptions = Subscription::query()
            ->whereIn('status', $activeStatuses)
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->with('plan:id,name,monthly_price,yearly_price')
            ->get();

        $allSubscriptions = Subscription::query()
            ->with('plan:id,name,monthly_price,yearly_price')
            ->get();

        $mrr = round((float) $activeSubscriptions->sum(function (Subscription $subscription) {
            if (!$subscription->plan) {
                return 0;
            }

            return match ($subscription->billing_cycle) {
                'monthly' => (float) $subscription->plan->monthly_price,
                'yearly' => ((float) $subscription->plan->yearly_price) / 12,
                default => 0,
            };
        }), 2);

        $arr = round((float) $activeSubscriptions->sum(function (Subscription $subscription) {
            if (!$subscription->plan) {
                return 0;
            }

            return match ($subscription->billing_cycle) {
                'monthly' => ((float) $subscription->plan->monthly_price) * 12,
                'yearly' => (float) $subscription->plan->yearly_price,
                default => 0,
            };
        }), 2);

        $bookedRevenue = round((float) $allSubscriptions->sum(function (Subscription $subscription) {
            if (!$subscription->plan) {
                return 0;
            }

            return match ($subscription->billing_cycle) {
                'monthly' => (float) $subscription->plan->monthly_price,
                'yearly' => (float) $subscription->plan->yearly_price,
                default => 0,
            };
        }), 2);

        $totalUsers = User::query()->count();
        $totalSubscribers = User::query()
            ->whereHas('roles', fn ($query) => $query->where('slug', 'subscriber'))
            ->count();

        $activeSubscribers = User::query()
            ->whereHas('subscriptions', function ($query) use ($activeStatuses) {
                $query->whereIn('status', $activeStatuses)
                    ->where(function ($q) {
                        $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
                    });
            })
            ->count();

        $trialSubscriptions = Subscription::query()->where('status', 'trial')->count();
        $churnedSubscriptions = Subscription::query()->whereIn('status', ['expired', 'canceled', 'paused'])->count();

        $activeByPlan = $activeSubscriptions
            ->groupBy(fn (Subscription $subscription) => $subscription->plan?->name ?? 'Unknown plan')
            ->map(fn ($items, $planName) => [
                'plan' => $planName,
                'count' => $items->count(),
            ])
            ->sortByDesc('count')
            ->values()
            ->take(6);

        $latestSubscribers = User::query()
            ->whereHas('roles', fn ($query) => $query->where('slug', 'subscriber'))
            ->with([
                'subscriptions' => function ($query) {
                    $query->latest('starts_at')
                        ->latest('id')
                        ->with('plan:id,name');
                },
            ])
            ->latest('id')
            ->take(6)
            ->get(['id', 'name', 'email', 'created_at'])
            ->map(function (User $subscriber) {
                /** @var \App\Models\Subscription|null $latestSub */
                $latestSub = $subscriber->subscriptions->first();
                return [
                    'id' => $subscriber->id,
                    'name' => $subscriber->name,
                    'email' => $subscriber->email,
                    'created_at' => $subscriber->created_at,
                    'plan' => $latestSub?->plan?->name ?? 'No plan',
                    'status' => $latestSub?->status ?? 'none',
                ];
            });

        return [
            'kpis' => [
                'total_users' => $totalUsers,
                'total_subscribers' => $totalSubscribers,
                'active_subscribers' => $activeSubscribers,
                'active_subscriptions' => $activeSubscriptions->count(),
                'trial_subscriptions' => $trialSubscriptions,
                'churned_subscriptions' => $churnedSubscriptions,
                'mrr' => $mrr,
                'arr' => $arr,
                'booked_revenue' => $bookedRevenue,
            ],
            'active_by_plan' => $activeByPlan,
            'latest_subscribers' => $latestSubscribers,
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
