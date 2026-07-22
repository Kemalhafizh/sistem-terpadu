<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RoleBasedRateLimiterMiddleware
{
    /**
     * Handle an incoming request for Role-Based API Rate Limiting.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        $role = $user->role ?? 'guest';

        $maxAttempts = match ($role) {
            'admin', 'superadmin' => 300,
            'guru', 'staf_tu' => 120,
            'student', 'wali_murid' => 60,
            default => 20,
        };

        $identifier = $user ? "user:{$user->id}" : "ip:{$request->ip()}";
        $cacheKey = "rate_limit:" . md5($identifier . ":" . date('YmdHi'));

        $attempts = (int) Cache::get($cacheKey, 0) + 1;
        Cache::put($cacheKey, $attempts, 60);

        $remaining = max(0, $maxAttempts - $attempts);

        if ($attempts > $maxAttempts) {
            return response()->json([
                'message' => 'Batas jumlah permintaan (Rate Limit) terlampaui. Silakan tunggu 1 menit.',
                'retry_after_seconds' => 60,
            ], 429, [
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
                'Retry-After' => 60,
            ]);
        }

        $response = $next($request);
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', $remaining);

        return $response;
    }
}
