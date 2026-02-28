<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stevebauman\Location\Facades\Location;

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

            // Try to set location if it's missing or if last seen was > 1 hour ago (to limit API calls if any)
            // But since it's a local database lookup (MaxMind), it's fast. Let's just do it if missing.
            if (empty($user->country) || empty($user->city)) {
                if ($position = Location::get($request->ip())) {
                    $user->country = $position->countryName;
                    $user->city = $position->cityName;
                }
            }
            
            // Save without triggering model events (like updated_at) to avoid excessive queries/event firing
            $user->saveQuietly();
        }

        return $next($request);
    }
}
