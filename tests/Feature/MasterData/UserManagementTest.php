<?php

/** @var TestCase|stdClass $this */

use App\Models\User;
use Tests\TestCase;

beforeEach(function () {
    $this->superadmin = User::factory()->create(['role' => 'superadmin']);
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->guru = User::factory()->create(['role' => 'guru']);
});

// ── INDEX ──────────────────────────────────────────────────────────────

test('admin can view users index page', function () {
    $response = $this->actingAs($this->admin)->get(route('users.index'));

    $response->assertStatus(200);
    $response->assertViewIs('users.index');
    $response->assertViewHas('users');
});

// ── STORE ──────────────────────────────────────────────────────────────

test('admin can create a new user', function () {
    $response = $this->actingAs($this->admin)->post(route('users.store'), [
        'name' => 'New Teacher',
        'email' => 'newteacher@sekolah.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'guru',
    ]);

    $response->assertRedirect(route('users.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'name' => 'New Teacher',
        'email' => 'newteacher@sekolah.com',
        'role' => 'guru',
    ]);
});

test('user store validates email uniqueness', function () {
    User::factory()->create(['email' => 'exists@sekolah.com']);

    $response = $this->actingAs($this->admin)->post(route('users.store'), [
        'name' => 'Duplicate User',
        'email' => 'exists@sekolah.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'guru',
    ]);

    $response->assertSessionHasErrors('email');
});

// ── UPDATE ─────────────────────────────────────────────────────────────

test('admin can update user data', function () {
    $user = User::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($this->admin)->put(route('users.update', $user->id), [
        'name' => 'Updated Name',
        'email' => $user->email,
        'role' => $user->role,
    ]);

    $response->assertRedirect(route('users.index'));
    $response->assertSessionHas('success');

    $user->refresh();
    expect($user->name)->toBe('Updated Name');
});

test('admin can update user password optionally', function () {
    $user = User::factory()->create();
    $oldPassword = $user->password;

    $response = $this->actingAs($this->admin)->put(route('users.update', $user->id), [
        'name' => $user->name,
        'email' => $user->email,
        'role' => $user->role,
        'password' => 'newpassword123',
    ]);

    $response->assertRedirect(route('users.index'));

    $user->refresh();
    expect($user->password)->not->toBe($oldPassword);
});

// ── DESTROY ────────────────────────────────────────────────────────────

test('admin can delete a user', function () {
    $user = User::factory()->create(['role' => 'guru']);

    $response = $this->actingAs($this->admin)->delete(route('users.destroy', $user->id));

    $response->assertRedirect(route('users.index'));
    $response->assertSessionHas('success');

    // SoftDeletes — data harus tetap ada di database tapi soft-deleted
    $this->assertSoftDeleted('users', ['id' => $user->id]);
});
