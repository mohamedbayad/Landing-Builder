<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VerifyLicenseOnRequest
{
    /**
     * Cache key for the last successful license verification result.
     * This prevents hammering the licensing server on every request.
     */
    const CACHE_KEY_VERIFIED = 'license_verified_status';
    const CACHE_KEY_TOKEN = 'license_token';
    const VERIFICATION_TTL_MINUTES = 5;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Get current token (from Cache or DB)
        $token = Cache::get(self::CACHE_KEY_TOKEN);

        if (!$token) {
            // Try to recover from DB setting if cache was cleared
            $token = $this->recoverTokenFromDb($request);
        }

        if (!$token) {
            Log::warning('LICENSE_REALTIME: No token found anywhere. Blocking access.', [
                'user_id' => $request->user()?->id,
            ]);
            return $this->denyAccess($request, 'License verification required. Please activate your license in Settings.');
        }

        // 2. Check if we have a recent successful verification cached
        $cachedStatus = Cache::get(self::CACHE_KEY_VERIFIED);
        
        if ($cachedStatus === 'active') {
            Log::debug('LICENSE_REALTIME: Access granted via cached verification.');
            return $next($request);
        }

        // 3. Perform live verification against the licensing server
        try {
            $baseUrl = config('services.licensing.url');
            
            if (!$baseUrl) {
                $baseUrl = env('LICENSING_SERVER_URL');
                if (!$baseUrl) {
                    Log::error('LICENSE_REALTIME: LICENSING_SERVER_URL not configured. Falling back to DB status.');
                    return $this->fallbackToDbStatus($request, $next, $token);
                }
            }

            $licensingUrl = rtrim($baseUrl, '/') . '/check-status';
            
            Log::info('LICENSE_REALTIME: Performing live verification.', [
                'url' => $licensingUrl,
                'token_preview' => substr($token, 0, 10) . '...',
            ]);

            $response = Http::timeout(5)->post($licensingUrl, [
                'token' => $token
            ]);

            if ($response->successful()) {
                $status = $response->json('status');
                
                Log::info('LICENSE_REALTIME: Server responded.', [
                    'status' => $status,
                ]);

                if ($status === 'active') {
                    // Cache the successful result to avoid hammering the server
                    Cache::put(self::CACHE_KEY_VERIFIED, 'active', now()->addMinutes(self::VERIFICATION_TTL_MINUTES));
                    return $next($request);
                }

                // Server explicitly said NOT active (blocked, expired, invalid)
                Log::warning('LICENSE_REALTIME: Server denied access.', [
                    'status' => $status,
                    'response' => $response->json(),
                ]);

                // Clear local state since the server explicitly denied it
                $this->clearLicenseState($request);
                
                return $this->denyAccess($request, 'Your license has been revoked or expired. Access is blocked.');
            } else {
                // Server returned an HTTP error (4xx/5xx) but was reachable
                Log::warning('LICENSE_REALTIME: Server returned error.', [
                    'http_status' => $response->status(),
                    'body' => substr($response->body(), 0, 300),
                ]);

                // Check if it's a definitive denial (403, 404) vs server error (500)
                if (in_array($response->status(), [403, 404])) {
                    $serverStatus = $response->json('status') ?? 'unknown';
                    if (in_array($serverStatus, ['blocked', 'expired', 'invalid'])) {
                        $this->clearLicenseState($request);
                        return $this->denyAccess($request, 'Your license is no longer valid.');
                    }
                }

                // For server errors (500, etc.), fallback to DB status
                return $this->fallbackToDbStatus($request, $next, $token);
            }

        } catch (\Exception $e) {
            // Network error, timeout, connection refused, etc.
            Log::warning('LICENSE_REALTIME: Server unreachable. Falling back to DB status.', [
                'error' => $e->getMessage(),
            ]);

            // GRACEFUL DEGRADATION: Don't block the user if we simply can't reach the licensing server
            return $this->fallbackToDbStatus($request, $next, $token);
        }
    }

    /**
     * Recover token from database if cache was cleared.
     */
    protected function recoverTokenFromDb(Request $request): ?string
    {
        $user = $request->user();
        if (!$user) return null;

        $workspace = $user->workspaces()->first();
        if (!$workspace || !$workspace->settings) return null;

        $token = $workspace->settings->license_data['token'] ?? null;
        
        if ($token) {
            Cache::put(self::CACHE_KEY_TOKEN, $token, now()->addDays(30));
            Log::info('LICENSE_REALTIME: Recovered token from DB and re-cached.');
        }

        return $token;
    }

    /**
     * Fallback to checking the database status when the licensing server is unreachable.
     * This implements fail-open for network issues while still respecting locally stored status.
     */
    protected function fallbackToDbStatus(Request $request, Closure $next, string $token): Response
    {
        $user = $request->user();
        if (!$user) {
            return $this->denyAccess($request, 'Authentication required.');
        }

        $workspace = $user->workspaces()->first();
        if (!$workspace || !$workspace->settings) {
            return $this->denyAccess($request, 'No workspace settings found.');
        }

        $dbStatus = $workspace->settings->license_status;
        $dbToken = $workspace->settings->license_data['token'] ?? null;

        Log::info('LICENSE_REALTIME: Fallback DB check.', [
            'db_status' => $dbStatus,
            'token_matches_db' => ($token === $dbToken),
        ]);

        if ($dbStatus === 'active' && $token === $dbToken) {
            // DB says active and tokens match — trust it
            // Cache a shorter-lived verification to retry the live check sooner
            Cache::put(self::CACHE_KEY_VERIFIED, 'active', now()->addMinutes(2));
            Log::info('LICENSE_REALTIME: Access GRANTED via DB fallback.');
            return $next($request);
        }

        Log::warning('LICENSE_REALTIME: DB fallback also denied access.', [
            'db_status' => $dbStatus,
        ]);

        return $this->denyAccess($request, 'License verification failed. Please check your license in Settings.');
    }

    /**
     * Clear all local license state (cache + DB).
     */
    protected function clearLicenseState(Request $request): void
    {
        Cache::forget(self::CACHE_KEY_TOKEN);
        Cache::forget(self::CACHE_KEY_VERIFIED);

        $user = $request->user();
        if ($user) {
            $workspace = $user->workspaces()->first();
            if ($workspace && $workspace->settings) {
                $workspace->settings()->update(['license_status' => 'inactive']);
            }
        }
    }

    /**
     * Return a deny response (redirect or JSON).
     */
    protected function denyAccess(Request $request, string $message): Response
    {
        Log::warning('LICENSE_REALTIME: ACCESS DENIED.', [
            'reason' => $message,
            'path' => $request->path(),
            'user_id' => $request->user()?->id,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'redirect' => route('settings.index'),
            ], 403);
        }

        return redirect()->route('settings.index')->with('error', $message);
    }
}
