<?php

/** @var TestCase|stdClass $this */

use App\Models\Student;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

beforeEach(function () {
    $this->user = User::factory()->create([
        'password' => bcrypt('password123'),
        'role' => 'student',
    ]);
});

test('api login works with correct credentials', function () {
    $response = $this->postJson('/api/v1/login', [
        'email' => $this->user->email,
        'password' => 'password123',
        'device_name' => 'test-device',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['token', 'user'],
        ]);
});

test('api login fails with incorrect credentials', function () {
    $response = $this->postJson('/api/v1/login', [
        'email' => $this->user->email,
        'password' => 'wrong-password',
        'device_name' => 'test-device',
    ]);

    $response->assertStatus(401);
});

test('authenticated user can access profile', function () {
    Sanctum::actingAs($this->user);

    $response = $this->getJson('/api/v1/profile');

    $response->assertStatus(200)
        ->assertJsonPath('data.email', $this->user->email);
});

test('authenticated user can list students', function () {
    Sanctum::actingAs($this->user);

    Student::factory()->count(3)->create();

    $response = $this->getJson('/api/v1/students');

    $response->assertStatus(200)
        ->assertJsonStructure(['data']);
});

test('unauthenticated user cannot access profile', function () {
    $response = $this->getJson('/api/v1/profile');

    $response->assertStatus(401);
});
