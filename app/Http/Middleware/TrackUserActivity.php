<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Update last_seen_at
            $user->last_seen_at = now();

            // Try to set location if it's missing
            if (empty($user->country) || empty($user->city)) {
                try {
                    if (class_exists(\Stevebauman\Location\Facades\Location::class)) {
                        $position = \Stevebauman\Location\Facades\Location::get($request->ip());
                        if ($position) {
                            $user->country = $position->countryName;
                            $user->city = $position->cityName;
                        }
                    }
                } catch (\Throwable $e) {
                    // Location package not available — skip silently
                }
            }
            
            // Save without triggering model events (like updated_at) to avoid excessive queries/event firing
            $user->saveQuietly();
        }

        return $next($request);
    }
}
