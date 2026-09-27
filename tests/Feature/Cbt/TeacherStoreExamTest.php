<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

/**
 * POST /api/v1/teacher/cbt/exams — manual exam authoring from the Android app.
 * The server never accepts a client token and never falls back to fake
 * subject/grade/academic-year values.
 */
beforeEach(function () {
    $this->subject = Subject::factory()->create(['name' => 'Matematika']);
    $this->classroom = Classroom::factory()->create(['name' => 'XI IPA 1', 'grade_level' => 'XI']);
    $this->year = AcademicYear::factory()->create(['is_active' => true]);
    $this->teacher = User::factory()->create(['role' => 'guru']);

    $this->payload = fn (array $overrides = []) => array_merge([
        'title' => 'Ulangan Harian Limit',
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'duration_minutes' => 60,
        'passing_score' => 75,
        'questions' => [[
            'question_text' => 'Berapa 1 + 1?',
            'options' => [
                ['key' => 'A', 'text' => '2'],
                ['key' => 'B', 'text' => '3'],
            ],
            'correct_answer' => 'A',
        ]],
    ], $overrides);
});

test('guru membuat ujian nyata; token tidak dibuat klien dan pengawas bisa membuka token server', function () {
    Sanctum::actingAs($this->teacher);

    $res = $this->postJson('/api/v1/teacher/cbt/exams', ($this->payload)(['token' => 'SA1-1234']))
        ->assertCreated()
        ->assertJsonPath('data.type', 'ulangan_harian')
        ->assertJsonPath('data.classroom', 'XI IPA 1')
        ->assertJsonPath('data.total_questions', 1)
        ->assertJsonPath('data.can_view_token', true)
        ->assertJsonMissingPath('data.token');

    $exam = Exam::findOrFail($res->json('data.exam_id'));
    expect($exam->created_by)->toBe($this->teacher->id)
        ->and($exam->academic_year_id)->toBe($this->year->id)
        ->and($exam->access_token)->toBeNull()
        ->and($exam->questions()->first()->grade_level)->toBe('XI');

    $token = $this->getJson("/api/v1/teacher/cbt/exams/{$exam->id}/token")->assertOk()->json('data.access_token');
    expect($token)->toHaveLength(6)->not->toBe('SA1-1234');
});

test('validasi menolak data palsu atau tidak lengkap', function () {
    Sanctum::actingAs($this->teacher);

    $this->postJson('/api/v1/teacher/cbt/exams', ($this->payload)(['subject_id' => null, 'subject_name' => 'Mat']))
        ->assertUnprocessable()->assertJsonValidationErrors('subject_id');

    $this->postJson('/api/v1/teacher/cbt/exams', ($this->payload)([
        'questions' => [[
            'question_text' => 'Soal',
            'options' => [['key' => 'A', 'text' => 'x'], ['key' => 'B', 'text' => '']],
            'correct_answer' => 'C',
            'image_url' => 'content://media/external/images/1',
        ]],
    ]))->assertUnprocessable()->assertJsonValidationErrors(['questions.0.image_url']);

    $this->postJson('/api/v1/teacher/cbt/exams', ($this->payload)([
        'questions' => [[
            'question_text' => 'Soal',
            'options' => [['key' => 'A', 'text' => 'x'], ['key' => 'B', 'text' => '']],
            'correct_answer' => 'C',
        ]],
    ]))->assertUnprocessable()->assertJsonValidationErrors(['questions.0.correct_answer', 'questions.0.options']);

    expect(Exam::count())->toBe(0);
});

test('tanpa tahun ajaran aktif, ujian tidak dibuat', function () {
    $this->year->update(['is_active' => false]);
    Sanctum::actingAs($this->teacher);

    $this->postJson('/api/v1/teacher/cbt/exams', ($this->payload)())->assertUnprocessable();
    expect(Exam::count())->toBe(0);
});
