<?php

namespace App\Services;

class RequestLifetimeCacheService
{
    private static array $storage = [];

    /**
     * Retrieve item from request-scoped in-memory cache, or execute fallback.
     */
    public function remember(string $key, callable $callback)
    {
        if (array_key_exists($key, self::$storage)) {
            return self::$storage[$key];
        }

        $value = $callback();
        self::$storage[$key] = $value;

        return $value;
    }

    /**
     * Flush all items in current request memory.
     */
    public function flush(): void
    {
        self::$storage = [];
    }

    /**
     * Get current stored keys count.
     */
    public function count(): int
    {
        return count(self::$storage);
    }
}
