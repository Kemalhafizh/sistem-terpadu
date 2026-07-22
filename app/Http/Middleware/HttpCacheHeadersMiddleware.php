<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HttpCacheHeadersMiddleware
{
    /**
     * Handle an incoming request for Response Gateway Caching.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Apply ETag & Cache-Control headers only for successful GET requests
        if ($request->isMethod('GET') && $response->getStatusCode() === 200) {
            $content = $response->getContent();
            if (is_string($content) && strlen($content) > 0) {
                $eTag = '"' . md5($content) . '"';

                $response->headers->set('ETag', $eTag);
                $response->headers->set('Cache-Control', 'public, max-age=300, must-revalidate');

                // Check If-None-Match header
                if ($request->headers->get('If-None-Match') === $eTag) {
                    $response->setStatusCode(304);
                    $response->setContent(null);
                }
            }
        }

        return $response;
    }
}
