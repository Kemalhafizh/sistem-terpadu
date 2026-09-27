<?php

use App\Models\CatItemResponse;
use App\Models\IrtItemParameter;
use App\Models\QuestionBank;
use App\Models\Subject;
use App\Models\User;
use App\Services\Adaptive\IrtCalibrationService;

/*
 * Regression: irt_item_parameters / cat_item_responses referenced a
 * "questions" table and an App\Models\Question class that never existed, so
 * every insert failed and ->question() crashed. Items are CBT bank rows.
 */

beforeEach(function () {
    $this->question = QuestionBank::create([
        'subject_id' => Subject::factory()->create()->id,
        'created_by' => User::factory()->create(['role' => 'guru'])->id,
        'question_text' => 'Berapakah turunan dari x^2?',
        'type' => 'multiple_choice',
        'options' => ['A' => 'x', 'B' => '2x'],
        'correct_answer' => 'B',
        'difficulty' => 'medium',
        'grade_level' => 'XI',
    ]);
});

test('kalibrasi IRT tersimpan untuk soal bank CBT', function () {
    $item = app(IrtCalibrationService::class)->calibrateItem($this->question->id);

    expect($item->exists)->toBeTrue()
        ->and($item->question)->toBeInstanceOf(QuestionBank::class)
        ->and($item->question->is($this->question))->toBeTrue();
});

test('parameter IRT ikut terhapus saat soal dihapus permanen', function () {
    IrtItemParameter::create(['question_id' => $this->question->id]);

    $this->question->forceDelete();

    expect(IrtItemParameter::count())->toBe(0);
});

test('respons CAT merujuk soal bank CBT', function () {
    $response = new CatItemResponse(['question_id' => $this->question->id]);

    expect($response->question)->toBeInstanceOf(QuestionBank::class)
        ->and($response->question->question_text)->toBe('Berapakah turunan dari x^2?');
});
