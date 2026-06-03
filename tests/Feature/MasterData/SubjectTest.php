<?php

/** @var TestCase|stdClass $this */

use App\Models\Subject;
use App\Models\User;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->student = User::factory()->create(['role' => 'student']);
});

// ── INDEX ──────────────────────────────────────────────────────────────

test('admin can view subjects index page', function () {
    Subject::factory()->count(3)->create();

    $response = $this->actingAs($this->admin)->get(route('subjects.index'));

    $response->assertStatus(200);
    $response->assertViewIs('subjects.index');
    $response->assertViewHas('subjects');
});

// ── STORE ──────────────────────────────────────────────────────────────

test('admin can create a new subject', function () {
    $response = $this->actingAs($this->admin)->post(route('subjects.store'), [
        'name' => 'Matematika',
        'code' => 'MTK-01',
        'category' => 'Nasional',
        'description' => 'Pelajaran Matematika kelas X',
    ]);

    $response->assertRedirect(route('subjects.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('subjects', [
        'name' => 'Matematika',
        'code' => 'MTK-01',
        'category' => 'Nasional',
    ]);
});

test('subject code must be unique', function () {
    Subject::factory()->create(['code' => 'MTK-01']);

    $response = $this->actingAs($this->admin)->post(route('subjects.store'), [
        'name' => 'Matematika 2',
        'code' => 'MTK-01', // Duplicate
        'category' => 'Nasional',
    ]);

    $response->assertSessionHasErrors('code');
});

test('subject store validates category enum', function () {
    $response = $this->actingAs($this->admin)->post(route('subjects.store'), [
        'name' => 'Invalid Subject',
        'code' => 'INV-01',
        'category' => 'InvalidCategory',
    ]);

    $response->assertSessionHasErrors('category');
});

test('subject store validates required fields', function () {
    $response = $this->actingAs($this->admin)->post(route('subjects.store'), []);

    $response->assertSessionHasErrors(['name', 'code', 'category']);
});

// ── UPDATE ─────────────────────────────────────────────────────────────

test('admin can update an existing subject', function () {
    $subject = Subject::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($this->admin)->put(route('subjects.update', $subject), [
        'name' => 'Fisika',
        'code' => $subject->code,
        'category' => 'Nasional',
    ]);

    $response->assertRedirect(route('subjects.index'));
    $response->assertSessionHas('success');

    $subject->refresh();
    expect($subject->name)->toBe('Fisika');
});

test('subject code unique constraint ignores self on update', function () {
    $subject = Subject::factory()->create(['code' => 'FIS-01']);

    $response = $this->actingAs($this->admin)->put(route('subjects.update', $subject), [
        'name' => 'Fisika Updated',
        'code' => 'FIS-01', // Same code, same record — should be fine
        'category' => 'Nasional',
    ]);

    $response->assertRedirect(route('subjects.index'));
    $response->assertSessionHas('success');
});

// ── DESTROY ────────────────────────────────────────────────────────────

test('admin can delete a subject', function () {
    $subject = Subject::factory()->create();

    $response = $this->actingAs($this->admin)->delete(route('subjects.destroy', $subject));

    $response->assertRedirect(route('subjects.index'));
    $response->assertSessionHas('success');
});
