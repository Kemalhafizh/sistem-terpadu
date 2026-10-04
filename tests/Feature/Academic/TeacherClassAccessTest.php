<?php

/** @var TestCase|stdClass $this */

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\DailyAssessment;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/*
 * teacher/classes/{id}/students and POST teacher/attendance never checked that
 * the teacher teaches the class: any teacher could read any class's roster and
 * record its attendance. teacher/schedule and teacher/classes also counted
 * earlier academic years, so the app's "kelas diampu" and "jam mengajar minggu
 * ini" included classes the teacher no longer has. POST assessments/teacher
 * accepted any class and subject, and filed the assessment under academic year
 * 1 when no year was active.
 */

beforeEach(function () {
    Cache::forget('academic_year_current');
    $this->lastYear = AcademicYear::create(['year' => '2025/2026', 'semester' => 'Genap', 'is_active' => false]);
    $this->year = AcademicYear::create(['year' => '2026/2027', 'semester' => 'Ganjil', 'is_active' => true]);
    $this->guru = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);
    $this->subject = Subject::factory()->create();

    $this->taught = Classroom::factory()->create(['name' => 'XI MIPA 2', 'academic_year' => '2026/2027']);
    $this->other = Classroom::factory()->create(['name' => 'XI MIPA 3', 'academic_year' => '2026/2027']);
    $this->oldClass = Classroom::factory()->create(['name' => 'X MIPA 2', 'academic_year' => '2025/2026']);

    $this->lesson = Schedule::create([
        'academic_year_id' => $this->year->id, 'classroom_id' => $this->taught->id, 'subject_id' => $this->subject->id,
        'teacher_id' => $this->guru->id, 'day' => 'Senin', 'session_start' => '07:00', 'session_end' => '08:30',
    ]);
    // Last year's lesson in another class: no longer this teacher's.
    Schedule::create([
        'academic_year_id' => $this->lastYear->id, 'classroom_id' => $this->oldClass->id, 'subject_id' => $this->subject->id,
        'teacher_id' => $this->guru->id, 'day' => 'Selasa', 'session_start' => '07:00', 'session_end' => '10:00',
    ]);
    // Someone else's lesson in the other class.
    $this->otherLesson = Schedule::create([
        'academic_year_id' => $this->year->id, 'classroom_id' => $this->other->id, 'subject_id' => $this->subject->id,
        'teacher_id' => User::factory()->create(['role' => 'guru'])->id, 'day' => 'Senin', 'session_start' => '07:00', 'session_end' => '08:30',
    ]);

    $this->studentIn = fn (Classroom $c) => Student::factory()->create([
        'user_id' => User::factory()->create(['role' => 'student'])->id,
        'classroom_id' => $c->id,
    ]);
});

test('guru hanya melihat daftar siswa kelas yang ia ajar tahun ini', function () {
    ($this->studentIn)($this->taught);
    ($this->studentIn)($this->other);
    Sanctum::actingAs($this->guru);

    $this->getJson("/api/v1/teacher/classes/{$this->taught->id}/students")->assertOk()->assertJsonCount(1, 'data');

    $this->getJson("/api/v1/teacher/classes/{$this->other->id}/students")
        ->assertForbidden()
        ->assertJsonPath('message', 'Anda tidak mengajar di kelas ini pada tahun ajaran aktif.')
        ->assertJsonPath('data', null);
    // Teaching it last year is not enough.
    $this->getJson("/api/v1/teacher/classes/{$this->oldClass->id}/students")->assertForbidden();
});

test('guru tidak bisa mengisi presensi kelas guru lain', function () {
    $student = ($this->studentIn)($this->other);
    Sanctum::actingAs($this->guru);

    $this->postJson('/api/v1/teacher/attendance', [
        'classroom_id' => $this->other->id,
        'date' => '2026-09-28',
        'students' => [['student_id' => $student->id, 'status' => 'A']],
    ])->assertForbidden();

    expect(Attendance::count())->toBe(0);
});

test('presensi kelas sendiri tersimpan, tapi jadwal kelas lain ditolak', function () {
    $student = ($this->studentIn)($this->taught);
    Sanctum::actingAs($this->guru);
    $payload = [
        'classroom_id' => $this->taught->id,
        'date' => '2026-09-28',
        'students' => [['student_id' => $student->id, 'status' => 'S']],
    ];

    $this->postJson('/api/v1/teacher/attendance', $payload + ['schedule_id' => $this->otherLesson->id])->assertStatus(422);
    expect(Attendance::count())->toBe(0);

    $this->postJson('/api/v1/teacher/attendance', $payload + ['schedule_id' => $this->lesson->id])
        ->assertOk()
        ->assertJsonPath('data.recorded_count', 1);
    expect(Attendance::sole())->status->toBe('S')->schedule_id->toBe($this->lesson->id);
});

