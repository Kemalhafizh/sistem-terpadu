<?php

/** @var TestCase|stdClass $this */

/**
 * Role-Based Access Control Tests.
 *
 * Memastikan bahwa setiap role hanya bisa mengakses route yang ditujukan.
 * Test ini bersifat "negative test" — menguji apa yang TIDAK boleh diakses.
 */

use App\Models\User;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->guru = User::factory()->create(['role' => 'guru']);
    $this->student = User::factory()->create(['role' => 'student']);
    $this->parent = User::factory()->create(['role' => 'parent']);
});

// ── GUEST ACCESS ──────────────────────────────────────────────────────

test('guest is redirected to login for protected routes', function () {
    $protectedRoutes = [
        route('dashboard'),
        route('users.index'),
        route('classrooms.index'),
        route('subjects.index'),
    ];

    foreach ($protectedRoutes as $url) {
        $this->get($url)->assertRedirect(route('login'));
    }
});

// ── STUDENT ACCESS RESTRICTIONS ───────────────────────────────────────

test('student cannot access admin-level routes', function () {
    $adminRoutes = [
        route('users.index'),
        route('users.create'),
    ];

    foreach ($adminRoutes as $url) {
        $this->actingAs($this->student)->get($url)->assertStatus(403);
    }
});

test('student cannot access finance routes', function () {
    $response = $this->actingAs($this->student)->get(route('finance.billings.index'));

    $response->assertStatus(403);
});

test('student cannot access classroom management', function () {
    $response = $this->actingAs($this->student)->get(route('classrooms.index'));

    $response->assertStatus(403);
});

test('student cannot access subject management', function () {
    $response = $this->actingAs($this->student)->get(route('subjects.index'));

    $response->assertStatus(403);
});

// ── GURU ACCESS RESTRICTIONS ──────────────────────────────────────────

test('guru cannot access user management', function () {
    $response = $this->actingAs($this->guru)->get(route('users.index'));

    $response->assertStatus(403);
});

// ── PARENT ACCESS RESTRICTIONS ────────────────────────────────────────

test('parent cannot access classroom management', function () {
    $response = $this->actingAs($this->parent)->get(route('classrooms.index'));

    $response->assertStatus(403);
});

// ── ADMIN CAN ACCESS ──────────────────────────────────────────────────

test('admin can access dashboard', function () {
    $response = $this->actingAs($this->admin)->get(route('dashboard'));

    $response->assertStatus(200);
});

test('admin can access user management', function () {
    $response = $this->actingAs($this->admin)->get(route('users.index'));

    $response->assertStatus(200);
});

test('admin can access classroom management', function () {
    $response = $this->actingAs($this->admin)->get(route('classrooms.index'));

    $response->assertStatus(200);
});

test('admin can access subject management', function () {
    $response = $this->actingAs($this->admin)->get(route('subjects.index'));

    $response->assertStatus(200);
});

test('admin can access student management', function () {
    $response = $this->actingAs($this->admin)->get(route('students.index'));

    $response->assertStatus(200);
});
