<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('POST') && ! $request->isMethod('PUT') && ! $request->isMethod('PATCH') && ! $request->isMethod('DELETE')) {
            return $next($request);
        }

        $idempotencyKey = $request->header('Idempotency-Key');

        if (! $idempotencyKey) {
            return $next($request);
        }

        $userId = $request->user() ? $request->user()->id : 'guest';
        $cacheKey = "idempotency_{$userId}_{$idempotencyKey}";

        if (Cache::has($cacheKey)) {
            $cachedResponse = Cache::get($cacheKey);

            return response($cachedResponse['content'], $cachedResponse['status'], $cachedResponse['headers']);
        }

        $response = $next($request);

        // Only cache successful responses
        if ($response->isSuccessful() || $response->isRedirection()) {
            Cache::put($cacheKey, [
                'content' => $response->getContent(),
                'status' => $response->getStatusCode(),
                'headers' => $response->headers->all(),
            ], now()->addHours(24));
        }

        return $response;
    }
}
