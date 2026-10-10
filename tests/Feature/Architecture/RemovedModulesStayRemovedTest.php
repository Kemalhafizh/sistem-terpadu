<?php

use Illuminate\Support\Facades\Route;

/*
 * FASE 119: modules cut from the school ERP stay cut. Each PR of the debloat
 * adds the URI prefixes it removed; a route that comes back under one of them
 * fails here.
 */

dataset('removed prefixes', [
    // PR 1: developer tools and showcase technology.
    'deploy/', 'developer/', 'ux/', 'etl', 'plugins', 'api-docs', 'graphql', 'api-clients', 'api-stats',
    'query-performance', 'asset-performance', 'load-test', 'scaling',
    'admin/observability', 'admin/queue-monitor', 'admin/report-builder', 'admin/uat', 'admin/disaster-recovery',
    'admin/analytics/edw', 'admin/vendor-sla', 'admin/webhooks', 'admin/bpa',
    'api/v1/infrastructure', 'api/v1/debloat', 'api/v1/design', 'api/v1/iot/energy', 'api/v1/blockchain/did',
    'api/v1/certificates/verify',
]);

test('route modul yang sudah dipotong tidak muncul lagi', function (string $prefix) {
    $back = collect(Route::getRoutes()->getRoutes())
        ->map(fn ($route) => $route->uri())
        ->filter(fn (string $uri) => $uri === rtrim($prefix, '/') || str_starts_with($uri, $prefix))
        ->values()->all();

    expect($back)->toBe([]);
})->with('removed prefixes');
