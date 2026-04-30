<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Landing;
use App\Services\AnalyticsTrackerService;
use App\Support\LandingPublicUrl;

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

        if (!$landing && $workspaceEndpoint = $request->route('workspaceEndpoint')) {
            $baseQuery = Landing::query()
                ->where('status', 'published')
                ->whereHas('workspace.settings', function ($query) use ($workspaceEndpoint) {
                    $query->where('workspace_public_endpoint', strtolower((string) $workspaceEndpoint));
                });

            $landing = (clone $baseQuery)
                ->where('is_main', true)
                ->first()
                ?? (clone $baseQuery)
                ->first();
        }

        if (!$landing && $request->path() === '/') {
            if (app()->has('active_landing_page') && app('active_landing_page') instanceof Landing) {
                $landing = app('active_landing_page');
            }
        }

        if (!$landing && $request->path() === '/') {
            $landing = Landing::query()
                ->where('is_main', true)
                ->where('status', 'published')
                ->with(['workspace.user.roles'])
                ->get()
                ->first(function (Landing $candidate) {
                    return LandingPublicUrl::isPlatformMainLanding($candidate);
                });
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
            'public.workspace.home',
            'public.workspace.landing',
            'public.workspace.landing.page',
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
