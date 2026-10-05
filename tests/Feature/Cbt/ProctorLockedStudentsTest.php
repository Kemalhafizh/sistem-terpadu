<?php

/** @var TestCase|stdClass $this */

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\CbtAccessControlService;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/*
 * The app's proctor screen asked the operator to type a student's database id
 * to unlock them after an anti-cheat force-close; nobody in the exam room knows
 * that id. teacher/cbt/exams/{id}/locked-students lists who is locked out, from
 * the attempts themselves. canViewToken() also let `admin` bypass the operator
 * rule, which CLAUDE.md reserves for superadmin.
 */

beforeEach(function () {
    $classroom = Classroom::factory()->create(['name' => 'XI MIPA 2']);
    $this->operator = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);
    $this->exam = Exam::create([
        'title' => 'UTS Fisika', 'subject_id' => Subject::factory()->create()->id, 'classroom_id' => $classroom->id,
        'academic_year_id' => AcademicYear::factory()->create()->id, 'created_by' => User::factory()->create(['role' => 'guru'])->id,
        'operator_id' => $this->operator->id, 'type' => 'uts', 'start_time' => now()->subMinutes(15), 'end_time' => now()->addHours(2),
        'duration_minutes' => 90, 'total_questions' => 5, 'status' => 'published', 'mobile_only' => true,
    ]);
    $this->studentNamed = fn (string $name) => Student::factory()->create([
        'user_id' => User::factory()->create(['role' => 'student', 'name' => $name])->id,
        'classroom_id' => $classroom->id,
        'nis' => (string) fake()->unique()->numberBetween(10000, 99999),
    ]);
    $this->attempt = fn (Student $student, string $status, ?string $reason = null) => ExamAttempt::create([
        'exam_id' => $this->exam->id, 'student_id' => $student->id, 'status' => $status,
        'force_closed' => $status === 'force_closed', 'force_close_reason' => $reason,
        'violation_count' => $status === 'force_closed' ? 2 : 0, 'started_at' => now()->subMinutes(10),
    ]);
});

test('operator melihat hanya siswa yang terkunci, dengan kode dan label alasan', function () {
    $locked = ($this->studentNamed)('Bagas Pratama');
    ($this->attempt)($locked, 'force_closed', 'split_screen');
    ($this->attempt)(($this->studentNamed)('Citra Lestari'), 'in_progress');
    Sanctum::actingAs($this->operator);

    $this->getJson("/api/v1/teacher/cbt/exams/{$this->exam->id}/locked-students")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.student_id', $locked->id)
        ->assertJsonPath('data.0.name', 'Bagas Pratama')
        ->assertJsonPath('data.0.nis', $locked->nis)
        ->assertJsonPath('data.0.classroom', 'XI MIPA 2')
        ->assertJsonPath('data.0.reason_code', 'split_screen')
        ->assertJsonPath('data.0.reason', 'Layar terbagi')
        ->assertJsonPath('data.0.violation_count', 2);
});

test('tanpa siswa terkunci daftarnya kosong', function () {
    ($this->attempt)(($this->studentNamed)('Citra Lestari'), 'in_progress');
    Sanctum::actingAs($this->operator);

    $this->getJson("/api/v1/teacher/cbt/exams/{$this->exam->id}/locked-students")->assertOk()->assertJsonCount(0, 'data');
});

test('guru yang bukan operator tidak melihat siswa terkunci', function () {
    ($this->attempt)(($this->studentNamed)('Bagas Pratama'), 'force_closed', 'app_closed');
    Sanctum::actingAs(User::factory()->create(['role' => 'guru', 'must_change_password' => false]));

    $this->getJson("/api/v1/teacher/cbt/exams/{$this->exam->id}/locked-students")
        ->assertForbidden()
        ->assertJsonPath('message', 'Anda tidak berwenang mengawasi ujian ini.');
});

test('label alasan mengikuti bahasa akun', function () {
    ($this->attempt)(($this->studentNamed)('Bagas Pratama'), 'force_closed', 'back_button_pressed');
    $this->operator->update(['preferred_locale' => 'en']);
    Sanctum::actingAs($this->operator);

    $this->getJson("/api/v1/teacher/cbt/exams/{$this->exam->id}/locked-students")
        ->assertJsonPath('data.0.reason_code', 'back_button_pressed')
        ->assertJsonPath('data.0.reason', 'Back button pressed');
});

test('siswa yang tidak terkunci tidak bisa direset, dengan pesan yang dimengerti guru', function () {
    $student = ($this->studentNamed)('Citra Lestari');
    ($this->attempt)($student, 'in_progress');
    Sanctum::actingAs($this->operator);

    $this->postJson("/api/v1/teacher/cbt/exams/{$this->exam->id}/token/reset-student", ['student_id' => $student->id])
        ->assertNotFound()
        ->assertJsonPath('message', 'Siswa ini tidak sedang terkunci di ujian ini.');
});

test('hanya superadmin yang melewati aturan operator, bukan admin', function () {
    $service = app(CbtAccessControlService::class);

    expect($service->canViewToken(User::factory()->create(['role' => 'admin']), $this->exam))->toBeFalse()
        ->and($service->canViewToken(User::factory()->create(['role' => 'superadmin']), $this->exam))->toBeTrue()
        ->and($service->canViewToken($this->operator, $this->exam))->toBeTrue();
});
