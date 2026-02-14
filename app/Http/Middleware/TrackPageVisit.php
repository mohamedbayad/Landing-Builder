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
        $path = $request->path();
        
        if ($request->is('api/*', 'dashboard*', 'analytics*', 'telescope*', '_debugbar*', 'sanctum/*', 'storage/*', 'build/*')) {
            return true;
        }
        
        $extensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'map', 'json'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if (in_array(strtolower($extension), $extensions)) {
            return true;
        }

        return false;
    }
}
