<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Landing;
use App\Services\AnalyticsTrackerService;

class TrackPageVisit
{
    protected $tracker;

    public function __construct(AnalyticsTrackerService $tracker)
    {
        $this->tracker = $tracker;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track successful page loads (200 OK)
        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        // Exclude system paths
        if ($this->shouldExclude($request)) {
            return $response;
        }

        // Resolve Landing
        $landing = $this->resolveLanding($request);

        // Track via Service
        try {
            $this->tracker->track($request, $landing);
        } catch (\Exception $e) {
            // Fail silently
        }

        return $response;
    }

    private function resolveLanding(Request $request): ?Landing
    {
        $landing = $request->route('landing');
        
        if (is_string($landing)) {
             $landing = Landing::where('slug', $landing)->first();
        }

        if (!$landing && $slug = $request->route('slug')) {
            $landing = Landing::where('slug', $slug)->first();
        }

        if (!$landing && $landingSlug = $request->route('landingSlug')) {
            $landing = Landing::where('slug', $landingSlug)->first();
        }

        if (!$landing && $request->path() === '/') {
            $landing = Landing::where('is_main', true)->where('status', 'published')->first();
        }

        return $landing;
    }

    private function shouldExclude(Request $request): bool
    {
        // Only track GET requests for pageviews
        if (!$request->isMethod('GET')) {
            return true;
        }

        // Exclude authenticated admins/users from being tracked as public visitors
        if (\Illuminate\Support\Facades\Auth::check()) {
            return true;
        }

        // The specific route names that represent public-facing pages we want to track
        $publicRoutes = [
            'public.home',
            'public.page',
            'public.landing.page',
            'landings.checkout'
        ];

        $routeName = $request->route() ? $request->route()->getName() : null;

        if ($routeName && in_array($routeName, $publicRoutes)) {
            // It's a public landing page. Don't exclude.
            return false;
        }

        // Exclude everything else (dashboard, editor, preview, API, etc.)
        return true;
    }
}
