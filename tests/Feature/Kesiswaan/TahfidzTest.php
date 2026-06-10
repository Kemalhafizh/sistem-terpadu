<?php

/** @var TestCase|stdClass $this */

use App\Models\Student;
use App\Models\User;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Models\TahfidzLog;
use App\Models\TahfidzTarget;
use App\Models\StudentXpLog;
use Tests\TestCase;

beforeEach(function () {
    $this->academicYear = AcademicYear::factory()->create([
        'year' => '2025/2026',
        'semester' => 'Genap',
        'is_active' => true,
        'start_date' => '2026-01-06',
        'end_date' => '2026-06-21'
    ]);
    
    $this->guru = User::factory()->create(['role' => 'guru']);
    $this->admin = User::factory()->create(['role' => 'admin']);
    
    $this->classroom = Classroom::factory()->create([
        'name' => 'XII-A',
        'grade_level' => 'XII',
        'major' => 'IPA',
        'teacher_id' => $this->guru->id,
        'academic_year' => '2025/2026'
    ]);
    
    $this->student = Student::factory()->create([
        'classroom_id' => $this->classroom->id
    ]);
    $this->studentUser = $this->student->user;
    $this->studentUser->update(['role' => 'student']);
});

// ── SHOW & DASHBOARD ───────────────────────────────────────────────────

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

test('guru can view tahfidz dashboard with classroom select', function () {
    $response = $this->actingAs($this->guru)->get(route('tahfidz.dashboard', ['classroom_id' => $this->classroom->id]));

    $response->assertStatus(200);
    $response->assertViewIs('tahfidz.dashboard');
    $response->assertViewHas('classroom');
    $response->assertViewHas('summary');
});

test('student can view their own dashboard', function () {
    $response = $this->actingAs($this->studentUser)->get(route('tahfidz.dashboard'));

    $response->assertStatus(200);
    $response->assertViewIs('tahfidz.dashboard');
    $response->assertViewHas('student');
    $response->assertViewHas('progress');
});

// ── STORE ──────────────────────────────────────────────────────────────

