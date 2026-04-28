<?php

namespace App\Services;

use App\Models\AnalyticsSession;
use App\Models\AnalyticsEvent;
use App\Models\Lead;
use App\Models\PageVisit;
use Illuminate\Support\Facades\DB;
use Carbon\CarbonPeriod;

class AnalyticsService
{
    public function getAggregateStats($landingIds, $start, $end)
    {
        $useLegacyVisits = $this->shouldUseLegacyVisits($landingIds, $start, $end);

        if ($useLegacyVisits) {
            $totalSessions = PageVisit::whereIn('landing_id', $landingIds)
                ->whereBetween('created_at', [$start, $end])
                ->count();

            $uniqueVisitors = PageVisit::whereIn('landing_id', $landingIds)
                ->whereBetween('created_at', [$start, $end])
                ->whereNotNull('ip_hash')
                ->distinct('ip_hash')
                ->count('ip_hash');
        } else {
            $totalSessions = AnalyticsSession::whereIn('landing_id', $landingIds)
                ->whereBetween('started_at', [$start, $end])
                ->count();

            $uniqueVisitors = AnalyticsSession::whereIn('landing_id', $landingIds)
                ->whereBetween('started_at', [$start, $end])
                ->distinct('visitor_id')
                ->count('visitor_id');
        }

        $totalLeads = Lead::whereIn('landing_id', $landingIds)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $conversionRate = $totalSessions > 0 
            ? round(($totalLeads / $totalSessions) * 100, 2) 
            : 0;

        $bounces = 0;
        $avgDuration = 0;
        if (!$useLegacyVisits) {
            $bounces = AnalyticsSession::whereIn('landing_id', $landingIds)
                ->whereBetween('started_at', [$start, $end])
                ->where('is_bounce', true)
                ->count();

            $avgDuration = AnalyticsSession::whereIn('landing_id', $landingIds)
                ->whereBetween('started_at', [$start, $end])
                ->avg('duration_seconds');
        }

        $bounceRate = $totalSessions > 0
            ? round(($bounces / $totalSessions) * 100, 1)
            : 0;
            
        return [
            'has_data' => $totalSessions > 0 || $totalLeads > 0,
            'sessions' => $totalSessions,
            'uniques' => $uniqueVisitors,
            'leads' => $totalLeads,
            'conversion_rate' => $conversionRate,
            'bounce_rate' => $bounceRate,
            'avg_duration' => round($avgDuration ?? 0), // seconds
        ];
    }

    public function getTimeSeries($landingIds, $start, $end)
    {
        $useLegacyVisits = $this->shouldUseLegacyVisits($landingIds, $start, $end);

        if ($useLegacyVisits) {
            $sessionsData = PageVisit::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->whereIn('landing_id', $landingIds)
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->pluck('count', 'date');
        } else {
            $sessionsData = AnalyticsSession::select(DB::raw('DATE(started_at) as date'), DB::raw('count(*) as count'))
                ->whereIn('landing_id', $landingIds)
                ->whereBetween('started_at', [$start, $end])
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->pluck('count', 'date');
        }

        $leadsData = Lead::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->whereIn('landing_id', $landingIds)
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');

        $period = CarbonPeriod::create($start, $end);
        $labels = [];
        $sessions = [];
        $leads = [];

        foreach ($period as $date) {
            $formatted = $date->format('Y-m-d');
            $labels[] = $date->format('M d');
            $sessions[] = $sessionsData[$formatted] ?? 0;
            $leads[] = $leadsData[$formatted] ?? 0;
        }

        return [
            'labels' => $labels,
            'sessions' => $sessions,
            'leads' => $leads,
        ];
    }

