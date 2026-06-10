<?php

/** @var TestCase|stdClass $this */

use App\Models\Announcement;
use App\Models\User;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->guru = User::factory()->create(['role' => 'guru']);
    $this->student = User::factory()->create(['role' => 'student']);
});

// ── INDEX ──────────────────────────────────────────────────────────────

test('admin can view announcements index', function () {
    Announcement::factory()->count(3)->create(['created_by' => $this->admin->id]);

    $response = $this->actingAs($this->admin)->get(route('announcements.index'));

    $response->assertStatus(200);
    $response->assertViewIs('announcements.index');
    $response->assertViewHas('announcements');
});

// ── STORE ──────────────────────────────────────────────────────────────

test('admin can create a new announcement', function () {
    $response = $this->actingAs($this->admin)->post(route('announcements.store'), [
        'title' => 'Libur Nasional',
        'content' => 'Sekolah libur pada tanggal 1 Juni 2026.',
        'target_role' => null,
        'priority' => 'normal',
        'category' => 'umum',
    ]);

    $response->assertRedirect(route('announcements.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('announcements', [
        'title' => 'Libur Nasional',
        'priority' => 'normal',
        'created_by' => $this->admin->id,
        'is_active' => true,
    ]);
});

test('admin can create targeted announcement', function () {
    $response = $this->actingAs($this->admin)->post(route('announcements.store'), [
        'title' => 'Info Guru',
        'content' => 'Rapat guru hari Jumat.',
        'target_role' => 'guru',
        'priority' => 'urgent',
        'category' => 'akademik',
    ]);

    $response->assertRedirect(route('announcements.index'));

    $this->assertDatabaseHas('announcements', [
        'title' => 'Info Guru',
        'target_role' => 'guru',
        'priority' => 'urgent',
    ]);
});

test('announcement store validates required fields', function () {
    $response = $this->actingAs($this->admin)->post(route('announcements.store'), []);

    $response->assertSessionHasErrors(['title', 'content', 'priority', 'category']);
});

test('announcement store validates priority enum', function () {
    $response = $this->actingAs($this->admin)->post(route('announcements.store'), [
        'title' => 'Test',
        'content' => 'Test content',
        'priority' => 'invalid_priority',
        'category' => 'umum',
    ]);

    $response->assertSessionHasErrors('priority');
});

// ── UPDATE ─────────────────────────────────────────────────────────────

test('admin can update an announcement', function () {
    $announcement = Announcement::factory()->create([
        'title' => 'Old Title',
        'created_by' => $this->admin->id,
    ]);

    $response = $this->actingAs($this->admin)->put(route('announcements.update', $announcement), [
        'title' => 'Updated Title',
        'content' => 'Updated content.',
        'priority' => 'urgent',
        'category' => 'akademik',
    ]);

    $response->assertRedirect(route('announcements.index'));
    $response->assertSessionHas('success');

    $announcement->refresh();
    expect($announcement->title)->toBe('Updated Title');
    expect($announcement->priority)->toBe('urgent');
});

// ── DESTROY ────────────────────────────────────────────────────────────

test('admin can delete an announcement', function () {
    $announcement = Announcement::factory()->create(['created_by' => $this->admin->id]);

    $response = $this->actingAs($this->admin)->delete(route('announcements.destroy', $announcement));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
});
