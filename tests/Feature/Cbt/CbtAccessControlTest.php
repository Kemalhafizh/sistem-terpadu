<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->subject = Subject::factory()->create(['name' => 'Matematika']);
    $this->classroom = Classroom::factory()->create();
    $this->academicYear = AcademicYear::factory()->create();

    // Teachers
    $this->teacherCreator = User::factory()->create(['role' => 'guru', 'name' => 'Guru Mapel']);
    $this->teacherOperator = User::factory()->create(['role' => 'guru', 'name' => 'Guru Operator']);
    $this->teacherProctor = User::factory()->create(['role' => 'guru', 'name' => 'Guru Pengawas Ruang']);

    // Student
    $this->studentUser = User::factory()->create(['role' => 'student']);
    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'classroom_id' => $this->classroom->id,
    ]);

    // Active UTS Exam (operator: teacherOperator)
    $this->utsExam = Exam::create([
        'title' => 'UTS Matematika Peminatan',
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'academic_year_id' => $this->academicYear->id,
        'created_by' => $this->teacherCreator->id,
        'operator_id' => $this->teacherOperator->id,
        'type' => 'uts',
        'start_time' => now()->subMinutes(15),
        'end_time' => now()->addHours(2),
        'duration_minutes' => 90,
        'total_questions' => 5,
        'status' => 'published',
        'access_token' => 'K7X4M2',
        'mobile_only' => true,
    ]);

    // Active Ulangan Harian Exam (no operator, creator: teacherCreator)
    $this->uhExam = Exam::create([
        'title' => 'PHB 1 Matematika Wajib',
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'academic_year_id' => $this->academicYear->id,
        'created_by' => $this->teacherCreator->id,
        'operator_id' => null,
        'type' => 'ulangan_harian',
        'start_time' => now()->subMinutes(10),
        'end_time' => now()->addHours(1),
        'duration_minutes' => 60,
        'total_questions' => 5,
        'status' => 'published',
        'access_token' => 'UH99AA',
        'mobile_only' => true,
    ]);
});

test('1. siswa dapat memvalidasi token yang benar dari platform android', function () {
    Sanctum::actingAs($this->studentUser);

    $response = $this->withHeaders([
        'X-SISTA-Platform' => 'android',
    ])->postJson("/api/v1/student/cbt/exams/{$this->utsExam->id}/validate-token", [
        'token' => 'K7X4M2',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'valid' => true,
                'exam_id' => $this->utsExam->id,
                'attempt_status' => 'in_progress',
            ],
        ]);

    $this->assertDatabaseHas('exam_attempts', [
        'exam_id' => $this->utsExam->id,
        'student_id' => $this->student->id,
        'access_platform' => 'android',
        'used_token' => 'K7X4M2',
        'status' => 'in_progress',
        'force_closed' => false,
    ]);
});

test('2. siswa ditolak jika token salah', function () {
    Sanctum::actingAs($this->studentUser);

    $response = $this->withHeaders([
        'X-SISTA-Platform' => 'android',
    ])->postJson("/api/v1/student/cbt/exams/{$this->utsExam->id}/validate-token", [
        'token' => 'WRONG1',
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
        ]);
});

test('3. siswa ditolak jika platform bukan android (misal web/browser)', function () {
    Sanctum::actingAs($this->studentUser);

    $response = $this->withHeaders([
        'X-SISTA-Platform' => 'web',
    ])->postJson("/api/v1/student/cbt/exams/{$this->utsExam->id}/validate-token", [
        'token' => 'K7X4M2',
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
        ]);
});

test('4. operator ujian dapat melihat token untuk ujian UTS/UAS', function () {
    Sanctum::actingAs($this->teacherOperator);

    $response = $this->getJson("/api/v1/teacher/cbt/exams/{$this->utsExam->id}/token");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'exam_id' => $this->utsExam->id,
                'access_token' => 'K7X4M2',
                'mobile_only' => true,
            ],
        ]);
});

test('5. guru pengawas kelas (bukan operator) TIDAK bisa melihat token ujian UTS/UAS', function () {
    Sanctum::actingAs($this->teacherProctor);

    $response = $this->getJson("/api/v1/teacher/cbt/exams/{$this->utsExam->id}/token");

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
        ]);
});

test('6. guru pembuat ujian dapat melihat token untuk Ulangan Harian', function () {
    Sanctum::actingAs($this->teacherCreator);

    $response = $this->getJson("/api/v1/teacher/cbt/exams/{$this->uhExam->id}/token");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'exam_id' => $this->uhExam->id,
                'access_token' => 'UH99AA',
            ],
        ]);
});

