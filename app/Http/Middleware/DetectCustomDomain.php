<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetectCustomDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $mainDomain = config('app.main_domain'); // e.g. builder.com
        
        // Skip if it's the main app domain or subdomain
        if ($host === $mainDomain || str_ends_with($host, '.' . $mainDomain)) {
            return $next($request);
        }
        
        // If the hidden custom-domain model is not deployed, skip gracefully.
        if (!class_exists(\App\Models\CustomDomain::class)) {
            return $next($request);
        }

        // Check if this custom domain exists and is active
        $customDomain = \App\Models\CustomDomain::findByHost($host);
        
        if ($customDomain && $customDomain->landing) {
            // Share the landing page with all downstream code
            app()->instance('active_landing_page', $customDomain->landing);
            $request->attributes->set('custom_domain', $customDomain);
            $request->attributes->set('landing_page_id', $customDomain->landing_page_id);
        }
        
        return $next($request);
    }
}
