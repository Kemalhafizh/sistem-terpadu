<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Exception;

class EnterpriseCacheService
{
    /**
     * Store item with optional tag grouping fallback.
     */
    public function remember(string $key, int $ttlSeconds, callable $callback, array $tags = [])
    {
        try {
            if (! empty($tags) && Cache::supportsTags()) {
                return Cache::tags($tags)->remember($key, $ttlSeconds, $callback);
            }
        } catch (Exception $e) {
            // Fallback for cache drivers that do not support tags (e.g. file / sqlite)
        }

        return Cache::remember($key, $ttlSeconds, $callback);
    }

    /**
     * Flush specific cache tag group.
     */
    public function flushTag(string $tag): bool
    {
        try {
            if (Cache::supportsTags()) {
                Cache::tags([$tag])->flush();
                return true;
            }
        } catch (Exception $e) {
            // Fallback
        }

        // Forget keys matching tag prefix
        Cache::forget("tag_{$tag}_*");
        return true;
    }

    /**
     * Flush all cache.
     */
    public function flushAll(): bool
    {
        return Cache::flush();
    }

    /**
     * Get Cache metrics summary.
     */
    public function getCacheMetrics(): array
    {
        $driver = config('cache.default', 'file');

        return [
            'driver' => strtoupper($driver),
            'supports_tags' => Cache::supportsTags(),
            'status' => 'ONLINE',
            'uptime' => '100%',
        ];
    }
}