test('7. force-close menyimpan snapshot jawaban dan mengubah status attempt ke force_closed', function () {
    Sanctum::actingAs($this->studentUser);

    // Initial attempt in_progress
    ExamAttempt::create([
        'exam_id' => $this->utsExam->id,
        'student_id' => $this->student->id,
        'status' => 'in_progress',
        'started_at' => now(),
        'access_platform' => 'android',
        'used_token' => 'K7X4M2',
        'answers' => ['1' => 'A'],
    ]);

    $response = $this->withHeaders([
        'X-SISTA-Platform' => 'android',
    ])->postJson("/api/v1/student/cbt/exams/{$this->utsExam->id}/force-close", [
        'reason' => 'app_minimized',
        'answers_snapshot' => ['1' => 'A', '2' => 'B'],
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'status' => 'force_closed',
                'answers_saved' => true,
                'force_close_reason' => 'app_minimized',
            ],
        ]);

    $attempt = ExamAttempt::where('exam_id', $this->utsExam->id)
        ->where('student_id', $this->student->id)
        ->first();

    expect($attempt->status)->toBe('force_closed')
        ->and($attempt->force_closed)->toBeTrue()
        ->and($attempt->force_close_reason)->toBe('app_minimized')
        ->and($attempt->answers)->toBe(['1' => 'A', '2' => 'B']);
});

test('8. operator dapat mereset status force_closed siswa', function () {
    // Attempt in force_closed state
    ExamAttempt::create([
        'exam_id' => $this->utsExam->id,
        'student_id' => $this->student->id,
        'status' => 'force_closed',
        'force_closed' => true,
        'force_close_reason' => 'app_closed',
        'started_at' => now()->subMinutes(10),
        'submitted_at' => now(),
    ]);

    Sanctum::actingAs($this->teacherOperator);

    $response = $this->postJson("/api/v1/teacher/cbt/exams/{$this->utsExam->id}/token/reset-student", [
        'student_id' => $this->student->id,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'exam_id' => $this->utsExam->id,
                'student_id' => $this->student->id,
                'attempt_status' => 'in_progress',
                'force_closed' => false,
            ],
        ]);

    $attempt = ExamAttempt::where('exam_id', $this->utsExam->id)
        ->where('student_id', $this->student->id)
        ->first();

    expect($attempt->status)->toBe('in_progress')
        ->and($attempt->force_closed)->toBeFalse()
        ->and($attempt->force_close_reason)->toBeNull()
        ->and($attempt->submitted_at)->toBeNull();
});

test('9. token regenerate menghasilkan token baru yang berbeda', function () {
    Sanctum::actingAs($this->teacherOperator);

    $oldToken = $this->utsExam->access_token;

    $response = $this->postJson("/api/v1/teacher/cbt/exams/{$this->utsExam->id}/token/regenerate");

    $response->assertStatus(200);
    $newToken = $response->json('data.access_token');

    expect($newToken)->not->toBe($oldToken)
        ->and(strlen($newToken))->toBe(6);

    expect($this->utsExam->fresh()->access_token)->toBe($newToken);
});

test('10. siswa yang force_closed tidak bisa masuk sebelum di-reset operator', function () {
    Sanctum::actingAs($this->studentUser);

    ExamAttempt::create([
        'exam_id' => $this->utsExam->id,
        'student_id' => $this->student->id,
        'status' => 'force_closed',
        'force_closed' => true,
        'force_close_reason' => 'split_screen',
    ]);

    $response = $this->withHeaders([
        'X-SISTA-Platform' => 'android',
    ])->postJson("/api/v1/student/cbt/exams/{$this->utsExam->id}/validate-token", [
        'token' => 'K7X4M2',
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
        ]);
});

test('11. web controller ExamController take() memblokir akses jika mobile_only = true', function () {
    // Siswa login via web session
    $this->actingAs($this->studentUser);

    $response = $this->get(route('cbt.exams.take', $this->utsExam));

    $response->assertRedirect(route('cbt.exams.index'));
    $response->assertSessionHas('error', 'Ujian ini hanya dapat dikerjakan melalui Aplikasi SISTA di HP Anda. Silakan buka aplikasi SISTA di ponsel Anda.');
});

test('12. siswa ditolak jika token sudah kedaluwarsa lebih dari 5 menit', function () {
    Sanctum::actingAs($this->studentUser);

    // Set token kedaluwarsa (misal 6 menit yang lalu)
    $this->utsExam->update([
        'access_token' => 'EXP123',
        'token_expires_at' => now()->subMinute(),
    ]);

    $response = $this->withHeaders([
        'X-SISTA-Platform' => 'android',
    ])->postJson("/api/v1/student/cbt/exams/{$this->utsExam->id}/validate-token", [
        'token' => 'EXP123',
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Token sudah kedaluwarsa. Minta token baru ke pengawas ujian.',
        ]);
});

test('13. proktor dapat me-refresh token baru yang berlaku 5 menit berikutnya', function () {
    Sanctum::actingAs($this->teacherOperator);

    $response = $this->postJson("/api/v1/teacher/cbt/exams/{$this->utsExam->id}/token/regenerate");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'duration_minutes' => 5,
                'is_expired' => false,
            ],
        ]);

    expect($this->utsExam->fresh()->token_expires_at)->not->toBeNull();
    expect($this->utsExam->fresh()->getTokenRemainingSeconds())->toBeGreaterThan(250);
});
