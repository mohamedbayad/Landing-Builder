<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Apply CSP only on builder routes (recommended)
        // Adjust this condition to match your builder URL pattern (e.g. /landings/{id}/editor)
        // Current route seems to include 'editor' or 'landings'
        if (!$request->is('*editor*') && !$request->is('landings/*')) {
             return $response;
        }

        $csp = implode('; ', [
            "default-src 'self'",
            // Allow Vite/dev assets and inline styles
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com http://127.0.0.1:5173 http://localhost:5173",
            "font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com http://127.0.0.1:5173 http://localhost:5173",
            "img-src 'self' data: blob: https: http:",
            // Allow Tailwind CDN if used (though we prefer local), and Vite's HMR scripts
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://js.stripe.com https://www.paypal.com http://127.0.0.1:5173 http://localhost:5173",
            // Connect src for Vite HMR and APIs
            "connect-src 'self' https://app.grapesjs.com http://localhost:* http://127.0.0.1:* ws://localhost:* ws://127.0.0.1:* https://api.stripe.com https://www.paypal.com",
            "frame-src 'self' https://js.stripe.com https://www.paypal.com",
            "object-src 'none'",
            "base-uri 'self'",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }
}
