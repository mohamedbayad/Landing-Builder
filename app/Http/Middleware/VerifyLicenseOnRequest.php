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
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Get current token (from Cache or DB)
        // LicenseService caches 'license_token'. If not, we might be unactivated.
        $token = Cache::get('license_token');

        if (!$token) {
             // Try to recover from DB setting if cache cleared
             $user = $request->user();
             if ($user) {
                 $workspace = $user->workspaces()->first();
                 if ($workspace && $workspace->settings && isset($workspace->settings->license_data['token'])) {
                     $token = $workspace->settings->license_data['token'];
                     Cache::put('license_token', $token, now()->addDays(30)); 
                 }
             }
        }

        if (!$token) {
            // No token found -> Block access
            if ($request->expectsJson()) {
                 return response()->json(['message' => 'License verification failed. No token found.'], 403);
            }
            return redirect()->route('settings.index')->with('error', 'License verification required to access the Builder.');
        }

        // 2. Real-Time Verification (Fail-Open or Fail-Closed?)
        // User requested "Strict" -> So we check every time. 
        // Optimization: Cache the "check" for 1-5 minutes to avoid hammering the server on every asset load?
        // User said: "Immediately block access... even if cached". So strict check implies live check.
        // Let's implement a very short cache (e.g. 30 seconds) or direct check.
        // Direct check for "Kill Switch" efficacy.
        
        try {
            // Use config() for production safety (env() returns null if cached)
            $baseUrl = config('services.licensing.url');
            
            if (!$baseUrl) {
                 // Fallback to env() just in case config cache issue, but log warning
                 $baseUrl = env('LICENSING_SERVER_URL');
                 if (!$baseUrl) {
                    Log::error("License Check Failed: LICENSING_SERVER_URL not configured.");
                    throw new \Exception("Licensing Server URL missing");
                 }
            }

            $licensingUrl = rtrim($baseUrl, '/') . '/check-status';
            
            // Increased timeout for shared hosting
            $response = Http::timeout(5)->post($licensingUrl, [
                'token' => $token
            ]);

            if ($response->successful()) {
                $status = $response->json('status');
                if ($status === 'active') {
                     return $next($request);
                }
                 Log::warning("License Verification Refused: Status is '$status'. Token: " . substr($token, 0, 10) . "...");
            } else {
                Log::warning("License Verification HTTP Error: " . $response->status() . " - " . $response->body());
            }
            
            // If we reach here, license is NOT active (blocked, expired, or invalid)
            
            // Clear local license state
            Cache::forget('license_token');

            // Update DB to inactive
            $user = $request->user();
            if ($user) {
                $workspace = $user->workspaces()->first();
                if ($workspace && $workspace->settings) {
                    $workspace->settings()->update(['license_status' => 'inactive']); 
                }
            }
            
            if ($request->expectsJson()) {
                 return response()->json(['message' => 'License is no longer valid.', 'debug' => 'Check server logs.'], 403);
            }
            return redirect()->route('settings.index')->with('error', 'Your license has been revoked or expired. Access to Builder is blocked.');

        } catch (\Exception $e) {
            Log::error("License Check Failed (Network/Error): " . $e->getMessage());
             if ($request->expectsJson()) {
                 return response()->json([
                     'message' => 'License verification server unreachable.',
                     'debug' => $e->getMessage() // Only for admin/debugging, maybe hide in prod? Left for now as they are admin.
                 ], 403);
            }
             return redirect()->route('settings.index')->with('error', 'Unable to verify license status. Please check your connection.');
        }
    }
}