    public function getBreakdowns($landingIds, $start, $end)
    {
        $useLegacyVisits = $this->shouldUseLegacyVisits($landingIds, $start, $end);

        if ($useLegacyVisits) {
            $sourceStats = PageVisit::whereIn('landing_id', $landingIds)
                ->whereBetween('created_at', [$start, $end])
                ->select('source_type', DB::raw('count(*) as total'))
                ->groupBy('source_type')
                ->pluck('total', 'source_type');
        } else {
            $sourceStats = AnalyticsSession::whereIn('landing_id', $landingIds)
                ->whereBetween('started_at', [$start, $end])
                ->select('source_type', DB::raw('count(*) as total'))
                ->groupBy('source_type')
                ->pluck('total', 'source_type');
        }

        $totalSources = $sourceStats->sum();
        $sources = ['Direct' => 0, 'Social' => 0, 'Search' => 0, 'Referral' => 0, 'Paid' => 0, 'Email' => 0];

        foreach ($sourceStats as $type => $count) {
            $key = match($type) {
                'direct' => 'Direct',
                'social' => 'Social',
                'search' => 'Search',
                'paid' => 'Paid',
                'email' => 'Email',
                default => 'Referral'
            };
            $sources[$key] += $count;
        }

        $sourcesPct = [];
        foreach ($sources as $k => $v) {
            $sourcesPct[$k] = $totalSources > 0 ? round(($v / $totalSources) * 100, 1) : 0;
        }

        if ($useLegacyVisits) {
            $deviceStats = PageVisit::whereIn('landing_id', $landingIds)
                ->whereBetween('created_at', [$start, $end])
                ->select('device_type', DB::raw('count(*) as total'))
                ->groupBy('device_type')
                ->pluck('total', 'device_type');
        } else {
            $deviceStats = AnalyticsSession::whereIn('landing_id', $landingIds)
                ->whereBetween('started_at', [$start, $end])
                ->select('device_type', DB::raw('count(*) as total'))
                ->groupBy('device_type')
                ->pluck('total', 'device_type');
        }

        $devices = [
            'mobile' => 0,
            'desktop' => 0,
            'tablet' => 0
        ];
        
        $totalDevices = 0;
        foreach ($deviceStats as $type => $count) {
            $devices[$type] = $count;
            $totalDevices += $count;
        }

        $devicesPct = [
             'mobile' => $totalDevices > 0 ? round(((($devices['mobile'] ?? 0) + ($devices['tablet'] ?? 0)) / $totalDevices) * 100, 1) : 0,
             'desktop' => $totalDevices > 0 ? round((($devices['desktop'] ?? 0) / $totalDevices) * 100, 1) : 0,
             'tablet' => $totalDevices > 0 ? round((($devices['tablet'] ?? 0) / $totalDevices) * 100, 1) : 0,
        ];

        if ($useLegacyVisits) {
            $topReferrers = PageVisit::whereIn('landing_id', $landingIds)
                ->whereBetween('created_at', [$start, $end])
                ->whereNotNull('referrer')
                ->select(DB::raw("SUBSTR(referrer, 1, 80) as ref_clean"), DB::raw('count(*) as total'))
                ->groupBy('ref_clean')
                ->orderByDesc('total')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    $host = parse_url($item->ref_clean, PHP_URL_HOST) ?? $item->ref_clean;
                    return ['domain' => $host, 'total' => $item->total];
                });

            $topCampaigns = PageVisit::whereIn('landing_id', $landingIds)
                ->whereBetween('created_at', [$start, $end])
                ->whereNotNull('utm_campaign')
                ->select('utm_campaign', DB::raw('count(*) as total'))
                ->groupBy('utm_campaign')
                ->orderByDesc('total')
                ->limit(10)
                ->get();

            $currentIps = PageVisit::whereIn('landing_id', $landingIds)
                ->whereBetween('created_at', [$start, $end])
                ->whereNotNull('ip_hash')
                ->distinct('ip_hash')
                ->pluck('ip_hash');

            $totalVisitors = $currentIps->count();
            $returningVisitors = 0;

            if ($totalVisitors > 0) {
                $returningVisitors = PageVisit::whereIn('landing_id', $landingIds)
                    ->where('created_at', '<', $start)
                    ->whereIn('ip_hash', $currentIps)
                    ->whereNotNull('ip_hash')
                    ->distinct('ip_hash')
                    ->count('ip_hash');
            }

