<?php

namespace App\Services;

use App\Models\AnalyticsSession;
use App\Models\AnalyticsVisitor;
use App\Models\AnalyticsEvent;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AnalyticsService
{
    public function getAggregateStats($landingIds, $start, $end)
    {
        // 1. Sessions (was Visits)
        $totalSessions = AnalyticsSession::whereIn('landing_id', $landingIds)
            ->whereBetween('started_at', [$start, $end])
            ->count();

        // 2. Unique Visitors (Count distinct visitor_id in sessions)
        $uniqueVisitors = AnalyticsSession::whereIn('landing_id', $landingIds)
            ->whereBetween('started_at', [$start, $end])
            ->distinct('visitor_id')
            ->count('visitor_id');

        // 3. Total Leads
        // Leads are still in leads table, but we might want to link them to sessions if possible.
        // For now, keep using Lead model but ensuring date range matches.
        $totalLeads = Lead::whereIn('landing_id', $landingIds)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        // 4. Conversion Rate (Leads / Sessions)
        $conversionRate = $totalSessions > 0 
            ? round(($totalLeads / $totalSessions) * 100, 2) 
            : 0;

        // 5. Bounce Rate (Sessions with is_bounce=1 / Total Sessions)
        $bounces = AnalyticsSession::whereIn('landing_id', $landingIds)
            ->whereBetween('started_at', [$start, $end])
            ->where('is_bounce', true)
            ->count();
        
        $bounceRate = $totalSessions > 0
            ? round(($bounces / $totalSessions) * 100, 1)
            : 0;

        // 6. Avg Session Duration
        $avgDuration = AnalyticsSession::whereIn('landing_id', $landingIds)
            ->whereBetween('started_at', [$start, $end])
            ->avg('duration_seconds');
            
        return [
            'has_data' => $totalSessions > 0,
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
        // Group by Date
        $sessionsData = AnalyticsSession::select(DB::raw('DATE(started_at) as date'), DB::raw('count(*) as count'))
            ->whereIn('landing_id', $landingIds)
            ->whereBetween('started_at', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');

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
        // 1. Traffic Sources
        $sourceStats = AnalyticsSession::whereIn('landing_id', $landingIds)
            ->whereBetween('started_at', [$start, $end])
            ->select('source_type', DB::raw('count(*) as total'))
            ->groupBy('source_type')
            ->pluck('total', 'source_type');

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

        // 2. Device Distribution
        $deviceStats = AnalyticsSession::whereIn('landing_id', $landingIds)
            ->whereBetween('started_at', [$start, $end])
            ->select('device_type', DB::raw('count(*) as total'))
            ->groupBy('device_type')
            ->pluck('total', 'device_type');

        // Normalize
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
             'mobile' => $totalDevices > 0 ? round((($devices['mobile']??0) / $totalDevices) * 100, 1) : 0,
             'desktop' => $totalDevices > 0 ? round((($devices['desktop']??0) / $totalDevices) * 100, 1) : 0,
             'tablet' => $totalDevices > 0 ? round((($devices['tablet']??0) / $totalDevices) * 100, 1) : 0,
        ];
        // Merge tablet into mobile for simple chart if requested, or keep separate. 
        // User asked for "Mobile/Desktop", but table might want detail. 
        // Let's pass the raw pct.


        // 3. Top Referrers
        $topReferrers = AnalyticsSession::whereIn('landing_id', $landingIds)
            ->whereBetween('started_at', [$start, $end])
            ->whereNotNull('referrer')
            ->select(DB::raw("SUBSTR(referrer, 1, 50) as ref_clean"), DB::raw('count(*) as total'))
            ->groupBy('ref_clean')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function($item) {
                $host = parse_url($item->ref_clean, PHP_URL_HOST) ?? $item->ref_clean;
                return ['domain' => $host, 'total' => $item->total];
            });

        // 4. Top UTM Campaigns
        $topCampaigns = AnalyticsSession::whereIn('landing_id', $landingIds)
            ->whereBetween('started_at', [$start, $end])
            ->whereNotNull('utm_campaign')
            ->select('utm_campaign', DB::raw('count(*) as total'))
            ->groupBy('utm_campaign')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // 5. New vs Returning
        // New: Visitor first_seen_at is within range
        // Returning: Visitor first_seen_at is before range (but they have a session in range)
        
        $newVisitors = AnalyticsSession::whereIn('landing_id', $landingIds)
            ->whereBetween('started_at', [$start, $end])
            ->whereHas('visitor', function($q) use ($start, $end) {
                $q->whereBetween('first_seen_at', [$start, $end]);
            })
            ->distinct('visitor_id')
            ->count('visitor_id');
            
        $totalVisitors = AnalyticsSession::whereIn('landing_id', $landingIds)
            ->whereBetween('started_at', [$start, $end])
            ->distinct('visitor_id')
            ->count('visitor_id');
            
        $returningVisitors = $totalVisitors - $newVisitors;
        
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
        // Step 1: Sessions (Landing View)
        $step1 = AnalyticsSession::whereIn('landing_id', $landingIds)
            ->whereBetween('started_at', [$start, $end])
            ->count();

        // Step 2: CTA Click (Unique sessions that had a cta_click event)
        $step2 = AnalyticsEvent::whereIn('landing_id', $landingIds)
            ->whereBetween('created_at', [$start, $end])
            ->where('event_name', 'cta_click')
            ->distinct('session_id')
            ->count('session_id');

        // Step 3: Form Start
        $step3 = AnalyticsEvent::whereIn('landing_id', $landingIds)
            ->whereBetween('created_at', [$start, $end])
            ->where('event_name', 'form_start')
            ->distinct('session_id')
            ->count('session_id');

        // Step 4: Lead Submitted
        // Use Leads table for accuracy
        $step4 = Lead::whereIn('landing_id', $landingIds)
            ->whereBetween('created_at', [$start, $end])
            ->count();
        
        // OR use 'lead_submit' event if we track it reliably via JS (e.g. AJAX forms)
        // For now, Leads table is safer source of truth for completion.

        return [
            ['label' => 'Sessions', 'value' => $step1, 'step' => 1],
            ['label' => 'CTA Clicks', 'value' => $step2, 'step' => 2],
            ['label' => 'Form Interactions', 'value' => $step3, 'step' => 3],
            ['label' => 'Leads', 'value' => $step4, 'step' => 4],
        ];
    }
    
    public function getLandingPerformance($landingIds, $start, $end)
    {
        // Table Data per Landing
        // We can use a query to group by landing_id
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
            
        // Fetch Leads count per Landing efficiently
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
}
