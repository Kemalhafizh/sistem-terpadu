<?php

namespace App\Support\Access;

use App\Models\User;

/**
 * Which features of the mobile app a user has, decided by the same route
 * middleware their requests will meet (see config/mobile_features.php).
 */
class MobileCapabilities
{
    /**
     * @return list<array{key: string, title: string, group: string, hardware: list<string>}>
     */
    public static function for(User $user): array
    {
        $features = [];
        foreach (self::registry() as $key => $feature) {
            if (self::allows($user, $feature['endpoint'])) {
                $features[] = [
                    'key' => $key,
                    'title' => $feature['title'],
                    'group' => $feature['group'],
                    'hardware' => $feature['hardware'] ?? [],
                ];
            }
        }

        return $features;
    }

    public static function allows(User $user, string $endpoint): bool
    {
        [$method, $uri] = self::split($endpoint);

        return RouteAccess::allowsEndpoint($user, $method, $uri);
    }

    /**
     * "GET student/grades" → ['GET', 'api/v1/student/grades']; route
     * parameters are filled with 1 so numeric constraints still match.
     *
     * @return array{0: string, 1: string}
     */
    public static function split(string $endpoint): array
    {
        [$method, $path] = explode(' ', $endpoint, 2);

        return [$method, 'api/v1/'.preg_replace('/\{[^}]+\}/', '1', ltrim($path, '/'))];
    }

    /** @return array<string, array{title: string, group: string, endpoint: string, hardware?: list<string>}> */
    public static function registry(): array
    {
        return config('mobile_features.features', []);
    }

    /** @return array<string, string> */
    public static function groups(): array
    {
        return config('mobile_features.groups', []);
    }
}
