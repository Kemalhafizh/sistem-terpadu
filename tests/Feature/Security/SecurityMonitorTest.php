<?php

/** @var TestCase|stdClass $this */

use App\Models\ActiveSession;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Services\SecurityMonitorService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->guru = User::factory()->create([
        'password' => Hash::make('password123'),
        'role' => 'guru',
    ]);

    $this->student = User::factory()->create([
        'password' => Hash::make('password123'),
        'role' => 'student',
    ]);
});

test('auth attempts log successfully and failed logs record failure reason', function () {
    // Bind session store to the request so hasSession() returns true
    $request = request();
    $request->setLaravelSession(app('session.store'));

    // 1. Successful Login audit log test
    event(new Login('web', $this->admin, false));

    $this->assertDatabaseHas('login_attempts', [
        'email' => $this->admin->email,
        'successful' => true,
    ]);

    $this->assertDatabaseHas('active_sessions', [
        'user_id' => $this->admin->id,
    ]);

    // 2. Failed Login audit log test
    event(new Failed('web', $this->admin, [
        'email' => $this->admin->email,
        'password' => 'wrong-password',
    ]));

    $this->assertDatabaseHas('login_attempts', [
        'email' => $this->admin->email,
        'successful' => false,
        'failure_reason' => 'Kredensial tidak valid',
    ]);
});

test('detect brute force attempts from service', function () {
    $service = new SecurityMonitorService;

    // Before brute force
    expect($service->detectBruteForce())->toBeEmpty();

    // Create 5 failed logins from same IP
    for ($i = 0; $i < 5; $i++) {
        $attempt = new LoginAttempt([
            'email' => 'hacker@example.com',
            'ip_address' => '1.2.3.4',
            'successful' => false,
            'failure_reason' => 'Kredensial tidak valid',
        ]);
        $attempt->created_at = now();
        $attempt->save();
    }

    $bruteForceAlerts = $service->detectBruteForce();
    expect($bruteForceAlerts)->toHaveCount(1);
    expect($bruteForceAlerts[0]['ip_address'])->toBe('1.2.3.4');
    expect((int) $bruteForceAlerts[0]['total_failures'])->toBe(5);
});

test('unauthorized users cannot access security dashboard and sessions', function () {
    // 1. Student should be blocked (403)
    $this->actingAs($this->student)
        ->get(route('admin.security.dashboard'))
        ->assertStatus(403);

    $this->actingAs($this->student)
        ->get(route('admin.security.sessions'))
        ->assertStatus(403);

    // 2. Guru should be blocked (403)
    $this->actingAs($this->guru)
        ->get(route('admin.security.dashboard'))
        ->assertStatus(403);

    // 3. Admin should be allowed (200)
    $this->actingAs($this->admin)
        ->get(route('admin.security.dashboard'))
        ->assertStatus(200);

    $this->actingAs($this->admin)
        ->get(route('admin.security.sessions'))
        ->assertStatus(200);
});

test('middleware logs user out if session is terminated', function () {
    // 1. Enable force logout testing flag
    config(['security.test_force_logout' => true]);

    $this->actingAs($this->admin);

    // 2. Since active_sessions is empty in the database, the middleware will treat the session as terminated
    // and immediately redirect to login with the security warning
    $this->get(route('admin.security.dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('error');

    // User is now logged out
    expect(auth()->check())->toBeFalse();
});

test('sessions can be terminated remotely by admin', function () {
    // 1. Create target session
    $targetUser = User::factory()->create(['role' => 'guru']);
    $session = ActiveSession::create([
        'user_id' => $targetUser->id,
        'session_id' => 'target-session-id-123',
        'ip_address' => '192.168.1.5',
        'last_activity_at' => now(),
    ]);

    // 2. Admin deletes the target session
    $this->actingAs($this->admin)
        ->delete(route('admin.security.sessions.terminate', $session->id))
        ->assertRedirect();

    $this->assertDatabaseMissing('active_sessions', [
        'id' => $session->id,
    ]);
});
