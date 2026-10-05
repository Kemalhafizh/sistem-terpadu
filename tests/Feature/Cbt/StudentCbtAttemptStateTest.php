<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\QuestionBank;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Laravel\Sanctum\Sanctum;

/*
 * The student CBT API (exam list, token, questions, sync, submit) as the app
 * uses it: only published exams are listed, a finished attempt stays
 * finished, and the clock comes from the attempt, not from the app.
 */

beforeEach(function () {
    $this->subject = Subject::factory()->create(['name' => 'Fisika']);
    $this->classroom = Classroom::factory()->create();
    $this->otherClassroom = Classroom::factory()->create();
    $this->academicYear = AcademicYear::factory()->create();
    $this->teacher = User::factory()->create(['role' => 'guru']);

    $this->studentUser = User::factory()->create(['role' => 'student']);
    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'classroom_id' => $this->classroom->id,
    ]);

    $this->makeExam = function (array $overrides = []): Exam {
        return Exam::create(array_merge([
            'title' => 'Ulangan Fisika',
            'subject_id' => $this->subject->id,
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'created_by' => $this->teacher->id,
            'type' => 'ulangan_harian',
            'start_time' => now()->subMinutes(10),
            'end_time' => now()->addHours(2),
            'duration_minutes' => 90,
            'total_questions' => 1,
            'passing_score' => 70,
            'status' => 'published',
            'access_token' => 'FIS123',
            'token_expires_at' => now()->addMinutes(5),
            'mobile_only' => true,
        ], $overrides));
    };

    $this->exam = ($this->makeExam)();
    $this->question = QuestionBank::create([
        'subject_id' => $this->subject->id,
        'question_text' => 'Satuan gaya adalah…',
        'type' => 'multiple_choice',
        'grade_level' => 'X',
        'options' => ['A' => 'Newton', 'B' => 'Joule'],
        'correct_answer' => 'A',
        'difficulty' => 'easy',
        'created_by' => $this->teacher->id,
    ]);
    $this->exam->questions()->attach($this->question->id, ['order' => 1, 'weight' => 1]);

    Sanctum::actingAs($this->studentUser);
});

function enterWithToken($test, Exam $exam, string $token = 'FIS123')
{
    return $test->withHeaders(['X-SISTA-Platform' => 'android'])
        ->postJson("/api/v1/student/cbt/exams/{$exam->id}/validate-token", ['token' => $token]);
}

test('daftar ujian tidak menampilkan draft dan memberi kode ketersediaan', function () {
    $draft = ($this->makeExam)(['title' => 'Draft rahasia', 'status' => 'draft']);
    $upcoming = ($this->makeExam)(['title' => 'Besok', 'start_time' => now()->addDay(), 'end_time' => now()->addDay()->addHours(2)]);
    $ended = ($this->makeExam)(['title' => 'Kemarin', 'start_time' => now()->subDay(), 'end_time' => now()->subDay()->addHours(2)]);

    $data = collect($this->getJson('/api/v1/student/cbt/exams')->assertOk()->json('data'))->keyBy('id');

    expect($data->has($draft->id))->toBeFalse()
        ->and($data[$this->exam->id]['availability_code'])->toBe('ongoing')
        ->and($data[$upcoming->id]['availability_code'])->toBe('upcoming')
        ->and($data[$ended->id]['availability_code'])->toBe('ended')
        ->and($data[$this->exam->id]['starts_at'])->toBe($this->exam->start_time->toIso8601String())
        ->and($data[$this->exam->id]['attempt_status'])->toBeNull()
        ->and($data[$this->exam->id]['score'])->toBeNull();
});

test('daftar ujian membawa status percobaan siswa itu sendiri', function () {
    $otherStudent = Student::factory()->create(['classroom_id' => $this->classroom->id]);
    ExamAttempt::create(['exam_id' => $this->exam->id, 'student_id' => $otherStudent->id, 'status' => 'graded', 'score' => 100]);

    $row = collect($this->getJson('/api/v1/student/cbt/exams')->json('data'))->firstWhere('id', $this->exam->id);
    expect($row['attempt_status'])->toBeNull()->and($row['has_attempted'])->toBeFalse();

    ExamAttempt::create(['exam_id' => $this->exam->id, 'student_id' => $this->student->id, 'status' => 'graded', 'score' => 80]);

    $row = collect($this->getJson('/api/v1/student/cbt/exams')->json('data'))->firstWhere('id', $this->exam->id);
    expect($row['attempt_status'])->toBe('graded')->and($row['score'])->toEqual(80);
});

test('token tidak membuka lagi percobaan yang sudah selesai', function (string $status) {
    $attempt = ExamAttempt::create(['exam_id' => $this->exam->id, 'student_id' => $this->student->id, 'status' => $status, 'score' => 40]);

    enterWithToken($this, $this->exam)->assertStatus(403)->assertJsonPath('success', false);

    expect($attempt->fresh()->status)->toBe($status);
})->with(['submitted', 'graded', 'blocked_violation', 'timeout', 'force_closed']);

