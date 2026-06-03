<?php

/** @var TestCase|stdClass $this */

use App\Models\User;
use Tests\TestCase;

beforeEach(function () {
    $this->superadmin = User::factory()->create(['role' => 'superadmin']);
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->guru = User::factory()->create(['role' => 'guru']);
    $this->studentUser = User::factory()->create(['role' => 'student']);
});

// ── TAKE (Start Impersonate) ──────────────────────────────────────────

test('admin can impersonate a non-admin user', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.impersonate.take', $this->guru));

    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('success');

    // Verify impersonation session is set
    expect(session('impersonate_by'))->toBe($this->admin->id);

    // Verify activity log was created
    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $this->admin->id,
        'event' => 'impersonate_start',
        'auditable_id' => $this->guru->id,
    ]);
});

test('admin cannot impersonate another admin', function () {
    $otherAdmin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($this->admin)->get(route('admin.impersonate.take', $otherAdmin));

    $response->assertRedirect();
    $response->assertSessionHas('error');

    // Session should NOT have impersonate data
    expect(session('impersonate_by'))->toBeNull();
});

test('non-admin cannot impersonate anyone', function () {
    $response = $this->actingAs($this->guru)->get(route('admin.impersonate.take', $this->studentUser));

    $response->assertStatus(403);
});

test('student cannot impersonate anyone', function () {
    $response = $this->actingAs($this->studentUser)->get(route('admin.impersonate.take', $this->guru));

    $response->assertStatus(403);
});

// ── LEAVE (End Impersonate) ───────────────────────────────────────────

test('admin can leave impersonation and return to own account', function () {
    // Setup: start impersonation first
    $this->actingAs($this->admin)->get(route('admin.impersonate.take', $this->guru));

    // Now leave
    $response = $this->actingAs($this->guru)
        ->withSession([
            'impersonate_by' => $this->admin->id,
            'impersonate_start_time' => now(),
        ])
        ->get(route('admin.impersonate.leave'));

    $response->assertRedirect(route('users.index'));
    $response->assertSessionHas('success');

    // Verify impersonation end activity log
    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $this->admin->id,
        'event' => 'impersonate_end',
    ]);
});

test('leave without active impersonation just redirects', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.impersonate.leave'));

    $response->assertRedirect(route('dashboard'));
});
