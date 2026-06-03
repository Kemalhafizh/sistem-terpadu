<?php

/** @var TestCase|stdClass $this */

use App\Models\Classroom;
use App\Models\User;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->guru = User::factory()->create(['role' => 'guru']);
    $this->student = User::factory()->create(['role' => 'student']);
});

// ── INDEX ──────────────────────────────────────────────────────────────

test('admin can view classrooms index page', function () {
    Classroom::factory()->count(3)->create();

    $response = $this->actingAs($this->admin)->get(route('classrooms.index'));

    $response->assertStatus(200);
    $response->assertViewIs('classrooms.index');
    $response->assertViewHas('classrooms');
});

test('student cannot access classrooms index', function () {
    $response = $this->actingAs($this->student)->get(route('classrooms.index'));

    $response->assertStatus(403);
});

// ── CREATE ─────────────────────────────────────────────────────────────

test('admin can view create classroom form', function () {
    $response = $this->actingAs($this->admin)->get(route('classrooms.create'));

    $response->assertStatus(200);
    $response->assertViewIs('classrooms.create');
});

// ── STORE ──────────────────────────────────────────────────────────────

test('admin can create a new classroom', function () {
    $response = $this->actingAs($this->admin)->post(route('classrooms.store'), [
        'name' => 'X IPA 1',
        'grade_level' => 'X',
        'major' => 'IPA',
        'teacher_id' => $this->guru->id,
        'academic_year' => '2025/2026',
    ]);

    $response->assertRedirect(route('classrooms.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('classrooms', [
        'name' => 'X IPA 1',
        'grade_level' => 'X',
        'major' => 'IPA',
    ]);
});

test('classroom store validates required fields', function () {
    $response = $this->actingAs($this->admin)->post(route('classrooms.store'), []);

    $response->assertSessionHasErrors(['name', 'grade_level', 'major', 'academic_year']);
});

test('classroom store validates grade_level enum', function () {
    $response = $this->actingAs($this->admin)->post(route('classrooms.store'), [
        'name' => 'Invalid Class',
        'grade_level' => 'XIII', // Invalid
        'major' => 'IPA',
        'academic_year' => '2025/2026',
    ]);

    $response->assertSessionHasErrors('grade_level');
});

// ── UPDATE ─────────────────────────────────────────────────────────────

test('admin can update an existing classroom', function () {
    $classroom = Classroom::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($this->admin)->put(route('classrooms.update', $classroom->id), [
        'name' => 'XII IPS 2',
        'grade_level' => 'XII',
        'major' => 'IPS',
        'academic_year' => '2025/2026',
    ]);

    $response->assertRedirect(route('classrooms.index'));
    $response->assertSessionHas('success');

    $classroom->refresh();
    expect($classroom->name)->toBe('XII IPS 2');
    expect($classroom->grade_level)->toBe('XII');
});

// ── DESTROY ────────────────────────────────────────────────────────────

test('admin can delete a classroom', function () {
    $classroom = Classroom::factory()->create();

    $response = $this->actingAs($this->admin)->delete(route('classrooms.destroy', $classroom->id));

    $response->assertRedirect(route('classrooms.index'));
    $response->assertSessionHas('success');
});
