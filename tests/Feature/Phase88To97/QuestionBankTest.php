<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\QuestionBankCategory;
use App\Models\QuestionBankItem;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;

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
    $subject = Subject::factory()->create();
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

/*
 * question-bank/auto-generate with an exam_id deletes that exam's questions and
 * links new ones. It used to accept any exam from any teacher, including one
 * students were already taking. The categories also never said how many easy,
 * medium and hard items they hold, so the app made up a 30/50/20 split.
 */

function questionBankExam(User $creator): Exam
{
    return Exam::create([
        'title' => 'PH Fisika', 'subject_id' => Subject::factory()->create()->id,
        'classroom_id' => Classroom::factory()->create()->id,
        'academic_year_id' => AcademicYear::factory()->create()->id, 'created_by' => $creator->id,
        'type' => 'ulangan_harian', 'start_time' => now()->addDay(), 'end_time' => now()->addDay()->addHour(),
        'duration_minutes' => 60, 'total_questions' => 1, 'status' => 'published',
    ]);
}

function questionBankItems(int $count, string $difficulty, QuestionBankCategory $category, User $creator): void
{
    foreach (range(1, $count) as $i) {
        QuestionBankItem::create([
            'category_id' => $category->id, 'question_text' => "Soal {$difficulty} {$i} ".uniqid(), 'correct_answer' => 'A',
            'difficulty' => $difficulty, 'cognitive_level' => 'C2', 'type' => 'pilihan_ganda', 'created_by' => $creator->id,
        ]);
    }
}

test('guru lain tidak bisa mengganti soal ujian lewat auto-generate', function () {
    $owner = User::factory()->create(['role' => 'guru']);
    $exam = questionBankExam($owner);
    $category = QuestionBankCategory::create(['name' => 'Gerak lurus', 'subject_id' => Subject::factory()->create()->id, 'grade_level' => 'X', 'created_by' => $owner->id]);
    questionBankItems(1, 'sedang', $category, $owner);
    DB::table('exam_question_links')->insert([
        'exam_id' => $exam->id, 'question_bank_item_id' => QuestionBankItem::sole()->id, 'order_number' => 1, 'weight' => 1,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->actingAs($this->guru)->postJson('/api/v1/question-bank/auto-generate', ['total_questions' => 5, 'exam_id' => $exam->id])
        ->assertForbidden()
        ->assertJsonPath('message', 'Hanya pembuat ujian yang dapat mengganti soal ujian ini.');

    expect(DB::table('exam_question_links')->where('exam_id', $exam->id)->count())->toBe(1);
});

test('soal ujian yang sudah dikerjakan siswa tidak bisa diganti', function () {
    $exam = questionBankExam($this->guru);
    ExamAttempt::create([
        'exam_id' => $exam->id, 'student_id' => Student::factory()->create()->id,
        'status' => 'in_progress', 'started_at' => now(),
    ]);

    $this->actingAs($this->guru)->postJson('/api/v1/question-bank/auto-generate', ['total_questions' => 5, 'exam_id' => $exam->id])
        ->assertStatus(422);
});

test('pembuat ujian bisa mengisi soal ujiannya dari bank soal', function () {
    $category = QuestionBankCategory::create(['name' => 'Gerak lurus', 'subject_id' => Subject::factory()->create()->id, 'grade_level' => 'X', 'created_by' => $this->guru->id]);
    questionBankItems(2, 'mudah', $category, $this->guru);
    $exam = questionBankExam($this->guru);

    $this->actingAs($this->guru)->postJson('/api/v1/question-bank/auto-generate', [
        'total_questions' => 2, 'exam_id' => $exam->id, 'category_id' => $category->id,
    ])->assertOk()->assertJsonPath('data.total_generated', 2);

    expect(DB::table('exam_question_links')->where('exam_id', $exam->id)->count())->toBe(2);
});

test('kategori mengirim jumlah soal per tingkat kesulitan yang sebenarnya', function () {
    $category = QuestionBankCategory::create(['name' => 'Hukum Newton', 'subject_id' => Subject::factory()->create()->id, 'grade_level' => 'XI', 'created_by' => $this->guru->id]);
    questionBankItems(1, 'mudah', $category, $this->guru);
    questionBankItems(3, 'sulit', $category, $this->guru);

    $row = collect($this->actingAs($this->guru)->getJson('/api/v1/question-bank/categories')->assertOk()->json('data'))
        ->firstWhere('id', $category->id);

    expect($row['items_count'])->toBe(4)
        ->and($row['easy_count'])->toBe(1)
        ->and($row['medium_count'])->toBe(0)
        ->and($row['hard_count'])->toBe(3);
});
