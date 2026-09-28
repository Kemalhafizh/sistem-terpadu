<?php

use App\Http\Middleware\RoleMiddleware;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

/*
 * Only superadmin passes every role check. Admin (school operator) passes
 * when a route lists it, or lists a leadership role (principal / vice
 * principals) — never into student, parent or teacher features.
 */

test('the role rule matrix', function (?string $role, array $allowed, bool $expected) {
    expect(RoleMiddleware::allows($role, $allowed))->toBe($expected);
})->with([
    'superadmin passes anything' => ['superadmin', ['student'], true],
    'admin not into student features' => ['admin', ['student', 'siswa'], false],
    'admin not into parent features' => ['admin', ['parent', 'orang_tua'], false],
    'admin not into teacher features' => ['admin', ['guru', 'bk'], false],
    'admin where listed' => ['admin', ['admin', 'staf_tu'], true],
    'admin into principal dashboards' => ['admin', ['kepala_sekolah'], true],
    'admin into vice-principal dashboards' => ['admin', ['waka_kurikulum', 'waka_kesiswaan'], true],
    'exact role only' => ['guru', ['guru', 'bk'], true],
    'no substring matches' => ['guru_honorer', ['guru'], false],
    'waka is not admin' => ['waka_kurikulum', ['admin'], false],
    'no role, no access' => [null, ['student'], false],
    'empty role, no access' => ['', ['student'], false],
]);

test('only superadmin passes every gate ability', function () {
    Gate::define('rolebypass-test-ability', fn () => false);

    expect(Gate::forUser(User::factory()->create(['role' => 'superadmin']))->allows('rolebypass-test-ability'))->toBeTrue()
        ->and(Gate::forUser(User::factory()->create(['role' => 'admin']))->allows('rolebypass-test-ability'))->toBeFalse();
});

test('api requests without a session get 401 json, not a login redirect', function () {
    Route::middleware(['api', 'role:guru'])->get('api/v1/_rolebypass-probe', fn () => 'ok');

    $this->getJson('api/v1/_rolebypass-probe')->assertUnauthorized();
});
