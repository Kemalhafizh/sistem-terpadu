<?php

namespace App\Support\Access;

use App\Http\Middleware\RoleMiddleware;
use App\Models\User;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route as Router;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * "Can this user open that route?" answered from the route's own `role:`
 * middleware — the same rule the request will be judged by. Menus (web
 * sidebar) and the mobile capability list use this instead of keeping their
 * own role lists, so what a user sees is exactly what they can open.
 *
 * Fail-closed: a signed-in web route with neither `role:` nor
 * `can:` is closed to everyone but superadmin, unless config/route_access.php
 * lists it as open to every signed-in user or as unguarded debt.
 */
class RouteAccess
{
    /** @var array<string, list<list<string>>|null> */
    private static array $cache = [];

    public static function allowsRoute(?User $user, string $routeName): bool
    {
        $route = Router::getRoutes()->getByName($routeName);

        return $route !== null && self::allows($user, $route);
    }

    public static function allowsEndpoint(?User $user, string $method, string $uri): bool
    {
        try {
            $route = Router::getRoutes()->match(Request::create('/'.ltrim($uri, '/'), strtoupper($method)));
        } catch (HttpException) {
            return false;
        }

        return self::allows($user, $route);
    }

    public static function allows(?User $user, Route $route): bool
    {
        if ($user === null) {
            return false;
        }
        if (self::isUnruled($route) && ! self::isListed($route)) {
            return $user->role === 'superadmin';
        }
        foreach (self::roleRules($route) as $roles) {
            if (! RoleMiddleware::allows($user->role, $roles)) {
                return false;
            }
        }
        foreach (self::abilityRules($route) as [$ability, $arguments]) {
            if (! Gate::forUser($user)->allows($ability, $arguments)) {
                return false;
            }
        }

        return true;
    }

    /** "GET dashboard", "PUT|PATCH users/{user}": how config/route_access.php names a route. */
    public static function key(Route $route): string
    {
        return implode('|', array_values(array_diff($route->methods(), ['HEAD']))).' '.$route->uri();
    }

    /** A signed-in web route: the kind that must carry `role:` or `can:`. */
    public static function requiresSignIn(Route $route): bool
    {
        $middleware = $route->gatherMiddleware();

        return in_array('web', $middleware, true) && array_filter($middleware, fn ($m) => $m === 'auth'
            || (is_string($m) && str_starts_with($m, 'auth:') && $m !== 'auth:sanctum')) !== [];
    }

    /** A signed-in web route without any `role:` or `can:` middleware. */
    public static function isUnruled(Route $route): bool
    {
        if (! self::requiresSignIn($route) || self::roleRules($route) !== []) {
            return false;
        }
        foreach ($route->gatherMiddleware() as $middleware) {
            if (is_string($middleware) && (str_starts_with($middleware, 'can:') || str_starts_with($middleware, Authorize::class.':'))) {
                return false;
            }
        }

        return true;
    }

    /** Listed in config/route_access.php as open to all or as unguarded debt. */
    public static function isListed(Route $route): bool
    {
        $key = self::key($route);

        return in_array($key, config('route_access.open_to_all', []), true)
            || in_array($key, config('route_access.unguarded_debt', []), true);
    }

    /**
     * `can:ability,Model` middleware whose arguments are class names (the
     * route-model ones need a real request and are left to the request).
     *
     * @return list<array{0: string, 1: list<string>}>
     */
    public static function abilityRules(Route $route): array
    {
        $rules = [];
        foreach ($route->gatherMiddleware() as $middleware) {
            if (! is_string($middleware)) {
                continue;
            }
            foreach (['can:', Authorize::class.':'] as $prefix) {
                if (str_starts_with($middleware, $prefix)) {
                    $parts = array_map('trim', explode(',', substr($middleware, strlen($prefix))));
                    $ability = array_shift($parts);
                    if (array_filter($parts, fn ($arg) => ! class_exists($arg)) === []) {
                        $rules[] = [$ability, $parts];
                    }
                }
            }
        }

        return $rules;
    }

    /**
     * Every `role:` middleware on the route (nested groups can stack more
     * than one; all of them must pass).
     *
     * @return list<list<string>>
     */
    public static function roleRules(Route $route): array
    {
        $key = implode('|', $route->methods()).' '.$route->uri();
        if (! array_key_exists($key, self::$cache)) {
            $rules = [];
            foreach ($route->gatherMiddleware() as $middleware) {
                if (! is_string($middleware)) {
                    continue;
                }
                foreach (['role:', RoleMiddleware::class.':'] as $prefix) {
                    if (str_starts_with($middleware, $prefix)) {
                        $rules[] = array_values(array_filter(array_map('trim', explode(',', substr($middleware, strlen($prefix))))));
                    }
                }
            }
            self::$cache[$key] = $rules;
        }

        return self::$cache[$key];
    }
}
