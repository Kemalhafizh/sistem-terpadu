<?php

use App\Http\Middleware\CheckTwoFactor;
use App\Http\Middleware\EnsurePasswordChanged;
use App\Http\Middleware\RestrictImpersonation;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    // Registered separately (instead of passing `channels:` to withRouting())
    // so /broadcasting/auth is guarded by auth:sanctum, not the default `web`
    // (session-cookie) middleware group — the mobile app authenticates with a
    // Sanctum bearer token, which the `web` group's session guard can't see,
    // so private-channel WebSocket subscriptions (a student's own attendance
    // channel, a parent-child chat channel) would 401 under the framework's
    // default wiring.
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        ['middleware' => ['auth:sanctum']],
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'mfa' => \App\Http\Middleware\VerifyMfa::class,
            'admin.ip' => \App\Http\Middleware\AdminIpWhitelistMiddleware::class,
            'elevated' => \App\Http\Middleware\EnsureElevatedSessionMiddleware::class,
            'idempotency' => \App\Http\Middleware\IdempotencyMiddleware::class,
        ]);

        $middleware->prepend(\App\Http\Middleware\InvisibleHoneypotMiddleware::class);
        $middleware->prepend(\App\Http\Middleware\HoneypotTrapMiddleware::class);
        $middleware->append(SecurityHeaders::class);

        $middleware->appendToGroup('web', [
            EnsurePasswordChanged::class,
            RestrictImpersonation::class,
            CheckTwoFactor::class,
            \App\Http\Middleware\UpdateActiveSession::class,
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\PerformanceLogger::class,
            \App\Http\Middleware\SystemLockdownMiddleware::class,
            \App\Http\Middleware\CorrelationIdMiddleware::class,
            \App\Http\Middleware\SessionFingerprintMiddleware::class,
        ]);

        $middleware->appendToGroup('api', [
            \App\Http\Middleware\IdempotencyMiddleware::class,
            // The mobile app talks only to the API, so without these its
            // requests never reached the APM latency/availability figures.
            \App\Http\Middleware\PerformanceLogger::class,
            \App\Http\Middleware\CorrelationIdMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
