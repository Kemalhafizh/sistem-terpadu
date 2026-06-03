<?php

/** @var TestCase|stdClass $this */

use App\Models\User;
use Illuminate\Auth\SessionGuard;
use Tests\TestCase;

// ── LOGIN ─────────────────────────────────────────────────────────────

test('user can login via API and receive token', function () {
    $user = User::factory()->create([
        'email' => 'test@sekolah.com',
        'role' => 'student',
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => 'test@sekolah.com',
        'password' => 'password',
        'device_name' => 'PHPUnit',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'token',
                'user' => ['name', 'email', 'role'],
            ],
        ]);

    expect($response->json('data.user.email'))->toBe('test@sekolah.com');
    expect($response->json('success'))->toBeTrue();
});

test('login fails with invalid credentials', function () {
    $response = $this->postJson('/api/v1/login', [
        'email' => 'nonexistent@sekolah.com',
        'password' => 'wrongpassword',
        'device_name' => 'PHPUnit',
    ]);

    $response->assertStatus(401);
    expect($response->json('success'))->toBeFalse();
});

test('login validates required fields', function () {
    $response = $this->postJson('/api/v1/login', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password', 'device_name']);
});

// ── PROFILE ───────────────────────────────────────────────────────────

test('authenticated user can get their profile', function () {
    $user = User::factory()->create(['role' => 'student']);
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/profile');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => ['name', 'email', 'role'],
        ]);

    expect($response->json('data.email'))->toBe($user->email);
});

test('unauthenticated user cannot get profile', function () {
    $response = $this->getJson('/api/v1/profile');

    $response->assertStatus(401);
});

// ── LOGOUT ────────────────────────────────────────────────────────────

test('authenticated user can logout (revoke token)', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/logout');

    $response->assertStatus(200);
    expect($response->json('success'))->toBeTrue();

    // Verify token is deleted from database
    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_id' => $user->id,
        'tokenable_type' => User::class,
    ]);

    // Force forget the authenticated user in memory for the next request in this test
    /** @var SessionGuard $guard */
    $guard = auth();
    $guard->forgetUser();

    // Token should be revoked — subsequent request should fail
    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/profile')
        ->assertStatus(401);
});
