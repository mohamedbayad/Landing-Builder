<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSubscription
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        // Backward compatibility for legacy accounts created before subscription rollout.
        if ($user->roles()->doesntExist()) {
            return $next($request);
        }

        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return $next($request);
        }

        if (!$user->hasActiveSubscription()) {
            return redirect()->route('subscriptions.index')->with('error', 'An active subscription is required to access this feature.');
        }

        return $next($request);
    }
}