test('guru can record a tahfidz memorization log', function () {
    $response = $this->actingAs($this->guru)->post(route('tahfidz.store'), [
        'student_id' => $this->student->id,
        'surah_number' => 36,
        'from_ayat' => 1,
        'to_ayat' => 12,
        'type' => 'ziyadah',
        'grade' => 'A',
        'notes' => 'Tajwid baik',
        'date' => now()->format('Y-m-d'),
        'juz' => 22,
        'pages_count' => 2,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('tahfidz_logs', [
        'student_id' => $this->student->id,
        'surah_number' => 36,
        'surah_name' => 'Ya Sin',
        'type' => 'ziyadah',
        'teacher_id' => $this->guru->id,
        'juz' => 22,
        'pages_count' => 2,
        'grade' => 'A',
    ]);
});

// ── BULK INPUT ─────────────────────────────────────────────────────────

test('guru can access bulk input form', function () {
    $response = $this->actingAs($this->guru)->get(route('tahfidz.bulk-input', ['classroom_id' => $this->classroom->id]));

    $response->assertStatus(200);
    $response->assertViewIs('tahfidz.bulk-input');
});

test('guru can submit bulk setoran', function () {
    $student2 = Student::factory()->create(['classroom_id' => $this->classroom->id]);

    $response = $this->actingAs($this->guru)->post(route('tahfidz.store-bulk'), [
        'classroom_id' => $this->classroom->id,
        'setoran' => [
            [
                'active' => '1',
                'student_id' => $this->student->id,
                'surah_number' => 78,
                'from_ayat' => 1,
                'to_ayat' => 10,
                'type' => 'ziyadah',
                'grade' => 'A',
                'juz' => 30,
                'pages_count' => 1,
                'notes' => 'Lancar',
                'date' => now()->format('Y-m-d'),
            ],
            [
                'active' => '1',
                'student_id' => $student2->id,
                'surah_number' => 78,
                'from_ayat' => 11,
                'to_ayat' => 20,
                'type' => 'ziyadah',
                'grade' => 'B',
                'juz' => 30,
                'pages_count' => 1,
                'notes' => 'Cukup',
                'date' => now()->format('Y-m-d'),
            ]
        ]
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('tahfidz_logs', ['student_id' => $this->student->id, 'from_ayat' => 1]);
    $this->assertDatabaseHas('tahfidz_logs', ['student_id' => $student2->id, 'from_ayat' => 11]);
});

// ── TARGET SETTING ─────────────────────────────────────────────────────

test('guru can access target setting form', function () {
    $response = $this->actingAs($this->guru)->get(route('tahfidz.target-setting', ['classroom_id' => $this->classroom->id]));

    $response->assertStatus(200);
    $response->assertViewIs('tahfidz.target-setting');
});

test('guru can store and update student targets', function () {
    $response = $this->actingAs($this->guru)->post(route('tahfidz.store-targets'), [
        'classroom_id' => $this->classroom->id,
        'targets' => [
            [
                'student_id' => $this->student->id,
                'target_juz' => 3,
                'target_pages' => 60,
                'notes' => 'Fokus semester ini',
            ]
        ]
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('tahfidz_targets', [
        'student_id' => $this->student->id,
        'academic_year_id' => $this->academicYear->id,
        'target_juz' => 3,
        'target_pages' => 60,
    ]);
});

// ── LEADERBOARD ────────────────────────────────────────────────────────

test('anyone can view tahfidz leaderboard', function () {
    $response = $this->actingAs($this->studentUser)->get(route('tahfidz.leaderboard'));

    $response->assertStatus(200);
    $response->assertViewIs('tahfidz.leaderboard');
    $response->assertViewHas('leaderboard');
});

// ── XP & TARGET COMPLETION logic ───────────────────────────────────────

test('submitting setoran awards grade-based XP and target updates', function () {
    // 1. Create Target
    TahfidzTarget::factory()->create([
        'student_id' => $this->student->id,
        'academic_year_id' => $this->academicYear->id,
        'target_juz' => 1,
        'target_pages' => 2,
        'achieved_juz' => 0,
        'achieved_pages' => 0,
    ]);

    // Track starting XP
    $startingXp = $this->student->fresh()->total_xp;

    // 2. Submit Ziyadah Grade A Setoran (should earn +15 XP)
    $response = $this->actingAs($this->guru)->post(route('tahfidz.store'), [
        'student_id' => $this->student->id,
        'surah_number' => 78,
        'from_ayat' => 1,
        'to_ayat' => 5,
        'type' => 'ziyadah',
        'grade' => 'A',
        'notes' => 'Mumtaz',
        'date' => now()->format('Y-m-d'),
        'juz' => 30,
        'pages_count' => 1,
    ]);

    // Assert XP increased by 15
    $currentXp = $this->student->fresh()->total_xp;
    expect($currentXp - $startingXp)->toBe(15);

    // Assert target achieved_pages updated to 1 (50% progress, which is 'behind' at 92.7% semester elapsed)
    $target = TahfidzTarget::where('student_id', $this->student->id)->first();
    expect($target->achieved_pages)->toBe(1);
    expect($target->status)->toBe('behind');

    // 3. Submit another setoran to meet/exceed target (1 more page, target is 2 pages)
    // Meeting the target should trigger completion +50 XP bonus.
    // So: +15 XP (Grade A Ziyadah) + 50 XP (Target Completion) = +65 XP.
    $this->actingAs($this->guru)->post(route('tahfidz.store'), [
        'student_id' => $this->student->id,
        'surah_number' => 78,
        'from_ayat' => 6,
        'to_ayat' => 10,
        'type' => 'ziyadah',
        'grade' => 'A',
        'notes' => 'Mumtaz',
        'date' => now()->format('Y-m-d'),
        'juz' => 30,
        'pages_count' => 1,
    ]);

    $newXp = $this->student->fresh()->total_xp;
    // Difference from currentXp should be 15 + 50 = 65
    expect($newXp - $currentXp)->toBe(65);

    // Verify status is completed
    $target = $target->fresh();
    expect($target->achieved_pages)->toBe(2);
    expect($target->status)->toBe('completed');

    // 4. Submit one more setoran. It should NOT award target completion XP again.
    // So only +15 XP for Grade A Ziyadah.
    $this->actingAs($this->guru)->post(route('tahfidz.store'), [
        'student_id' => $this->student->id,
        'surah_number' => 78,
        'from_ayat' => 11,
        'to_ayat' => 15,
        'type' => 'ziyadah',
        'grade' => 'A',
        'notes' => 'Mumtaz',
        'date' => now()->format('Y-m-d'),
        'juz' => 30,
        'pages_count' => 1,
    ]);

    $finalXp = $this->student->fresh()->total_xp;
    expect($finalXp - $newXp)->toBe(15); // only grade XP, no target bonus again
});

// ── PDF EXPORT ─────────────────────────────────────────────────────────

test('authorized users can export student progress to pdf', function () {
    // Set a target to prevent empty pdf issues
    TahfidzTarget::factory()->create([
        'student_id' => $this->student->id,
        'academic_year_id' => $this->academicYear->id,
        'target_juz' => 1,
        'target_pages' => 20,
    ]);

    $response = $this->actingAs($this->guru)->get(route('tahfidz.export-pdf', $this->student));
    
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/pdf');
});
