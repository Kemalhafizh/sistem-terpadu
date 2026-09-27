<?php

namespace App\Http\Middleware;

use App\Support\ContentSecurityPolicy;
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

        // PHP adds "X-Powered-By: PHP/x.y.z" when expose_php is on (the Docker
        // image turns it off, a plain server install may not). Don't advertise
        // the exact runtime version to scanners.
        if (! headers_sent()) {
            header_remove('X-Powered-By');
        }
        $response->headers->remove('X-Powered-By');

        // Make sure it's a response object we can modify headers on
        if (method_exists($response, 'header')) {
            $response->header('X-Content-Type-Options', 'nosniff');
            $response->header('X-Frame-Options', 'DENY');
            $response->header('X-XSS-Protection', '1; mode=block');
            $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');
            $response->header('Permissions-Policy', 'camera=(self), microphone=(), geolocation=(self)');

            $response->header('Content-Security-Policy', ContentSecurityPolicy::header());

            // HSTS only in non-local environments to avoid localhost SSL loop issues
            if (! app()->environment('local', 'testing')) {
                $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
            }
        }

        return $response;
    }
}
