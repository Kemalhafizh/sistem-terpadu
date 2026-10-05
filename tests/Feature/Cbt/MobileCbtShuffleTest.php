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
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\EntersCbtExam;

uses(EntersCbtExam::class);

/*
 * GET student/cbt/exams/{id}/questions (Android app) must honor the exam's
 * shuffle_questions / shuffle_options flags with a per-student deterministic
 * order that matches the web take view and the encrypted vault payload, and
 * grading must stay correct because option keys are never relabeled.
 */

beforeEach(function () {
    $this->subject = Subject::factory()->create(['name' => 'Biologi']);
    $this->classroom = Classroom::factory()->create();
    $this->academicYear = AcademicYear::factory()->create();
    $this->teacher = User::factory()->create(['role' => 'guru']);

    $this->makeStudent = function (): array {
        $user = User::factory()->create(['role' => 'siswa']);
        $student = Student::factory()->create([
            'user_id' => $user->id,
            'classroom_id' => $this->classroom->id,
        ]);

        return [$user, $student];
    };

    [$this->userA, $this->studentA] = ($this->makeStudent)();

    // 8 questions × 5 options: a coincidentally identical permutation is ~1/40320.
    // Stored in the shape ApiTeacherController::storeExamWithQuestions writes.
    $this->questions = [];
    $this->correctText = [];
    foreach (range(1, 8) as $n) {
        $correct = ['A', 'B', 'C', 'D', 'E'][$n % 5];
        $options = [];
        foreach (['A', 'B', 'C', 'D', 'E'] as $key) {
            $options[] = ['key' => $key, 'text' => "Q{$n} opsi {$key}", 'image_url' => null];
        }
        $q = QuestionBank::create([
            'subject_id' => $this->subject->id,
            'created_by' => $this->teacher->id,
            'question_text' => "Soal {$n}",
            'type' => 'multiple_choice',
            'options' => $options,
            'correct_answer' => $correct,
            'difficulty' => 'medium',
            'grade_level' => 'X',
        ]);
        $this->questions[] = $q;
        $this->correctText[$q->id] = "Q{$n} opsi {$correct}";
    }

    $this->makeExam = function (bool $shuffleQuestions, bool $shuffleOptions): Exam {
        $exam = Exam::create([
            'title' => 'Ulangan Harian Biologi',
            'subject_id' => $this->subject->id,
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'created_by' => $this->teacher->id,
            'type' => 'ulangan_harian',
            'start_time' => now()->subMinutes(5),
            'end_time' => now()->addHours(2),
            'duration_minutes' => 60,
            'total_questions' => count($this->questions),
            'shuffle_questions' => $shuffleQuestions,
            'shuffle_options' => $shuffleOptions,
            'mobile_only' => true,
            'max_violations' => 3,
            'passing_score' => 70,
            'status' => 'published',
        ]);
        // Authored order is the reverse of insertion/id order, so "authored"
        // really means pivot `order`, not whatever the relation returns.
        $count = count($this->questions);
        foreach ($this->questions as $idx => $q) {
            $exam->questions()->attach($q->id, ['order' => $count - $idx, 'weight' => 1.00]);
        }

        return $exam;
    };

    // Questions are only served after the student entered the proctor's token
    // (validate-token), so fetch enters the exam first like the app does.
    $this->fetch = function (User $user, Exam $exam): array {
        if (! ExamAttempt::openFor($exam, $user->student)) {
            $this->enterCbtExam($user, $exam);
        }
        Sanctum::actingAs($user);

        return $this->getJson("/api/v1/student/cbt/exams/{$exam->id}/questions")
            ->assertOk()
            ->json('data.questions');
    };
});

function mobileCbtQuestionIds(array $questions): array
{
    return array_column($questions, 'id');
}

function mobileCbtOptionKeys(array $question): array
{
    return array_column($question['options'], 'key');
}

test('same student gets the same question and option order on repeated fetches', function () {
    $exam = ($this->makeExam)(true, true);

    $first = ($this->fetch)($this->userA, $exam);
    $second = ($this->fetch)($this->userA, $exam);

    expect($second)->toEqual($first);
    expect(array_column($first, 'number'))->toEqual(range(1, 8));
});

test('same order with and without an attempt row, and the attempt stores it', function () {
    $exam = ($this->makeExam)(true, true);

    // Pre-fetch before entering the exam: no attempt yet, so the payload's
    // order is computed from the seed and nothing is persisted.
    Sanctum::actingAs($this->userA);
    $payload = $this->getJson("/api/v1/student/cbt/exams/{$exam->id}/encrypted-payload")->assertOk()->json('data');
    expect(ExamAttempt::where('exam_id', $exam->id)->count())->toBe(0);

    // validate-token creates the attempt; the questions must not reshuffle.
    $attempt = $this->enterCbtExam($this->userA, $exam);
    $withAttempt = ($this->fetch)($this->userA, $exam);

    $key = base64_decode($this->getJson("/api/v1/student/cbt/exams/{$exam->id}/decryption-key")->assertOk()->json('data.key'));
    $withoutAttempt = json_decode(openssl_decrypt(
        base64_decode($payload['encrypted_data']), 'aes-256-cbc', $key, OPENSSL_RAW_DATA, base64_decode($payload['iv'])
    ), true)['questions'];

    expect(mobileCbtQuestionIds($withAttempt))->toEqual(mobileCbtQuestionIds($withoutAttempt));
    expect($attempt->fresh()->question_order)->toEqual(mobileCbtQuestionIds($withAttempt));
});

