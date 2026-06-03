<?php

/** @var TestCase|stdClass $this */

use App\Models\Student;
use App\Models\User;
use Tests\TestCase;

beforeEach(function () {
    $this->guru = User::factory()->create(['role' => 'guru']);
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->student = Student::factory()->create();
    $this->studentUser = $this->student->user;
});

// ── SHOW ───────────────────────────────────────────────────────────────

test('guru can view tahfidz logs for a student', function () {
    $response = $this->actingAs($this->guru)->get(route('tahfidz.show', $this->student));

    $response->assertStatus(200);
    $response->assertViewIs('tahfidz.show');
    $response->assertViewHas('student');
    $response->assertViewHas('logs');
    $response->assertViewHas('surahs');
});

test('student can view their own tahfidz logs', function () {
    $response = $this->actingAs($this->studentUser)->get(route('tahfidz.show', $this->student));

    $response->assertStatus(200);
});

test('student cannot view other students tahfidz logs', function () {
    $otherStudent = Student::factory()->create();

    $response = $this->actingAs($this->studentUser)->get(route('tahfidz.show', $otherStudent));

    $response->assertStatus(403);
});

// ── STORE ──────────────────────────────────────────────────────────────

test('guru can record a tahfidz memorization log', function () {
    $response = $this->actingAs($this->guru)->post(route('tahfidz.store'), [
        'student_id' => $this->student->id,
        'surah_number' => 36,
        'surah_name' => 'Ya Sin',
        'from_ayat' => 1,
        'to_ayat' => 12,
        'type' => 'ziyadah',
        'grade' => 'A',
        'notes' => 'Tajwid baik',
        'date' => now()->format('Y-m-d'),
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('tahfidz_logs', [
        'student_id' => $this->student->id,
        'surah_number' => 36,
        'surah_name' => 'Ya Sin',
        'type' => 'ziyadah',
        'teacher_id' => $this->guru->id,
    ]);
});

test('guru can record murajaah type', function () {
    $response = $this->actingAs($this->guru)->post(route('tahfidz.store'), [
        'student_id' => $this->student->id,
        'surah_number' => 112,
        'surah_name' => 'Al-Ikhlas',
        'from_ayat' => 1,
        'to_ayat' => 4,
        'type' => 'murajaah',
        'date' => now()->format('Y-m-d'),
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('tahfidz_logs', [
        'type' => 'murajaah',
    ]);
});

// ── VALIDATION ────────────────────────────────────────────────────────

test('store validates required fields', function () {
    $response = $this->actingAs($this->guru)->post(route('tahfidz.store'), []);

    $response->assertSessionHasErrors([
        'student_id', 'surah_number', 'surah_name', 'from_ayat', 'to_ayat', 'type', 'date',
    ]);
});

test('store validates type enum (ziyadah or murajaah)', function () {
    $response = $this->actingAs($this->guru)->post(route('tahfidz.store'), [
        'student_id' => $this->student->id,
        'surah_number' => 1,
        'surah_name' => 'Al-Fatihah',
        'from_ayat' => 1,
        'to_ayat' => 7,
        'type' => 'invalid_type',
        'date' => now()->format('Y-m-d'),
    ]);

    $response->assertSessionHasErrors('type');
});

test('store validates surah_number range (1-114)', function () {
    $response = $this->actingAs($this->guru)->post(route('tahfidz.store'), [
        'student_id' => $this->student->id,
        'surah_number' => 999,
        'surah_name' => 'Invalid',
        'from_ayat' => 1,
        'to_ayat' => 1,
        'type' => 'ziyadah',
        'date' => now()->format('Y-m-d'),
    ]);

    $response->assertSessionHasErrors('surah_number');
});
