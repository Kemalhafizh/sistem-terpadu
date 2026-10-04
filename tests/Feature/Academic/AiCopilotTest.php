<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\LessonPlan;
use App\Models\Subject;
use App\Models\User;
use App\Services\AiCopilotService;

beforeEach(function () {
    $this->teacher = User::factory()->create(['role' => 'guru']);
    $this->subject = Subject::factory()->create(['name' => 'Matematika']);
    $this->classroom = Classroom::factory()->create(['name' => 'X-A']);
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);

    $this->copilotService = app(AiCopilotService::class);
});

test('teacher can access copilot landing page', function () {
    $response = $this->actingAs($this->teacher)->get(route('academic.copilot.index'));
    $response->assertStatus(200);
    $response->assertSee('RPP Kurikulum Merdeka Writer');
    $response->assertSee('CBT Smart Question Creator');
});

test('teacher can generate RPP in local fallback mode', function () {
    $response = $this->actingAs($this->teacher)->post(route('academic.copilot.rpp.generate'), [
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'topic' => 'Aljabar',
        'competencies' => 'Memecahkan SPLDV',
        'provider' => 'local_fallback',
    ]);

    $response->assertStatus(200);
    $response->assertSee('IDENTITAS MODUL');
    $response->assertSee('Aljabar');
    $response->assertSee('Memecahkan SPLDV');
});

test('teacher can save generated RPP as a draft in SISTA', function () {
    $rppMarkdown = '# RENCANA PELAKSANAAN PEMBELAJARAN (RPP) KURIKULUM MERDEKA
## 2. TUJUAN PEMBELAJARAN
- Siswa mampu menyelesaikan SPLDV.
## 5. ASESMEN
- Tes tertulis.
## 6. MEDIA
- Buku paket.';

    $response = $this->actingAs($this->teacher)->post(route('academic.copilot.rpp.save'), [
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'topic' => 'Aljabar',
        'competencies' => 'Memecahkan SPLDV',
        'rpp_content' => $rppMarkdown,
    ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('curriculum.lesson-plans.index'));

    $this->assertDatabaseHas('lesson_plans', [
        'user_id' => $this->teacher->id,
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'topic' => 'Aljabar',
        'status' => 'draft',
    ]);

    $plan = LessonPlan::where('topic', 'Aljabar')->first();
    expect($plan->learning_objectives)->toContain('Siswa mampu menyelesaikan SPLDV.');
});

test('CBT generation without an AI provider returns an error, not template questions', function () {
    // This used to "generate" template questions with a random answer key.
    $response = $this->actingAs($this->teacher)->from(route('academic.copilot.cbt'))->post(route('academic.copilot.cbt.generate'), [
        'subject_id' => $this->subject->id,
        'material_text' => 'Ini adalah materi pembelajaran sejarah proklamasi kemerdekaan Republik Indonesia yang dibacakan oleh Ir. Soekarno pada tanggal 17 Agustus 1945.',
        'type' => 'multiple_choice',
        'count' => 3,
        'difficulty' => 'medium',
        'provider' => 'local_fallback',
    ]);

    $response->assertRedirect(route('academic.copilot.cbt'));
    $response->assertSessionHas('error');
});

test('teacher can save generated CBT questions to Question Banks', function () {
    $response = $this->actingAs($this->teacher)->post(route('academic.copilot.cbt.save'), [
        'subject_id' => $this->subject->id,
        'selected_questions' => [0, 1], // import index 0 and 1
        'questions' => [
            [
                'question_text' => 'Siapakah pembaca teks proklamasi?',
                'type' => 'multiple_choice',
                'options' => [
                    'A' => 'Soekarno',
                    'B' => 'Hatta',
                ],
                'correct_answer' => 'A',
                'explanation' => 'Teks dibacakan oleh Ir. Soekarno.',
                'difficulty' => 'easy',
            ],
            [
                'question_text' => 'Di mana teks proklamasi dibacakan?',
                'type' => 'essay',
                'correct_answer' => 'Jalan Pegangsaan Timur No. 56',
                'explanation' => 'Dibacakan di kediaman Soekarno.',
                'difficulty' => 'easy',
            ],
        ],
    ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('cbt.question-banks.index'));

    $this->assertDatabaseHas('question_banks', [
        'subject_id' => $this->subject->id,
        'question_text' => 'Siapakah pembaca teks proklamasi?',
        'correct_answer' => 'A',
    ]);

    $this->assertDatabaseHas('question_banks', [
        'subject_id' => $this->subject->id,
        'question_text' => 'Di mana teks proklamasi dibacakan?',
        'correct_answer' => 'Jalan Pegangsaan Timur No. 56',
    ]);
});
