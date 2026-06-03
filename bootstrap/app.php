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
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);

        $middleware->append(SecurityHeaders::class);

        $middleware->appendToGroup('web', [
            EnsurePasswordChanged::class,
            RestrictImpersonation::class,
            CheckTwoFactor::class,
            \App\Http\Middleware\UpdateActiveSession::class,
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\PerformanceLogger::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
