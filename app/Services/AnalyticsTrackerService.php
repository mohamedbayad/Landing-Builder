<?php

namespace App\Services;

use App\Models\AnalyticsVisitor;
use App\Models\AnalyticsSession;
use App\Models\AnalyticsEvent;
use App\Models\Landing;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;
use Carbon\Carbon;

class AnalyticsTrackerService
{
    protected $visitorCookieName = 'lb_visitor_id';
    protected $sessionCookieName = 'lb_session_id';
    protected $sessionTimeoutMinutes = 30;

    public function track(Request $request, ?Landing $landing)
    {
        // 1. Identify or Create Visitor
        $visitor = $this->getOrCreateVisitor($request);
        
        // 2. Identify or Create Session
        $session = $this->getOrCreateSession($request, $visitor, $landing);

        // 3. Log Pageview Event
        $this->logEvent($session, 'pageview', [
            'url' => $request->fullUrl(),
            'title' => null, // Could be passed if available
        ], $request->path());

        return [
            'visitor_id' => $visitor->visitor_id,
            'session_id' => $session->session_id,
        ];
    }

    public function logEvent($sessionOrId, $eventName, $data = [], $urlPath = null, $elementLabel = null, $elementType = null, $elementPosition = null)
    {
        $session = $sessionOrId instanceof AnalyticsSession ? $sessionOrId : AnalyticsSession::where('session_id', $sessionOrId)->first();
        
        if (!$session) return null;

        $event = new AnalyticsEvent();
        $event->session_id_fk = $session->id;
        $event->session_id = $session->session_id; // For redundancy/speed if needed
        $event->visitor_id = $session->visitor_id;
        $event->landing_id = $session->landing_id;
        $event->event_name = $eventName;
        $event->event_data = $data;
        $event->url_path = $urlPath;
        $event->element_label = $elementLabel;
        $event->element_type = $elementType;
        $event->element_position = $elementPosition;
        $event->save();

        // Update Session Activity
        $session->touch('last_activity_at');
        
        // Calculate Duration
        $start = Carbon::parse($session->started_at);
        $now = now();
        $session->duration_seconds = $start->diffInSeconds($now);

        // Bounce Logic: If > 1 event, it's not a bounce (unless we define bounce strictly as pageview count)
        // Usually, an interaction event means not a bounce.
        if ($eventName !== 'pageview' || $session->events()->count() > 1) {
            $session->is_bounce = false;
        }

        $session->save();

        return $event;
    }

    protected function getOrCreateVisitor(Request $request)
    {
        $visitorId = $request->cookie($this->visitorCookieName);
        
        // Verify if exists in DB
        $visitor = null;
        if ($visitorId) {
            $visitor = AnalyticsVisitor::where('visitor_id', $visitorId)->first();
        }

        if (!$visitor) {
            $visitorId = (string) Str::uuid();
            $visitor = new AnalyticsVisitor();
            $visitor->visitor_id = $visitorId;
            $visitor->first_seen_at = now();
        }

        // Update Info
        $visitor->ip_hash = hash_hmac('sha256', $request->ip(), config('app.key'));
        $visitor->user_agent = substr($request->header('User-Agent'), 0, 500);
        $visitor->last_seen_at = now();
        $visitor->save();

        // Queue Cookie (1 year)
        Cookie::queue($this->visitorCookieName, $visitor->visitor_id, 60 * 24 * 365);

        return $visitor;
    }

    protected function getOrCreateSession(Request $request, AnalyticsVisitor $visitor, ?Landing $landing)
    {
        $sessionId = $request->cookie($this->sessionCookieName);
        $session = null;

        if ($sessionId) {
            $session = AnalyticsSession::where('session_id', $sessionId)->first();
            
            // Check timeout
            if ($session) {
                $lastActivity = Carbon::parse($session->last_activity_at);
                if ($lastActivity->diffInMinutes(now()) > $this->sessionTimeoutMinutes) {
                    $session = null; // Expired
                }
            }
        }

        if (!$session) {
            $sessionId = (string) Str::uuid();
            $session = new AnalyticsSession();
            $session->session_id = $sessionId;
            $session->visitor_id = $visitor->id;
            $session->landing_id = $landing ? $landing->id : null;
            $session->started_at = now();
            
            // Capture Source Info (First touch)
            $session->source_type = $this->determineSourceType($request);
            $session->referrer = $request->header('referer');
            $session->utm_source = $request->query('utm_source');
            $session->utm_medium = $request->query('utm_medium');
            $session->utm_campaign = $request->query('utm_campaign');
            $session->utm_content = $request->query('utm_content');
            $session->utm_term = $request->query('utm_term');
            
            // Device Info
            $session->device_type = $this->determineDeviceType($request->header('User-Agent'));
            // Browser/OS detection could go here (using a lib or simple regex)

            // Geolocation
            $ip = $request->ip();
            if ($position = \Stevebauman\Location\Facades\Location::get($ip)) {
                $session->country = $position->countryName;
                $session->city = $position->cityName;
            } elseif (in_array($ip, ['127.0.0.1', '::1']) || str_starts_with($ip, '192.168.')) {
                // Fallback for local development so map still renders
                $session->country = 'Morocco';
                $session->city = 'Casablanca';
            }
        }

        $session->last_activity_at = now();
        $session->save();

        // Queue Cookie (30 mins rolling)
        Cookie::queue($this->sessionCookieName, $session->session_id, $this->sessionTimeoutMinutes);

        return $session;
    }

    // Reuse logic from previous implementation
    protected function determineSourceType(Request $request): string
    {
        $referrer = $request->header('referer');
        $utmSource = $request->query('utm_source');
        $utmMedium = $request->query('utm_medium');

        if ($utmSource) {
            $utm = strtolower($utmSource);
            $medium = strtolower($utmMedium ?? '');
            
            if (in_array($medium, ['email', 'newsletter'])) return 'email';
            if (in_array($medium, ['cpc', 'ppc', 'paid'])) return 'paid';
            if (in_array($medium, ['affiliate', 'referral'])) return 'referral';
            if (in_array($utm, ['facebook', 'instagram', 'twitter', 'linkedin', 'tiktok', 'social'])) return 'social';
            if (in_array($utm, ['google', 'bing', 'yahoo', 'search'])) return 'search';
            return 'other'; 
        }

        if (empty($referrer)) return 'direct';

        $host = strtolower(parse_url($referrer, PHP_URL_HOST));
        
        $searchEngines = ['google.', 'bing.', 'yahoo.', 'duckduckgo.', 'yandex.', 'baidu.'];
        foreach ($searchEngines as $engine) {
            if (str_contains($host, $engine)) return 'search';
        }

        $socials = ['facebook.', 'instagram.', 'twitter.', 't.co', 'linkedin.', 'tiktok.', 'pinterest.', 'youtube.', 'whatsapp.'];
        foreach ($socials as $social) {
            if (str_contains($host, $social)) return 'social';
        }

        $currentHost = request()->getHost();
        if (str_contains($host, $currentHost)) return 'direct';

        return 'referral';
    }

    protected function determineDeviceType(?string $userAgent): string
    {
        if (empty($userAgent)) return 'unknown';
        $ua = strtolower($userAgent);
        
        if (str_contains($ua, 'bot') || str_contains($ua, 'crawl')) return 'bot';
        if (str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone')) return 'mobile';
        if (str_contains($ua, 'ipad') || str_contains($ua, 'tablet')) return 'tablet';
        
        return 'desktop';
    }
}
