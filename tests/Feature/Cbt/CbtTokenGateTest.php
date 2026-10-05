<?php

use App\Models\AcademicYear;
use App\Models\CbtEssaySubmission;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\QuestionBank;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\EntersCbtExam;

uses(EntersCbtExam::class);

/*
 * The proctor's token (validate-token) is the only way into a CBT exam. The
 * questions, decryption-key, micro-sync, upload-essay and submit endpoints
 * refuse a student of the class who never entered it (e.g. a plain HTTP call
 * from home during the exam window) and never create an attempt themselves.
 */

beforeEach(function () {
    Storage::fake('public');

    $this->subject = Subject::factory()->create();
    $this->classroom = Classroom::factory()->create();
    $this->academicYear = AcademicYear::factory()->create();
    $this->teacher = User::factory()->create(['role' => 'guru']);

    $this->studentUser = User::factory()->create(['role' => 'siswa']);
    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'classroom_id' => $this->classroom->id,
    ]);

    $this->question = QuestionBank::create([
        'subject_id' => $this->subject->id,
        'created_by' => $this->teacher->id,
        'question_text' => 'Ibu kota Jawa Tengah?',
        'type' => 'multiple_choice',
        'options' => ['A' => 'Semarang', 'B' => 'Solo', 'C' => 'Kudus'],
        'correct_answer' => 'A',
        'difficulty' => 'easy',
        'grade_level' => 'X',
    ]);

    $this->makeExam = function (array $overrides = []): Exam {
        $exam = Exam::create(array_merge([
            'title' => 'Ulangan Harian Geografi',
            'subject_id' => $this->subject->id,
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'created_by' => $this->teacher->id,
            'type' => 'ulangan_harian',
            'start_time' => now()->subMinutes(5),
            'end_time' => now()->addHours(2),
            'duration_minutes' => 60,
            'total_questions' => 1,
            'mobile_only' => true,
            'max_violations' => 3,
            'passing_score' => 70,
            'status' => 'published',
            'access_token' => 'GEO123',
        ], $overrides));
        $exam->questions()->attach($this->question->id, ['order' => 1, 'weight' => 1.00]);

        return $exam;
    };

    $this->exam = ($this->makeExam)();
});

dataset('gated endpoints', [
    'questions' => fn () => ['get', 'questions', []],
    'decryption-key' => fn () => ['get', 'decryption-key', []],
    'micro-sync' => fn () => ['post', 'micro-sync', ['answers' => ['1' => 'A']]],
    'upload-essay' => fn () => ['post', 'upload-essay', ['file' => UploadedFile::fake()->image('esai.jpg', 400, 300)]],
    'submit' => fn () => ['post', 'submit', ['answers' => []]],
]);

function cbtGateCall($test, Exam $exam, array $endpoint)
{
    [$method, $path, $data] = $endpoint;
    if ($path === 'upload-essay') {
        $data['question_id'] = $test->question->id;
    }
    $url = "/api/v1/student/cbt/exams/{$exam->id}/{$path}";

    return $method === 'get' ? $test->getJson($url) : $test->postJson($url, $data);
}

test('a student who never entered the token is refused and no attempt is created', function (array $endpoint) {
    Sanctum::actingAs($this->studentUser);

    cbtGateCall($this, $this->exam, $endpoint)
        ->assertForbidden()
        ->assertJsonPath('message', 'Masukkan token ujian dari pengawas terlebih dahulu.');

    expect(ExamAttempt::where('exam_id', $this->exam->id)->exists())->toBeFalse();
})->with('gated endpoints');

test('an in-progress attempt without a validated token is refused on a token-gated exam', function (array $endpoint) {
    // E.g. a row the old encrypted-payload pre-fetch created before the student met the proctor.
    $attempt = ExamAttempt::create([
        'exam_id' => $this->exam->id,
        'student_id' => $this->student->id,
        'status' => 'in_progress',
        'started_at' => now(),
    ]);
    Sanctum::actingAs($this->studentUser);

    cbtGateCall($this, $this->exam, $endpoint)->assertForbidden();

    expect($attempt->fresh()->status)->toBe('in_progress')
        ->and($attempt->fresh()->answers)->toBeNull();
})->with('gated endpoints');

test('each endpoint works after the token was validated', function (array $endpoint) {
    $this->enterCbtExam($this->studentUser, $this->exam);

    cbtGateCall($this, $this->exam, $endpoint)->assertOk();
})->with('gated endpoints');

test('the refusal message follows the user language', function () {
    $this->studentUser->update(['preferred_locale' => 'en']);
    Sanctum::actingAs($this->studentUser);

    $this->getJson("/api/v1/student/cbt/exams/{$this->exam->id}/questions")
        ->assertForbidden()
        ->assertJsonPath('message', 'Enter the exam token from your proctor first.');
});

