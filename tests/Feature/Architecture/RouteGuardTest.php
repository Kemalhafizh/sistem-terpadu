<?php

use App\Models\User;
use App\Support\Access\RouteAccess;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

/*
 * FASE 118.5.1: every signed-in web route carries `role:` or `can:`, or is
 * listed in config/route_access.php. Before FASE 118, 17 route files were
 * guarded by `auth` alone, so students and parents could open (and change)
 * treasury, crisis and HR pages, and the menus showed them those links.
 */

function unruledRouteKeys(): array
{
    return collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($route) => RouteAccess::isUnruled($route))
        ->map(fn ($route) => RouteAccess::key($route))
        ->values()->all();
}

test('setiap route web ber-auth punya role: atau can:, kecuali yang terdaftar', function () {
    $listed = [...config('route_access.open_to_all'), ...config('route_access.unguarded_debt')];

    expect(array_values(array_diff(unruledRouteKeys(), $listed)))->toBe([]);
});

test('daftar whitelist dan utang hanya berisi route yang masih ada dan masih tanpa aturan', function () {
    // A route that got its role (or was removed) must leave the list, so the debt only shrinks.
    $unruled = unruledRouteKeys();

    expect(array_values(array_diff(config('route_access.open_to_all'), $unruled)))->toBe([])
        ->and(array_values(array_diff(config('route_access.unguarded_debt'), $unruled)))->toBe([])
        ->and(array_intersect(config('route_access.open_to_all'), config('route_access.unguarded_debt')))->toBe([]);
});

/**
 * "METHODS uri" of every route the FASE 45-62 route files register, loaded
 * into a router of their own. FASE 119 deletes some of these files outright
 * (recruitment, omnichannel, adaptive, extended_diamond, ...); they leave this
 * list with them.
 */
function fase45to62RouteKeys(): array
{
    $files = ['treasury', 'crisis', 'inclusive_education', 'islamic_tech'];
    $router = new Router(app('events'), app());
    $original = Route::getFacadeRoot();
    Route::swap($router);
    try {
        foreach ($files as $file) {
            require base_path("routes/{$file}.php");
        }
    } finally {
        Route::swap($original);
    }

    return collect($router->getRoutes()->getRoutes())->map(fn ($route) => RouteAccess::key($route))->all();
}

test('route modul FASE 45-62 semuanya berperan, hanya form lapor bullying yang terbuka', function () {
    $modules = fase45to62RouteKeys();

    expect(count($modules))->toBeGreaterThan(50)
        ->and(array_values(array_intersect($modules, unruledRouteKeys())))->toBe(['GET crisis/bullying/report', 'POST crisis/bullying/report'])
        ->and(array_values(array_intersect($modules, config('route_access.unguarded_debt'))))->toBe([]);
});

test('RouteAccess menolak route tanpa aturan yang tidak terdaftar, kecuali untuk superadmin', function () {
    Route::middleware(['web', 'auth'])->get('/__fase118-unruled', fn () => 'ok')->name('fase118.unruled');
    Route::getRoutes()->refreshNameLookups();

    $student = User::factory()->create(['role' => 'student']);

    expect(RouteAccess::allowsRoute($student, 'fase118.unruled'))->toBeFalse()
        ->and(RouteAccess::allowsRoute(User::factory()->create(['role' => 'admin']), 'fase118.unruled'))->toBeFalse()
        ->and(RouteAccess::allowsRoute(User::factory()->create(['role' => 'superadmin']), 'fase118.unruled'))->toBeTrue()
        // Listed as open to all: still open.
        ->and(RouteAccess::allowsRoute($student, 'dashboard'))->toBeTrue()
        ->and(RouteAccess::allowsRoute($student, 'crisis.bullying.report'))->toBeTrue();
});
