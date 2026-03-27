<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\WorkspaceSetting;

class CheckLicenseMiddleware
{
    /**
     * Handle an incoming request.
     * This is the primary (fast) license gate. It checks cached token or DB status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Fast path: Cached token exists
        if (Cache::has('license_token')) {
            Log::debug('LICENSE_CHECK: Passed via cached token.');
            return $next($request);
        }

        // 2. Database fallback
        $user = $request->user();
        if ($user) {
            $workspace = $user->workspaces()->first();
            if ($workspace && $workspace->settings) {
                $settings = $workspace->settings;
                
                Log::info('LICENSE_CHECK: DB lookup.', [
                    'license_status' => $settings->license_status,
                    'has_token' => isset($settings->license_data['token']),
                    'has_key' => !empty($settings->license_key),
                ]);

                if ($settings->license_status === 'active') {
                    // Re-cache token if available
                    if (isset($settings->license_data['token'])) {
                        Cache::put('license_token', $settings->license_data['token'], now()->addDays(30));
                        Log::info('LICENSE_CHECK: Re-cached token from DB. Access granted.');
                    }
                    return $next($request);
                }

                Log::warning('LICENSE_CHECK: DB status is not active.', [
                    'status' => $settings->license_status,
                    'key' => $settings->license_key ? substr($settings->license_key, 0, 10) . '...' : 'NULL',
                ]);
            } else {
                Log::warning('LICENSE_CHECK: No workspace or settings found.', [
                    'user_id' => $user->id,
                    'has_workspace' => (bool) $workspace,
                ]);
            }
        } else {
            Log::warning('LICENSE_CHECK: No authenticated user.');
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'License required.'], 403);
        }

        return redirect()->route('settings.index')->with('error', 'Please activate your license to access this feature.');
    }
}
