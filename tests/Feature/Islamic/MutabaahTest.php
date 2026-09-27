<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\MutabaahLog;
use App\Models\MutabaahWeeklySummary;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create basic data needed for tests
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);

    // Homeroom teacher
    $this->teacherUser = User::factory()->create(['role' => 'guru']);
    $this->classroom = Classroom::factory()->create([
        'teacher_id' => $this->teacherUser->id,
    ]);

    // Parent
    $this->parentUser = User::factory()->create(['role' => 'parent']);

    // Student
    $this->studentUser = User::factory()->create(['role' => 'student']);
    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'parent_id' => $this->parentUser->id,
        'classroom_id' => $this->classroom->id,
    ]);
});

test('student can view mutabaah calendar page', function () {
    $response = $this->actingAs($this->studentUser)->get(route('mutabaah.index'));
    $response->assertStatus(200);
    $response->assertViewHas('student');
    $response->assertViewHas('monthlyLogs');
});

test('student can store daily detailed log and system auto-calculates score', function () {
    $dateStr = now()->format('Y-m-d');

    $response = $this->actingAs($this->studentUser)->post(route('mutabaah.store-daily-log'), [
        'student_id' => $this->student->id,
        'date' => $dateStr,
        'sholat_subuh' => 2, // Berjamaah (15 pts)
        'sholat_dzuhur' => 2, // Berjamaah (15 pts)
        'sholat_ashar' => 2, // Berjamaah (15 pts)
        'sholat_maghrib' => 2, // Berjamaah (15 pts)
        'sholat_isya' => 2, // Berjamaah (15 pts)
        'sholat_dhuha' => 1, // Ya (5 pts)
        'sholat_tahajud' => 1, // Ya (5 pts)
        'tadarus_pages' => 2, // Tadarus (10 pts)
        'puasa_sunnah' => false,
        'sedekah' => true, // Ya (5 pts)
        'dzikir_pagi' => true, // Ya (2.5 pts)
        'dzikir_sore' => true, // Ya (2.5 pts)
        'catatan_harian' => 'Hari yang produktif ibadah',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();
    $response->assertSessionHas('success');

    // Total expected score: 15*5 + 5 + 5 + 10 + 5 + 2.5 + 2.5 = 105, capped at 100
    $log = MutabaahLog::where('student_id', $this->student->id)->first();

    expect($log)->not->toBeNull();
    expect($log->score)->toBe(100);
    expect($log->sholat_subuh)->toBe(2);
    expect($log->tadarus_pages)->toBe(2);
    expect($log->sedekah)->toBe(true);
});

test('daily log submission triggers weekly summary auto-generation', function () {
    $dateStr = now()->startOfWeek()->format('Y-m-d'); // Monday

    $this->actingAs($this->studentUser)->post(route('mutabaah.store-daily-log'), [
        'student_id' => $this->student->id,
        'date' => $dateStr,
        'sholat_subuh' => 2,
        'sholat_dzuhur' => 2,
        'sholat_ashar' => 2,
        'sholat_maghrib' => 2,
        'sholat_isya' => 2,
        'sholat_dhuha' => 0,
        'sholat_tahajud' => 0,
        'tadarus_pages' => 0,
        'puasa_sunnah' => false,
        'sedekah' => false,
        'dzikir_pagi' => false,
        'dzikir_sore' => false,
    ]);

    $summary = MutabaahWeeklySummary::where('student_id', $this->student->id)->first();

    expect($summary)->not->toBeNull();
    // 5 sholats * 15 pts = 75 pts
    expect($summary->total_score)->toBe(75);
    expect($summary->grade)->toBe('B'); // 70-84 is B
});

test('teacher can view class rekap and write comment on student weekly summary', function () {
    $weekStart = now()->startOfWeek()->format('Y-m-d');

    // Seed a weekly summary
    $summary = MutabaahWeeklySummary::factory()->create([
        'student_id' => $this->student->id,
        'week_start' => $weekStart,
        'week_end' => now()->endOfWeek()->format('Y-m-d'),
        'total_score' => 88,
        'sholat_5_waktu_count' => 6,
        'sholat_berjamaah_count' => 20,
        'tadarus_total_pages' => 15,
        'grade' => 'A',
    ]);

    // Access class rekap
    $response = $this->actingAs($this->teacherUser)->get(route('mutabaah.rekap-kelas', $this->classroom->id));
    $response->assertStatus(200);
    $response->assertViewHas('summaries');

    // Post comment
    $commentResponse = $this->actingAs($this->teacherUser)->post(route('mutabaah.wali-kelas-comment', $summary->id), [
        'wali_kelas_comment' => 'Pertahankan semangat ibadahmu!',
    ]);

    $commentResponse->assertRedirect();
    expect($summary->fresh()->wali_kelas_comment)->toBe('Pertahankan semangat ibadahmu!');
});

test('parent can view child mutabaah and acknowledge weekly report', function () {
    $weekStart = now()->startOfWeek()->format('Y-m-d');

    // Seed summary
    $summary = MutabaahWeeklySummary::factory()->create([
        'student_id' => $this->student->id,
        'week_start' => $weekStart,
        'week_end' => now()->endOfWeek()->format('Y-m-d'),
        'total_score' => 88,
        'sholat_5_waktu_count' => 6,
        'sholat_berjamaah_count' => 20,
        'tadarus_total_pages' => 15,
        'grade' => 'A',
    ]);

    // Access parent portal view
    $response = $this->actingAs($this->parentUser)->get(route('mutabaah.parent-view', $this->student->id));
    $response->assertStatus(200);
    $response->assertViewHas('student');
    $response->assertViewHas('summaries');

    // Acknowledge report
    $ackResponse = $this->actingAs($this->parentUser)->post(route('mutabaah.parent-acknowledge', $summary->id));
    $ackResponse->assertRedirect();
    expect($summary->fresh()->parent_acknowledged)->toBeTrue();
});