test('siswa yang diblokir karena pelanggaran mendapat alasannya', function () {
    ExamAttempt::create(['exam_id' => $this->exam->id, 'student_id' => $this->student->id, 'status' => 'blocked_violation']);

    enterWithToken($this, $this->exam)
        ->assertStatus(403)
        ->assertJsonPath('message', 'Akses ujian Anda dikunci karena batas pelanggaran sudah tercapai.');
});

test('token dari kelas lain ditolak tanpa membuat percobaan', function () {
    $foreign = ($this->makeExam)(['classroom_id' => $this->otherClassroom->id, 'access_token' => 'LAIN99']);

    enterWithToken($this, $foreign, 'LAIN99')
        ->assertStatus(403)
        ->assertJsonPath('message', 'Ujian ini bukan untuk kelas Anda.');

    expect(ExamAttempt::where('exam_id', $foreign->id)->exists())->toBeFalse();
});

test('validasi token mengirim sisa waktu dari percobaan, dipotong akhir ujian', function () {
    $data = enterWithToken($this, $this->exam)->assertOk()->json('data');
    expect($data['remaining_seconds'])->toBeGreaterThan(90 * 60 - 5)->toBeLessThanOrEqual(90 * 60)
        ->and($data['ends_at'])->not->toBeNull();

    // An exam closing in 20 minutes leaves 20 minutes, not the full 90.
    $closing = ($this->makeExam)(['end_time' => now()->addMinutes(20), 'access_token' => 'TUTUP1']);
    $data = enterWithToken($this, $closing, 'TUTUP1')->assertOk()->json('data');
    expect($data['remaining_seconds'])->toBeLessThanOrEqual(20 * 60)
        ->and($data['ends_at'])->toBe($closing->end_time->toIso8601String());
});

test('masuk ulang tidak memulai ulang jam ujian', function () {
    ExamAttempt::create([
        'exam_id' => $this->exam->id,
        'student_id' => $this->student->id,
        'status' => 'in_progress',
        'started_at' => now()->subMinutes(30),
    ]);

    $data = enterWithToken($this, $this->exam)->assertOk()->json('data');
    expect($data['remaining_seconds'])->toBeLessThanOrEqual(60 * 60);

    $meta = $this->getJson("/api/v1/student/cbt/exams/{$this->exam->id}/questions")->assertOk()->json('data.exam');
    expect($meta['remaining_seconds'])->toBeLessThanOrEqual(60 * 60)
        ->and($meta['ends_at'])->toBe($data['ends_at']);
});

test('jawaban yang sudah dinilai tidak bisa dikirim ulang', function () {
    $attempt = ExamAttempt::create([
        'exam_id' => $this->exam->id,
        'student_id' => $this->student->id,
        'status' => 'graded',
        'score' => 0,
    ]);

    $this->postJson("/api/v1/student/cbt/exams/{$this->exam->id}/submit", [
        'answers' => [$this->question->id => 'A'],
        'total_violations' => 0,
        'time_spent_seconds' => 60,
    ])->assertStatus(403);

    expect((float) $attempt->fresh()->score)->toBe(0.0)->and($attempt->fresh()->status)->toBe('graded');
});

test('sinkron jawaban tidak menghidupkan lagi ujian yang ditutup paksa', function () {
    $attempt = ExamAttempt::create([
        'exam_id' => $this->exam->id,
        'student_id' => $this->student->id,
        'status' => 'force_closed',
        'force_closed' => true,
    ]);

    $this->postJson("/api/v1/student/cbt/exams/{$this->exam->id}/micro-sync", [
        'answers' => [$this->question->id => 'A'],
    ])->assertStatus(403);

    expect($attempt->fresh()->status)->toBe('force_closed');
});

test('hasil ujian memakai KKM ujian dan waktu ISO-8601', function () {
    enterWithToken($this, $this->exam)->assertOk();

    $data = $this->postJson("/api/v1/student/cbt/exams/{$this->exam->id}/submit", [
        'answers' => [$this->question->id => 'A'],
        'total_violations' => 0,
        'time_spent_seconds' => 60,
    ])->assertOk()->json('data');

    expect($data['score'])->toEqual(100)
        ->and($data['passed'])->toBeTrue()
        ->and(Carbon::hasFormat($data['submitted_at'], DATE_ATOM))->toBeTrue();
});

test('pesan token mengikuti bahasa pengguna', function () {
    $this->studentUser->update(['preferred_locale' => 'en']);

    enterWithToken($this, $this->exam, 'SALAH1')
        ->assertStatus(403)
        ->assertJsonPath('message', 'Invalid token. Check the code from the exam proctor again.');
});
