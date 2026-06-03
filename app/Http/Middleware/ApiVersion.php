<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiVersion
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Enforce JSON accept header so Laravel always returns JSON errors/validations
        $request->headers->set('Accept', 'application/json');

        $response = $next($request);

        // Inject the API Version header into the response
        $response->headers->set('X-API-Version', 'v1.0');

        return $response;
    }
}