test('an order already stored on the attempt (e.g. by the web path) is reused', function () {
    $exam = ($this->makeExam)(true, false);
    $stored = collect($this->questions)->pluck('id')->reverse()->values()->all();
    // Swap the first two so it can't coincide with the seeded order by accident.
    [$stored[0], $stored[1]] = [$stored[1], $stored[0]];

    ExamAttempt::create([
        'exam_id' => $exam->id,
        'student_id' => $this->studentA->id,
        'started_at' => now(),
        'status' => 'in_progress',
        'question_order' => $stored,
    ]);

    expect(mobileCbtQuestionIds(($this->fetch)($this->userA, $exam)))->toEqual($stored);
});

test('different students get different orders when shuffle is on', function () {
    $exam = ($this->makeExam)(true, true);
    [$userB] = ($this->makeStudent)();
    [$userC] = ($this->makeStudent)();

    $a = ($this->fetch)($this->userA, $exam);
    $b = ($this->fetch)($userB, $exam);
    $c = ($this->fetch)($userC, $exam);

    // Same question set, different sequence.
    $sortedIds = fn (array $qs) => collect(mobileCbtQuestionIds($qs))->sort()->values()->all();
    expect($sortedIds($b))->toEqual($sortedIds($a));
    expect(mobileCbtQuestionIds($a) === mobileCbtQuestionIds($b) && mobileCbtQuestionIds($a) === mobileCbtQuestionIds($c))->toBeFalse();

    // Options: same keys per question (never relabeled), but the display order
    // differs from authored A–E for at least one question.
    $authoredKeys = ['A', 'B', 'C', 'D', 'E'];
    foreach ($a as $question) {
        expect(collect(mobileCbtOptionKeys($question))->sort()->values()->all())->toEqual($authoredKeys);
    }
    $anyReordered = collect($a)->contains(fn ($q) => mobileCbtOptionKeys($q) !== $authoredKeys);
    expect($anyReordered)->toBeTrue();

    // Option order is per student too.
    $optionsById = fn (array $qs) => collect($qs)->mapWithKeys(fn ($q) => [$q['id'] => mobileCbtOptionKeys($q)])->sortKeys()->all();
    expect($optionsById($a))->not->toEqual($optionsById($b));
});

test('authored order is returned when both flags are off', function () {
    $exam = ($this->makeExam)(false, false);
    [$userB] = ($this->makeStudent)();

    $authoredIds = collect($this->questions)->pluck('id')->reverse()->values()->all();

    foreach ([$this->userA, $userB] as $user) {
        $questions = ($this->fetch)($user, $exam);
        expect(mobileCbtQuestionIds($questions))->toEqual($authoredIds);
        foreach ($questions as $question) {
            expect(mobileCbtOptionKeys($question))->toEqual(['A', 'B', 'C', 'D', 'E']);
        }
    }
});

test('each flag works independently', function () {
    $authoredIds = collect($this->questions)->pluck('id')->reverse()->values()->all();

    $onlyOptions = ($this->fetch)($this->userA, ($this->makeExam)(false, true));
    expect(mobileCbtQuestionIds($onlyOptions))->toEqual($authoredIds);
    expect(collect($onlyOptions)->contains(fn ($q) => mobileCbtOptionKeys($q) !== ['A', 'B', 'C', 'D', 'E']))->toBeTrue();

    $onlyQuestions = ($this->fetch)($this->userA, ($this->makeExam)(true, false));
    expect(mobileCbtQuestionIds($onlyQuestions))->not->toEqual($authoredIds);
    foreach ($onlyQuestions as $question) {
        expect(mobileCbtOptionKeys($question))->toEqual(['A', 'B', 'C', 'D', 'E']);
    }
});

test('encrypted vault payload has the same order as the questions endpoint', function () {
    $exam = ($this->makeExam)(true, true);

    $questions = ($this->fetch)($this->userA, $exam);

    Sanctum::actingAs($this->userA);
    $payload = $this->getJson("/api/v1/student/cbt/exams/{$exam->id}/encrypted-payload")->assertOk()->json('data');
    $key = base64_decode($this->getJson("/api/v1/student/cbt/exams/{$exam->id}/decryption-key")->assertOk()->json('data.key'));

    $decrypted = json_decode(openssl_decrypt(
        base64_decode($payload['encrypted_data']), 'aes-256-cbc', $key, OPENSSL_RAW_DATA, base64_decode($payload['iv'])
    ), true);

    expect(mobileCbtQuestionIds($decrypted['questions']))->toEqual(mobileCbtQuestionIds($questions));
    foreach ($decrypted['questions'] as $i => $vaultQuestion) {
        expect($vaultQuestion['options'])->toEqual($questions[$i]['options']);
    }

    // And a later fetch still agrees.
    expect(($this->fetch)($this->userA, $exam))->toEqual($questions);
});