test('the attempt the token opened is the one that gets answers, essays and the score', function () {
    $attempt = $this->enterCbtExam($this->studentUser, $this->exam);

    $this->postJson("/api/v1/student/cbt/exams/{$this->exam->id}/micro-sync", [
        'answers' => [(string) $this->question->id => 'A'],
    ])->assertOk();
    $this->postJson("/api/v1/student/cbt/exams/{$this->exam->id}/upload-essay", [
        'question_id' => $this->question->id,
        'file' => UploadedFile::fake()->image('esai.jpg', 400, 300),
    ])->assertOk();
    $this->postJson("/api/v1/student/cbt/exams/{$this->exam->id}/submit", [
        'answers' => [$this->question->id => 'A'],
    ])->assertOk()->assertJsonPath('data.score', 100);

    expect(ExamAttempt::where('exam_id', $this->exam->id)->where('student_id', $this->student->id)->count())->toBe(1);
    $attempt->refresh();
    expect($attempt->status)->toBe('submitted')
        ->and((float) $attempt->score)->toBe(100.0)
        ->and($attempt->token_validated_at)->not->toBeNull();
    expect(CbtEssaySubmission::where('exam_attempt_id', $attempt->id)->exists())->toBeTrue();
});

test('the encrypted payload stays available before entering and creates no attempt', function () {
    Sanctum::actingAs($this->studentUser);

    $this->getJson("/api/v1/student/cbt/exams/{$this->exam->id}/encrypted-payload")
        ->assertOk()
        ->assertJsonPath('data.format', 'aes-256-cbc');

    expect(ExamAttempt::where('exam_id', $this->exam->id)->exists())->toBeFalse();
});

test('a student of another class is refused even with the right token', function () {
    $otherUser = User::factory()->create(['role' => 'siswa']);
    Student::factory()->create([
        'user_id' => $otherUser->id,
        'classroom_id' => Classroom::factory()->create()->id,
    ]);
    Sanctum::actingAs($otherUser);

    $this->withHeaders(['X-SISTA-Platform' => 'android'])
        ->postJson("/api/v1/student/cbt/exams/{$this->exam->id}/validate-token", ['token' => 'GEO123']);

    $this->getJson("/api/v1/student/cbt/exams/{$this->exam->id}/questions")->assertForbidden();
    $this->postJson("/api/v1/student/cbt/exams/{$this->exam->id}/submit", ['answers' => []])->assertForbidden();
});

test('a finished attempt stays closed after the token was validated', function () {
    $attempt = $this->enterCbtExam($this->studentUser, $this->exam);
    $this->postJson("/api/v1/student/cbt/exams/{$this->exam->id}/submit", ['answers' => []])->assertOk();

    $this->postJson("/api/v1/student/cbt/exams/{$this->exam->id}/micro-sync", ['answers' => ['1' => 'B']])->assertForbidden();
    $this->getJson("/api/v1/student/cbt/exams/{$this->exam->id}/decryption-key")->assertForbidden();
    $this->postJson("/api/v1/student/cbt/exams/{$this->exam->id}/submit", ['answers' => []])->assertForbidden();

    expect($attempt->fresh()->status)->toBe('submitted');
});

test('after the operator resets a force-closed student, the token must be entered again', function () {
    $this->enterCbtExam($this->studentUser, $this->exam);
    $this->postJson("/api/v1/student/cbt/exams/{$this->exam->id}/force-close", ['reason' => 'app_minimized'])->assertOk();

    Sanctum::actingAs($this->teacher);
    $this->postJson("/api/v1/teacher/cbt/exams/{$this->exam->id}/token/reset-student", [
        'student_id' => $this->student->id,
    ])->assertOk();

    Sanctum::actingAs($this->studentUser);
    $this->getJson("/api/v1/student/cbt/exams/{$this->exam->id}/questions")->assertForbidden();

    $this->enterCbtExam($this->studentUser, $this->exam);
    $this->getJson("/api/v1/student/cbt/exams/{$this->exam->id}/questions")->assertOk();
});

test('ExamAttempt::openFor needs in_progress, and a validated token only when the exam has one', function () {
    $attempt = ExamAttempt::create([
        'exam_id' => $this->exam->id,
        'student_id' => $this->student->id,
        'status' => 'in_progress',
        'started_at' => now(),
    ]);
    expect(ExamAttempt::openFor($this->exam, $this->student))->toBeNull();

    $attempt->update(['token_validated_at' => now()]);
    expect(ExamAttempt::openFor($this->exam, $this->student)?->id)->toBe($attempt->id);

    foreach (['submitted', 'graded', 'blocked_violation', 'timeout', 'force_closed'] as $status) {
        $attempt->update(['status' => $status]);
        expect(ExamAttempt::openFor($this->exam, $this->student))->toBeNull();
    }

    $tokenless = ($this->makeExam)(['access_token' => null, 'mobile_only' => false]);
    $webAttempt = ExamAttempt::create([
        'exam_id' => $tokenless->id,
        'student_id' => $this->student->id,
        'status' => 'in_progress',
        'started_at' => now(),
    ]);
    expect(ExamAttempt::openFor($tokenless, $this->student)?->id)->toBe($webAttempt->id);
});

test('a web exam that never uses a token still works from take to submit', function () {
    $exam = ($this->makeExam)(['access_token' => null, 'mobile_only' => false]);
    $this->actingAs($this->studentUser);

    $this->get(route('cbt.exams.take', $exam))->assertOk();
    $this->postJson(route('cbt.exams.submit', $exam), [
        'answers' => [(string) $this->question->id => 'A'],
    ])->assertOk()->assertJsonPath('success', true);

    // ExamController::submit grades right away, so the attempt ends up graded.
    $attempt = ExamAttempt::where('exam_id', $exam->id)->where('student_id', $this->student->id)->first();
    expect($attempt->status)->toBe('graded')
        ->and((float) $attempt->score)->toBe(100.0)
        ->and($attempt->token_validated_at)->toBeNull();
});
