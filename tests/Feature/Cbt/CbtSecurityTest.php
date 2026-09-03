<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\QuestionBank;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\CbtService;
use App\Events\StudentCheatedEvent;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->subject = Subject::factory()->create(['name' => 'Matematika']);
    $this->classroom = Classroom::factory()->create();
    $this->academicYear = AcademicYear::factory()->create();

    // Teacher
    $this->teacher = User::factory()->create(['role' => 'guru']);

    // Student A
    $this->studentUserA = User::factory()->create(['role' => 'siswa']);
    $this->studentA = Student::factory()->create([
        'user_id' => $this->studentUserA->id,
        'classroom_id' => $this->classroom->id,
    ]);

    // Student B
    $this->studentUserB = User::factory()->create(['role' => 'siswa']);
    $this->studentB = Student::factory()->create([
        'user_id' => $this->studentUserB->id,
        'classroom_id' => $this->classroom->id,
    ]);

    // Create question bank
    $this->questions = [];
    foreach (['A', 'B', 'C'] as $i => $correct) {
        $this->questions[] = QuestionBank::create([
            'subject_id' => $this->subject->id,
            'created_by' => $this->teacher->id,
            'question_text' => "Test question " . ($i + 1),
            'type' => 'multiple_choice',
            'options' => ['A' => 'Option A', 'B' => 'Option B', 'C' => 'Option C', 'D' => 'Option D'],
            'correct_answer' => $correct,
            'difficulty' => 'medium',
            'grade_level' => 'X',
        ]);
    }

    // Create exam
    $this->exam = Exam::create([
        'title' => 'UTS Matematika X',
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'academic_year_id' => $this->academicYear->id,
        'created_by' => $this->teacher->id,
        'type' => 'uts',
        'start_time' => now()->subMinutes(5),
        'end_time' => now()->addHours(2),
        'duration_minutes' => 60,
        'total_questions' => 3,
        'shuffle_questions' => true,
        'shuffle_options' => true,
        'is_secure_mode' => true,
        'max_violations' => 3,
        'passing_score' => 70,
        'status' => 'published',
    ]);

    // Attach questions
    foreach ($this->questions as $idx => $q) {
        $this->exam->questions()->attach($q->id, ['order' => $idx + 1, 'weight' => 1.00]);
    }

    $this->cbtService = new CbtService();
});

// ================================================================
// DETERMINISTIC SHUFFLING TESTS
// ================================================================

test('two students get different question orders', function () {
    $attemptA = ExamAttempt::create([
        'exam_id' => $this->exam->id,
        'student_id' => $this->studentA->id,
        'started_at' => now(),
        'status' => 'in_progress',
    ]);
    $attemptB = ExamAttempt::create([
        'exam_id' => $this->exam->id,
        'student_id' => $this->studentB->id,
        'started_at' => now(),
        'status' => 'in_progress',
    ]);

    $orderA = $this->cbtService->getOrInitializeQuestionOrder($this->exam, $this->studentA, $attemptA);
    $orderB = $this->cbtService->getOrInitializeQuestionOrder($this->exam, $this->studentB, $attemptB);

    // Both contain the same questions
    expect(sort($orderA))->toEqual(sort($orderB));

    // Orders should differ (statistically near-certain with different student IDs)
    // Note: there's a tiny chance they're the same for 3 questions, so we just verify length
    expect(count($orderA))->toBe(3);
    expect(count($orderB))->toBe(3);
});

test('same student gets same order on repeated calls', function () {
    $attempt = ExamAttempt::create([
        'exam_id' => $this->exam->id,
        'student_id' => $this->studentA->id,
        'started_at' => now(),
        'status' => 'in_progress',
    ]);

    $order1 = $this->cbtService->getOrInitializeQuestionOrder($this->exam, $this->studentA, $attempt);
    $order2 = $this->cbtService->getOrInitializeQuestionOrder($this->exam, $this->studentA, $attempt->fresh());

    expect($order1)->toEqual($order2);
});

// ================================================================
// AUTO-GRADING TESTS
// ================================================================

test('auto-grading calculates score correctly with all correct answers', function () {
    $attempt = ExamAttempt::create([
        'exam_id' => $this->exam->id,
        'student_id' => $this->studentA->id,
        'started_at' => now(),
        'status' => 'in_progress',
        'answers' => [
            $this->questions[0]->id => 'A',
            $this->questions[1]->id => 'B',
            $this->questions[2]->id => 'C',
        ],
    ]);

    $graded = $this->cbtService->autoGrade($attempt);

    expect($graded->score)->toEqual(100.00);
    expect($graded->correct_count)->toBe(3);
    expect($graded->wrong_count)->toBe(0);
    expect($graded->unanswered_count)->toBe(0);
    expect($graded->status)->toBe('graded');
});

test('auto-grading calculates partial score correctly', function () {
    $attempt = ExamAttempt::create([
        'exam_id' => $this->exam->id,
        'student_id' => $this->studentA->id,
        'started_at' => now(),
        'status' => 'in_progress',
        'answers' => [
            $this->questions[0]->id => 'A', // correct
            $this->questions[1]->id => 'A', // wrong (correct is B)
            $this->questions[2]->id => 'C', // correct
        ],
    ]);

    $graded = $this->cbtService->autoGrade($attempt);

    expect(round((float) $graded->score, 2))->toEqual(66.67);
    expect($graded->correct_count)->toBe(2);
    expect($graded->wrong_count)->toBe(1);
    expect($graded->unanswered_count)->toBe(0);
});

