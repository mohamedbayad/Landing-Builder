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
            $licensingUrl = config('services.licensing.url', env('LICENSING_SERVER_URL')) . '/check-status';
            
            $response = Http::timeout(2)->post($licensingUrl, [
                'token' => $token
            ]);

            if ($response->successful()) {
                $status = $response->json('status');
                if ($status === 'active') {
                     return $next($request);
                }
            }
            
            // If we reach here, license is NOT active (blocked, expired, or invalid)
            Log::warning("License Verification Failed: " . $response->body());
            
            // Clear local license state
            Cache::forget('license_token');

            // Update DB to inactive
            $user = $request->user();
            if ($user) {
                $workspace = $user->workspaces()->first();
                if ($workspace && $workspace->settings) {
                    $workspace->settings()->update(['license_status' => 'inactive']); // or 'revoked' if we had that enum, sticking to inactive for now to match UI logic
                }
            }
            
            if ($request->expectsJson()) {
                 return response()->json(['message' => 'License is no longer valid.'], 403);
            }
            return redirect()->route('settings.index')->with('error', 'Your license has been revoked or expired. Access to Builder is blocked.');

        } catch (\Exception $e) {
            // Network failure? 
            // If Licensing Server is down, do we block user? 
            // "Kill Switch" usually implies Fail-Closed for security, but bad UX if server down.
            // User requirement: "If I delete/ban... immediately block".
            // Implementation: We will Log warning and ALLOW if network error (Fail-Open) to prevent outage if LS is down?
            // OR Fail-Closed? "Strict Middleware" suggests Fail-Closed.
            // Let's go with Fail-Closed for "Strict" requirement, or maybe softer?
            // I'll implement Fail-Closed (Block) as requested for "Real-Time Verification".
            // Actually, for SaaS, usually you allow if server unreachable to avoid angry customers during your own downtime.
            // But if user wants "Kill Switch", they usually prioritize control.
            // I will implement a default BLOCK on network error but log it.
            
            Log::error("License Check Failed (Network/Error): " . $e->getMessage());
             if ($request->expectsJson()) {
                 return response()->json(['message' => 'License verification server unreachable.'], 403);
            }
             return redirect()->route('settings.index')->with('error', 'Unable to verify license status. Please check your connection.');
        }
    }
}
