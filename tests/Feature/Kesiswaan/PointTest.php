<?php

/** @var TestCase|stdClass $this */

use App\Models\PointCategory;
use App\Models\Student;
use App\Models\StudentPointLog;
use App\Models\User;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->bk = User::factory()->create(['role' => 'bk']);
    $this->student = Student::factory()->create();
});

// ── INDEX ──────────────────────────────────────────────────────────────

test('admin can view points index page', function () {
    $response = $this->actingAs($this->admin)->get(route('points.index'));

    $response->assertStatus(200);
    $response->assertViewIs('points.index');
    $response->assertViewHas('students');
});

// ── SHOW ───────────────────────────────────────────────────────────────

test('admin can view point history for a specific student', function () {
    $response = $this->actingAs($this->admin)->get(route('points.show', $this->student));

    $response->assertStatus(200);
    $response->assertViewIs('points.show');
    $response->assertViewHas('student');
    $response->assertViewHas('logs');
});

// ── STORE (Manual Entry) ──────────────────────────────────────────────

test('admin can add manual penalty points', function () {
    $response = $this->actingAs($this->admin)->post(route('points.store'), [
        'student_id' => $this->student->id,
        'points' => 15,
        'type' => 'penalty',
        'description' => 'Terlambat masuk kelas lebih dari 15 menit',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('student_point_logs', [
        'student_id' => $this->student->id,
        'points' => 15,
        'type' => 'penalty',
        'created_by' => $this->admin->id,
    ]);
});

test('admin can add reward points', function () {
    $response = $this->actingAs($this->admin)->post(route('points.store'), [
        'student_id' => $this->student->id,
        'points' => 5,
        'type' => 'reward',
        'description' => 'Juara 1 Lomba Sains',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('student_point_logs', [
        'student_id' => $this->student->id,
        'type' => 'reward',
        'points' => 5,
    ]);
});

// ── STORE (From Category) ─────────────────────────────────────────────

test('store from category uses default points and type from category', function () {
    $category = PointCategory::create([
        'name' => 'Bolos',
        'type' => 'penalty',
        'default_points' => 25,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->admin)->post(route('points.store'), [
        'student_id' => $this->student->id,
        'point_category_id' => $category->id,
        'description' => 'Bolos pelajaran PKN',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('student_point_logs', [
        'student_id' => $this->student->id,
        'point_category_id' => $category->id,
        'points' => 25,
        'type' => 'penalty',
    ]);
});

// ── VALIDATION ────────────────────────────────────────────────────────

test('store validates required fields', function () {
    $response = $this->actingAs($this->admin)->post(route('points.store'), []);

    $response->assertSessionHasErrors(['student_id', 'description']);
});

test('store validates type enum', function () {
    $response = $this->actingAs($this->admin)->post(route('points.store'), [
        'student_id' => $this->student->id,
        'points' => 10,
        'type' => 'invalid_type',
        'description' => 'Test',
    ]);

    $response->assertSessionHasErrors('type');
});

// ── DESTROY ────────────────────────────────────────────────────────────

test('admin can delete a point log entry', function () {
    $log = StudentPointLog::create([
        'student_id' => $this->student->id,
        'created_by' => $this->admin->id,
        'points' => 10,
        'type' => 'penalty',
        'description' => 'Terlambat',
    ]);

    $response = $this->actingAs($this->admin)->delete(route('points.destroy', $log));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('student_point_logs', ['id' => $log->id]);
});
