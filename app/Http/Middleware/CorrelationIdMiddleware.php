<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CorrelationIdMiddleware
{
    /** What a client-supplied id may look like; anything else is replaced. */
    private const VALID_ID = '/^[A-Za-z0-9._-]{1,64}$/';

    /**
     * Handle an incoming request for Distributed Tracing Correlation ID.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $correlationId = $request->headers->get('X-Correlation-ID');

        // The id is written to request logs, so a client-supplied value is
        // only kept when it's a plain token (no spaces, tabs or newlines).
        if (! is_string($correlationId) || ! preg_match(self::VALID_ID, $correlationId)) {
            $correlationId = (string) Str::uuid();
            $request->headers->set('X-Correlation-ID', $correlationId);
        }

        $response = $next($request);
        $response->headers->set('X-Correlation-ID', $correlationId);

        return $response;
    }
}
