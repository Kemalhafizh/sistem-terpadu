<?php

use App\Models\User;
use App\Support\Access\MobileCapabilities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;

/*
 * The app draws its menus from GET me/capabilities. These tests hold that list
 * to what the server enforces: a feature an account does not get is refused
 * when called directly (no bypass by typing the URL or a deep link), and a
 * feature it does get is never stopped at the role gate.
 */

const CAPABILITY_ROLES = [
    'student', 'siswa', 'parent', 'orang_tua', 'guru', 'bk', 'wali_kelas',
    'kepala_sekolah', 'waka_kurikulum', 'waka_kesiswaan', 'staf_tu',
    'petugas_uks', 'admin', 'superadmin', 'alumni', 'merchant',
];

const ROLE_GATE_MESSAGES = ['Anda tidak memiliki akses ke halaman ini.', 'This action is unauthorized.'];

function capabilityUser(string $role): User
{
    return User::factory()->create(['role' => $role, 'must_change_password' => false]);
}

function capabilityKeys(User $user): array
{
    return array_column(MobileCapabilities::for($user), 'key');
}

function callFeature(object $test, string $endpoint)
{
    [$method, $uri] = MobileCapabilities::split($endpoint);

    return $test->json($method, '/'.$uri, []);
}

test('every feature points at a real, signed-in API route', function () {
    $groups = array_keys(MobileCapabilities::groups());

    foreach (MobileCapabilities::registry() as $key => $feature) {
        expect($feature['group'])->toBeIn($groups, "$key: unknown group");
        expect(array_diff($feature['hardware'] ?? [], ['camera', 'location', 'microphone', 'audio_output', 'nfc', 'biometric']))
            ->toBe([], "$key: unknown hardware");

        [$method, $uri] = MobileCapabilities::split($feature['endpoint']);
        $route = Route::getRoutes()->match(Request::create('/'.$uri, $method));
        expect(in_array('auth:sanctum', $route->gatherMiddleware(), true))->toBeTrue("$key: {$feature['endpoint']} is not behind sign-in");
    }
});

test('the capabilities endpoint needs a signed-in user', function () {
    $this->getJson('/api/v1/me/capabilities')->assertUnauthorized();
});

test('the endpoint returns the features this account has', function () {
    $student = capabilityUser('student');
    Sanctum::actingAs($student);

    $response = $this->getJson('/api/v1/me/capabilities')->assertOk()
        ->assertJsonStructure(['success', 'data' => ['role', 'version', 'groups' => [['key', 'title']], 'features' => [['key', 'title', 'group', 'hardware']]]]);

    expect($response->json('data.role'))->toBe('student')
        ->and(array_column($response->json('data.features'), 'key'))->toBe(capabilityKeys($student));
});

test('a hidden feature is refused when called directly', function () {
    foreach (CAPABILITY_ROLES as $role) {
        $user = capabilityUser($role);
        $granted = capabilityKeys($user);
        Sanctum::actingAs($user);

        foreach (MobileCapabilities::registry() as $key => $feature) {
            if (in_array($key, $granted, true)) {
                continue;
            }
            $status = callFeature($this, $feature['endpoint'])->status();
            expect($status)->toBe(403, "$role reached hidden feature $key ({$feature['endpoint']})");
        }
    }
});

test('a shown feature is never stopped at the role gate', function () {
    foreach (CAPABILITY_ROLES as $role) {
        $user = capabilityUser($role);
        Sanctum::actingAs($user);

        foreach (MobileCapabilities::for($user) as $feature) {
            $endpoint = MobileCapabilities::registry()[$feature['key']]['endpoint'];
            $response = callFeature($this, $endpoint);
            $roleGate = $response->status() === 403 && in_array($response->json('message'), ROLE_GATE_MESSAGES, true);
            expect($roleGate)->toBeFalse("$role was shown {$feature['key']} but the route refuses it");
        }
    }
});

test('each role sees its own features and nobody else\'s', function () {
    $prefixed = fn (array $keys, string $prefix) => array_values(array_filter($keys, fn ($k) => str_starts_with($k, $prefix)));

    $student = capabilityKeys(capabilityUser('student'));
    expect($student)->toContain('student.schedule', 'student.grades', 'attendance.gps', 'attendance.class_scan')
        ->and($prefixed($student, 'teacher.'))->toBe([])
        ->and($prefixed($student, 'parent.'))->toBe([])
        ->and($prefixed($student, 'admin.'))->toBe([]);

    $teacher = capabilityKeys(capabilityUser('guru'));
    expect($teacher)->toContain('teacher.sessions', 'teacher.journal', 'teacher.assessments')
        ->and($prefixed($teacher, 'student.'))->toBe([])
        ->and($prefixed($teacher, 'parent.'))->toBe([])
        ->and($prefixed($teacher, 'admin.'))->toBe([]);

    $parent = capabilityKeys(capabilityUser('parent'));
    expect($parent)->toContain('parent.children', 'parent.messages')
        ->and($prefixed($parent, 'student.'))->toBe([])
        ->and($prefixed($parent, 'teacher.'))->toBe([]);

    // Admin: school operator plus the leadership dashboards — none of the
    // personal student, parent or teacher features.
    $admin = capabilityKeys(capabilityUser('admin'));
    expect($admin)->toContain('admin.dashboard', 'admin.executive', 'admin.class_sessions')
        ->and($prefixed($admin, 'student.'))->toBe([])
        ->and($prefixed($admin, 'parent.'))->toBe([])
        ->and($prefixed($admin, 'teacher.'))->toBe([]);

    expect(capabilityKeys(capabilityUser('kepala_sekolah')))->toContain('admin.dashboard', 'admin.executive');
});

test('only superadmin has every feature', function () {
    $all = array_keys(MobileCapabilities::registry());

    expect(capabilityKeys(capabilityUser('superadmin')))->toBe($all);
    foreach (array_diff(CAPABILITY_ROLES, ['superadmin']) as $role) {
        expect(count(capabilityKeys(capabilityUser($role))))->toBeLessThan(count($all), "$role has everything");
    }
});
