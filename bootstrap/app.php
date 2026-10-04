<?php

use App\Http\Middleware\AdminIpWhitelistMiddleware;
use App\Http\Middleware\CheckTwoFactor;
use App\Http\Middleware\CorrelationIdMiddleware;
use App\Http\Middleware\EnsureElevatedSessionMiddleware;
use App\Http\Middleware\EnsurePasswordChanged;
use App\Http\Middleware\HoneypotTrapMiddleware;
use App\Http\Middleware\IdempotencyMiddleware;
use App\Http\Middleware\InvisibleHoneypotMiddleware;
use App\Http\Middleware\PerformanceLogger;
use App\Http\Middleware\RestrictImpersonation;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SessionFingerprintMiddleware;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\SystemLockdownMiddleware;
use App\Http\Middleware\UpdateActiveSession;
use App\Http\Middleware\VerifyMfa;
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
            'mfa' => VerifyMfa::class,
            'admin.ip' => AdminIpWhitelistMiddleware::class,
            'elevated' => EnsureElevatedSessionMiddleware::class,
            'idempotency' => IdempotencyMiddleware::class,
        ]);

        $middleware->prepend(InvisibleHoneypotMiddleware::class);
        $middleware->prepend(HoneypotTrapMiddleware::class);
        $middleware->append(SecurityHeaders::class);

        $middleware->appendToGroup('web', [
            EnsurePasswordChanged::class,
            RestrictImpersonation::class,
            CheckTwoFactor::class,
            UpdateActiveSession::class,
            SetLocale::class,
            PerformanceLogger::class,
            SystemLockdownMiddleware::class,
            CorrelationIdMiddleware::class,
            SessionFingerprintMiddleware::class,
        ]);

        $middleware->appendToGroup('api', [
            // Messages in the language the user picked (see SetLocale).
            SetLocale::class,
            IdempotencyMiddleware::class,
            // The mobile app talks only to the API, so without these its
            // requests never reached the APM latency/availability figures.
            PerformanceLogger::class,
            CorrelationIdMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
