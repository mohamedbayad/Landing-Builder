<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Cache;
use App\Models\WorkspaceSetting;

class CheckLicenseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Simple Cached Token Check
        if (Cache::has('license_token')) {
            return $next($request);
        }

        // Database Fallback
        $user = $request->user();
        if ($user) {
            $workspace = $user->workspaces()->first();
            if ($workspace && $workspace->settings) {
                if ($workspace->settings->license_status === 'active') {
                    // Re-cache token if available
                    if (isset($workspace->settings->license_data['token'])) {
                         Cache::put('license_token', $workspace->settings->license_data['token'], now()->addDays(30));
                         return $next($request);
                    }
                }
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'License required.'], 403);
        }

        return redirect()->route('settings.index')->with('error', 'Please activate your license to access this feature.');
    }
}