            $newVisitors = max($totalVisitors - $returningVisitors, 0);
        } else {
            $topReferrers = AnalyticsSession::whereIn('landing_id', $landingIds)
                ->whereBetween('started_at', [$start, $end])
                ->whereNotNull('referrer')
                ->select(DB::raw("SUBSTR(referrer, 1, 80) as ref_clean"), DB::raw('count(*) as total'))
                ->groupBy('ref_clean')
                ->orderByDesc('total')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    $host = parse_url($item->ref_clean, PHP_URL_HOST) ?? $item->ref_clean;
                    return ['domain' => $host, 'total' => $item->total];
                });

            $topCampaigns = AnalyticsSession::whereIn('landing_id', $landingIds)
                ->whereBetween('started_at', [$start, $end])
                ->whereNotNull('utm_campaign')
                ->select('utm_campaign', DB::raw('count(*) as total'))
                ->groupBy('utm_campaign')
                ->orderByDesc('total')
                ->limit(10)
                ->get();

            $newVisitors = AnalyticsSession::whereIn('landing_id', $landingIds)
                ->whereBetween('started_at', [$start, $end])
                ->whereHas('visitor', function ($q) use ($start, $end) {
                    $q->whereBetween('first_seen_at', [$start, $end]);
                })
                ->distinct('visitor_id')
                ->count('visitor_id');

            $totalVisitors = AnalyticsSession::whereIn('landing_id', $landingIds)
                ->whereBetween('started_at', [$start, $end])
                ->distinct('visitor_id')
                ->count('visitor_id');

            $returningVisitors = max($totalVisitors - $newVisitors, 0);
        }
        
        $visitorTypes = [
            'new' => $totalVisitors > 0 ? round(($newVisitors / $totalVisitors) * 100, 1) : 0,
            'returning' => $totalVisitors > 0 ? round(($returningVisitors / $totalVisitors) * 100, 1) : 0,
        ];

        return [
            'sources' => $sources,
            'sources_pct' => $sourcesPct,
            'devices' => $devicesPct,
            'top_referrers' => $topReferrers,
            'top_campaigns' => $topCampaigns,
            'visitor_types' => $visitorTypes,
        ];
    }
    
    public function getFunnel($landingIds, $start, $end)
    {
        $useLegacyVisits = $this->shouldUseLegacyVisits($landingIds, $start, $end);

        $step1 = $useLegacyVisits
            ? PageVisit::whereIn('landing_id', $landingIds)
                ->whereBetween('created_at', [$start, $end])
                ->count()
            : AnalyticsSession::whereIn('landing_id', $landingIds)
                ->whereBetween('started_at', [$start, $end])
                ->count();

        $step2 = AnalyticsEvent::whereIn('landing_id', $landingIds)
            ->whereBetween('created_at', [$start, $end])
            ->where('event_name', 'cta_click')
            ->distinct('session_id')
            ->count('session_id');

        $step3 = AnalyticsEvent::whereIn('landing_id', $landingIds)
            ->whereBetween('created_at', [$start, $end])
            ->where('event_name', 'form_start')
            ->distinct('session_id')
            ->count('session_id');

        $step4 = Lead::whereIn('landing_id', $landingIds)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        return [
            ['label' => 'Sessions', 'value' => $step1, 'step' => 1],
            ['label' => 'CTA Clicks', 'value' => $step2, 'step' => 2],
            ['label' => 'Form Interactions', 'value' => $step3, 'step' => 3],
            ['label' => 'Leads', 'value' => $step4, 'step' => 4],
        ];
    }
    
    public function getLandingPerformance($landingIds, $start, $end)
    {
        $useLegacyVisits = $this->shouldUseLegacyVisits($landingIds, $start, $end);

        if ($useLegacyVisits) {
            $stats = PageVisit::whereIn('landing_id', $landingIds)
                ->whereBetween('created_at', [$start, $end])
                ->select(
                    'landing_id',
                    DB::raw('count(*) as sessions'),
                    DB::raw('count(distinct ip_hash) as uniques'),
                    DB::raw('0 as avg_duration'),
                    DB::raw('0 as bounces')
                )
                ->groupBy('landing_id')
                ->with('landing:id,name,slug,is_main')
                ->get();
        } else {
            $stats = AnalyticsSession::whereIn('landing_id', $landingIds)
                ->whereBetween('started_at', [$start, $end])
                ->select(
                    'landing_id',
                    DB::raw('count(*) as sessions'),
                    DB::raw('count(distinct visitor_id) as uniques'),
                    DB::raw('avg(duration_seconds) as avg_duration'),
                    DB::raw('sum(case when is_bounce = 1 then 1 else 0 end) as bounces')
                )
                ->groupBy('landing_id')
                ->with('landing:id,name,slug,is_main')
                ->get();
        }
            
        $leads = Lead::whereIn('landing_id', $landingIds)
            ->whereBetween('created_at', [$start, $end])
            ->select('landing_id', DB::raw('count(*) as total'))
            ->groupBy('landing_id')
            ->pluck('total', 'landing_id');

        return $stats->map(function($stat) use ($leads) {
            $totalLeads = $leads[$stat->landing_id] ?? 0;
            $sessions = $stat->sessions;
            
            return [
                'id' => $stat->landing->id ?? null,
                'name' => $stat->landing->name ?? 'Unknown',
                'is_main' => $stat->landing->is_main ?? false,
                'sessions' => $sessions,
                'visits_count' => $sessions, // Alias for view compatibility
                'uniques' => $stat->uniques,
                'leads' => $totalLeads,
                'conversion_rate' => $sessions > 0 ? round(($totalLeads / $sessions) * 100, 2) : 0,
                'bounce_rate' => $sessions > 0 ? round(($stat->bounces / $sessions) * 100, 1) : 0,
                'avg_duration' => round($stat->avg_duration),
            ];
        });
    }

    public function getClickBreakdown($landingIds, $start, $end)
    {
        $events = AnalyticsEvent::whereIn('landing_id', $landingIds)
            ->whereBetween('created_at', [$start, $end])
            ->where('event_name', 'cta_click')
            ->get(['element_label', 'event_data']);

        $totalClicks = $events->count();

        $breakdown = $events
            ->map(function ($event) {
                $label = trim((string) ($event->element_label ?: data_get($event->event_data, 'text', 'unknown')));
                return $label !== '' ? $label : 'unknown';
            })
            ->countBy()
            ->sortDesc()
            ->take(20);

        return $breakdown->map(function ($clicks, $label) use ($totalClicks) {
            return [
                'label' => $label,
                'clicks' => $clicks,
                'percentage' => $totalClicks > 0 ? round(($clicks / $totalClicks) * 100, 1) : 0,
            ];
        })->values();
    }

    private function shouldUseLegacyVisits($landingIds, $start, $end): bool
    {
        $sessionExists = AnalyticsSession::whereIn('landing_id', $landingIds)
            ->whereBetween('started_at', [$start, $end])
            ->exists();

        if ($sessionExists) {
            return false;
        }

        return PageVisit::whereIn('landing_id', $landingIds)
            ->whereBetween('created_at', [$start, $end])
            ->exists();
    }
}

