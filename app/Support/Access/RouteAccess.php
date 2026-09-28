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