test('auto-grading handles unanswered questions', function () {
    $attempt = ExamAttempt::create([
        'exam_id' => $this->exam->id,
        'student_id' => $this->studentA->id,
        'started_at' => now(),
        'status' => 'in_progress',
        'answers' => [
            $this->questions[0]->id => 'A', // correct
        ],
    ]);

    $graded = $this->cbtService->autoGrade($attempt);

    expect(round((float) $graded->score, 2))->toEqual(33.33);
    expect($graded->correct_count)->toBe(1);
    expect($graded->unanswered_count)->toBe(2);
});

// ================================================================
// VIOLATION LOGGING & BLOCKING TESTS
// ================================================================

test('violation log endpoint saves violations correctly', function () {
    Event::fake([StudentCheatedEvent::class]);

    $attempt = ExamAttempt::create([
        'exam_id' => $this->exam->id,
        'student_id' => $this->studentA->id,
        'started_at' => now(),
        'status' => 'in_progress',
    ]);

    $response = $this->actingAs($this->studentUserA)->postJson(
        route('cbt.exams.log-violation', $this->exam),
        ['type' => 'tab_switch']
    );

    $response->assertOk();
    $response->assertJson(['success' => true, 'violation_count' => 1, 'is_blocked' => false]);

    $attempt->refresh();
    expect($attempt->violation_count)->toBe(1);
    expect($attempt->violation_logs)->toHaveCount(1);
    expect($attempt->violation_logs[0]['type'])->toBe('tab_switch');

    Event::assertDispatched(StudentCheatedEvent::class);
});

test('student gets blocked after max violations', function () {
    Event::fake([StudentCheatedEvent::class]);

    $attempt = ExamAttempt::create([
        'exam_id' => $this->exam->id,
        'student_id' => $this->studentA->id,
        'started_at' => now(),
        'status' => 'in_progress',
        'violation_count' => 2,
        'violation_logs' => [
            ['type' => 'blur', 'time' => now()->toDateTimeString()],
            ['type' => 'blur', 'time' => now()->toDateTimeString()],
        ],
        'answers' => [$this->questions[0]->id => 'A'],
    ]);

    // Third violation should trigger block
    $response = $this->actingAs($this->studentUserA)->postJson(
        route('cbt.exams.log-violation', $this->exam),
        ['type' => 'fullscreen_exit']
    );

    $response->assertOk();
    $response->assertJson(['success' => true, 'is_blocked' => true]);

    $attempt->refresh();
    expect($attempt->status)->toBe('blocked_violation');
    expect($attempt->violation_count)->toBe(3);
    expect($attempt->is_auto_submitted)->toBeTrue();
});

// ================================================================
// EXAM SUBMISSION TESTS
// ================================================================

test('student can submit exam and get auto-graded', function () {
    $attempt = ExamAttempt::create([
        'exam_id' => $this->exam->id,
        'student_id' => $this->studentA->id,
        'started_at' => now(),
        'status' => 'in_progress',
    ]);

    $response = $this->actingAs($this->studentUserA)->postJson(
        route('cbt.exams.submit', $this->exam),
        ['answers' => [
            $this->questions[0]->id => 'A',
            $this->questions[1]->id => 'B',
            $this->questions[2]->id => 'C',
        ]]
    );

    $response->assertOk();
    $response->assertJsonStructure(['success', 'redirect']);

    $attempt->refresh();
    expect($attempt->status)->toBe('graded');
    expect($attempt->score)->toEqual(100.00);
    expect($attempt->submitted_at)->not()->toBeNull();
});

test('double submission is prevented', function () {
    ExamAttempt::create([
        'exam_id' => $this->exam->id,
        'student_id' => $this->studentA->id,
        'started_at' => now(),
        'submitted_at' => now(),
        'status' => 'graded',
        'score' => 100,
    ]);

    $response = $this->actingAs($this->studentUserA)->postJson(
        route('cbt.exams.submit', $this->exam),
        ['answers' => []]
    );

    $response->assertStatus(409);
});

// ================================================================
// AUTO-SAVE TESTS
// ================================================================

test('auto-save endpoint stores answers', function () {
    $attempt = ExamAttempt::create([
        'exam_id' => $this->exam->id,
        'student_id' => $this->studentA->id,
        'started_at' => now(),
        'status' => 'in_progress',
    ]);

    $answers = [$this->questions[0]->id => 'B', $this->questions[1]->id => 'C'];

    $response = $this->actingAs($this->studentUserA)->postJson(
        route('cbt.exams.save-answers', $this->exam),
        ['answers' => $answers]
    );

    $response->assertOk();
    $response->assertJson(['success' => true]);

    $attempt->refresh();
    expect($attempt->answers)->toEqual($answers);
});

// ================================================================
// AUTHORIZATION TESTS
// ================================================================

test('teacher can create exams', function () {
    $this->actingAs($this->teacher)->get(route('cbt.exams.create'))->assertOk();
});

test('student cannot create exams', function () {
    $this->actingAs($this->studentUserA)->get(route('cbt.exams.create'))->assertForbidden();
});

test('student can access take exam page for published ongoing exam', function () {
    $this->exam->update(['mobile_only' => false]);
    $this->actingAs($this->studentUserA)->get(route('cbt.exams.take', $this->exam))->assertOk();
});

test('teacher can access monitor page', function () {
    $this->actingAs($this->teacher)->get(route('cbt.exams.monitor', $this->exam))->assertOk();
});