test('encrypted vault payload keeps authored option order when shuffle_options is off', function () {
    $exam = ($this->makeExam)(true, false);

    $this->enterCbtExam($this->userA, $exam);
    $payload = $this->getJson("/api/v1/student/cbt/exams/{$exam->id}/encrypted-payload")->assertOk()->json('data');
    $key = base64_decode($this->getJson("/api/v1/student/cbt/exams/{$exam->id}/decryption-key")->json('data.key'));
    $decrypted = json_decode(openssl_decrypt(
        base64_decode($payload['encrypted_data']), 'aes-256-cbc', $key, OPENSSL_RAW_DATA, base64_decode($payload['iv'])
    ), true);

    foreach ($decrypted['questions'] as $question) {
        expect(mobileCbtOptionKeys($question))->toEqual(['A', 'B', 'C', 'D', 'E']);
        expect($question['options'][0]['text'])->toStartWith('Q');
    }
});

test('scoring is correct when the student picks by displayed option with shuffled options', function () {
    $exam = ($this->makeExam)(true, true);
    $questions = ($this->fetch)($this->userA, $exam);

    // Pick the option whose *displayed text* is correct, send its key back,
    // exactly as CbtExamRoomScreen does (selectOption(question.id, option.key)).
    $answers = [];
    foreach ($questions as $question) {
        $picked = collect($question['options'])->firstWhere('text', $this->correctText[$question['id']]);
        $answers[$question['id']] = $picked['key'];
    }

    // Micro-sync stores the same keys (used later by force-close / autoGrade).
    Sanctum::actingAs($this->userA);
    $this->postJson("/api/v1/student/cbt/exams/{$exam->id}/micro-sync", ['answers' => $answers])->assertOk();

    $this->postJson("/api/v1/student/cbt/exams/{$exam->id}/submit", ['answers' => $answers])
        ->assertOk()
        ->assertJsonPath('data.correct_answers', 8)
        ->assertJsonPath('data.score', 100);
});

test('wrong displayed picks score zero with shuffled options', function () {
    $exam = ($this->makeExam)(true, true);
    $questions = ($this->fetch)($this->userA, $exam);

    // Always pick the first displayed option that is not the correct one.
    $answers = [];
    foreach ($questions as $question) {
        $picked = collect($question['options'])->first(fn ($o) => $o['text'] !== $this->correctText[$question['id']]);
        $answers[$question['id']] = $picked['key'];
    }

    Sanctum::actingAs($this->userA);
    $this->postJson("/api/v1/student/cbt/exams/{$exam->id}/submit", ['answers' => $answers])
        ->assertOk()
        ->assertJsonPath('data.correct_answers', 0)
        ->assertJsonPath('data.score', 0);
});

test('autoGrade after force-close scores shuffled-option answers by original key', function () {
    $exam = ($this->makeExam)(true, true);
    $questions = ($this->fetch)($this->userA, $exam);

    // Half right, half wrong, picked by displayed text.
    $answers = [];
    foreach ($questions as $i => $question) {
        $correct = $this->correctText[$question['id']];
        $picked = $i % 2 === 0
            ? collect($question['options'])->firstWhere('text', $correct)
            : collect($question['options'])->first(fn ($o) => $o['text'] !== $correct);
        $answers[(string) $question['id']] = $picked['key'];
    }

    Sanctum::actingAs($this->userA);
    $this->postJson("/api/v1/student/cbt/exams/{$exam->id}/force-close", [
        'reason' => 'app_minimized',
        'answers_snapshot' => $answers,
    ])->assertOk();

    $attempt = app(CbtService::class)->autoGrade(
        ExamAttempt::where('exam_id', $exam->id)->where('student_id', $this->studentA->id)->first()
    );

    expect($attempt->correct_count)->toBe(4);
    expect((float) $attempt->score)->toBe(50.0);
});

test('legacy associative options are shuffled and graded by key too', function () {
    $q = $this->questions[0];
    $q->update([
        'options' => ['A' => 'Mitokondria', 'B' => 'Ribosom', 'C' => 'Lisosom', 'D' => 'Vakuola', 'E' => 'Nukleus'],
        'correct_answer' => 'D',
    ]);
    $exam = ($this->makeExam)(true, true);

    $questions = ($this->fetch)($this->userA, $exam);
    $legacy = collect($questions)->firstWhere('id', $q->id);

    expect(collect(mobileCbtOptionKeys($legacy))->sort()->values()->all())->toEqual(['A', 'B', 'C', 'D', 'E']);
    $vakuola = collect($legacy['options'])->firstWhere('text', 'Vakuola');
    expect($vakuola['key'])->toBe('D');

    Sanctum::actingAs($this->userA);
    $this->postJson("/api/v1/student/cbt/exams/{$exam->id}/submit", ['answers' => [$q->id => $vakuola['key']]])
        ->assertOk()
        ->assertJsonPath('data.correct_answers', 1);
});
