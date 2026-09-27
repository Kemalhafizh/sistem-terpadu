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
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Make sure it's a response object we can modify headers on
        if (method_exists($response, 'header')) {
            $response->header('X-Content-Type-Options', 'nosniff');
            $response->header('X-Frame-Options', 'DENY');
            $response->header('X-XSS-Protection', '1; mode=block');
            $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');
            $response->header('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self)');

            // Content-Security-Policy configuration
            $csp = "default-src 'self'; "
                 ."script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://app.midtrans.com https://sandbox.midtrans.com; "
                 ."style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; "
                 ."font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; "
                 ."img-src 'self' data: https:; "
                 ."frame-src 'self' https://app.midtrans.com https://sandbox.midtrans.com; "
                 ."connect-src 'self' ws: wss: https://api.sandbox.midtrans.com https://api.midtrans.com;";

            $response->header('Content-Security-Policy', $csp);

            // HSTS only in non-local environments to avoid localhost SSL loop issues
            if (! app()->environment('local', 'testing')) {
                $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
            }
        }

        return $response;
    }
}