test('wali kelas tahun ini boleh mengisi presensi kelasnya, wali tahun lalu tidak', function () {
    $homeroom = Classroom::factory()->create(['teacher_id' => $this->guru->id, 'academic_year' => '2026/2027']);
    $formerHomeroom = Classroom::factory()->create(['teacher_id' => $this->guru->id, 'academic_year' => '2025/2026']);
    Sanctum::actingAs($this->guru);

    $this->getJson("/api/v1/teacher/classes/{$homeroom->id}/students")->assertOk();
    $this->getJson("/api/v1/teacher/classes/{$formerHomeroom->id}/students")->assertForbidden();

    $classes = $this->getJson('/api/v1/teacher/classes')->assertOk()->json('data');
    expect(collect($classes)->pluck('id')->sort()->values()->all())->toBe(collect([$this->taught->id, $homeroom->id])->sort()->values()->all())
        ->and(collect($classes)->firstWhere('id', $homeroom->id)['is_homeroom'])->toBeTrue();
});

test('jadwal mengajar hanya tahun ajaran aktif', function () {
    Sanctum::actingAs($this->guru);

    $this->getJson('/api/v1/teacher/schedule')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $this->lesson->id);
});

test('tanpa tahun ajaran aktif, guru tidak punya kelas maupun jadwal', function () {
    $this->year->update(['is_active' => false]);
    Cache::forget('academic_year_current');
    Sanctum::actingAs($this->guru);

    $this->getJson('/api/v1/teacher/classes')->assertOk()->assertJsonCount(0, 'data');
    $this->getJson('/api/v1/teacher/schedule')->assertOk()->assertJsonCount(0, 'data');
    $this->getJson("/api/v1/teacher/classes/{$this->taught->id}/students")->assertForbidden();
});

test('penilaian harian hanya untuk mapel dan kelas yang diajar', function () {
    Sanctum::actingAs($this->guru);
    $assessment = ['subject_id' => $this->subject->id, 'title' => 'PH 1', 'assessment_date' => '2026-09-28', 'kkm' => 75];

    $this->postJson('/api/v1/assessments/teacher', $assessment + ['classroom_id' => $this->other->id])->assertForbidden();
    expect(DailyAssessment::count())->toBe(0);

    $this->postJson('/api/v1/assessments/teacher', $assessment + ['classroom_id' => $this->taught->id])->assertCreated();
    expect(DailyAssessment::sole()->academic_year_id)->toBe($this->year->id);
});

test('penilaian harian tidak dibuat tanpa tahun ajaran aktif', function () {
    $this->year->update(['is_active' => false]);
    Cache::forget('academic_year_current');
    Sanctum::actingAs($this->guru);

    $this->postJson('/api/v1/assessments/teacher', [
        'subject_id' => $this->subject->id, 'classroom_id' => $this->taught->id,
        'title' => 'PH 1', 'assessment_date' => '2026-09-28', 'kkm' => 75,
    ])->assertStatus(400)->assertJsonPath('success', false);

    expect(DailyAssessment::count())->toBe(0);
});

test('pesan endpoint guru mengikuti bahasa akun', function () {
    $this->guru->update(['preferred_locale' => 'en']);
    Sanctum::actingAs($this->guru);

    $this->getJson("/api/v1/teacher/classes/{$this->other->id}/students")
        ->assertForbidden()
        ->assertJsonPath('message', 'You do not teach this class in the active academic year.');
    $this->getJson('/api/v1/teacher/schedule')->assertJsonPath('message', 'Teaching schedule loaded');

    $this->guru->update(['preferred_locale' => 'ar']);
    $foreign = DailyAssessment::create([
        'subject_id' => $this->subject->id, 'classroom_id' => $this->other->id, 'teacher_id' => $this->otherLesson->teacher_id,
        'academic_year_id' => $this->year->id, 'title' => 'PH', 'type' => 'ulangan_harian',
        'assessment_date' => '2026-09-28', 'kkm' => 75, 'max_score' => 100,
    ]);
    $this->getJson("/api/v1/assessments/{$foreign->id}/scores")
        ->assertForbidden()
        ->assertJsonPath('message', 'تم رفض الوصول. هذه البيانات تخص فصل معلم آخر.');
});
