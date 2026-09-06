<?php

use App\Models\User;
use App\Models\QuestionBankCategory;
use App\Models\QuestionBankItem;

beforeEach(function () {
    $this->guru = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);
});

test('guru can get question bank categories', function () {
    $response = $this->actingAs($this->guru)->getJson('/api/v1/question-bank/categories');
    $response->assertStatus(200)
             ->assertJsonStructure(['success', 'data']);
});

test('guru can get question bank items', function () {
    $response = $this->actingAs($this->guru)->getJson('/api/v1/question-bank/items');
    $response->assertStatus(200)
             ->assertJsonStructure(['success', 'data']);
});

test('guru can create question bank item', function () {
    $subject = \App\Models\Subject::factory()->create();
    $category = QuestionBankCategory::create([
        'subject_id' => $subject->id,
        'created_by' => $this->guru->id,
        'name' => 'Fisika Kuantum Test',
        'grade_level' => 'XII',
    ]);

    $response = $this->actingAs($this->guru)->postJson('/api/v1/question-bank/items', [
        'category_id' => $category->id,
        'question_text' => 'Berapakah energi foton dengan frekuensi $f$?',
        'correct_answer' => 'E = hf',
        'difficulty' => 'sedang',
        'type' => 'pilihan_ganda',
    ]);

    $response->assertStatus(201)
             ->assertJson(['success' => true]);
});

test('guru can auto-generate exam package from question bank', function () {
    $response = $this->actingAs($this->guru)->postJson('/api/v1/question-bank/auto-generate', [
        'total_questions' => 10,
        'difficulty_distribution' => [
            'mudah' => 30,
            'sedang' => 50,
            'sulit' => 20,
        ],
    ]);

    $response->assertStatus(200)
             ->assertJson(['success' => true]);
});
