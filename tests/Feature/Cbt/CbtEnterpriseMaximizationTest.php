<?php

use App\Events\ProctorInterventionEvent;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\QuestionBank;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\EntersCbtExam;

uses(EntersCbtExam::class);

beforeEach(function () {
    $this->subject = Subject::factory()->create(['name' => 'Fisika']);
    $this->classroom = Classroom::factory()->create();
    $this->academicYear = AcademicYear::factory()->create();

    $this->teacher = User::factory()->create(['role' => 'guru']);

    $this->studentUser = User::factory()->create(['role' => 'student']);
    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'classroom_id' => $this->classroom->id,
    ]);

    // Create 3 questions
    $this->questions = [];
    foreach (['A', 'B', 'C'] as $i => $correct) {
        $this->questions[] = QuestionBank::create([
            'subject_id' => $this->subject->id,
            'created_by' => $this->teacher->id,
            'question_text' => 'Soal Fisika Nomor '.($i + 1),
            'type' => 'multiple_choice',
            'options' => ['A' => 'Opsi A', 'B' => 'Opsi B', 'C' => 'Opsi C', 'D' => 'Opsi D'],
            'correct_answer' => $correct,
            'difficulty' => 'medium',
            'grade_level' => 'XII',
        ]);
    }

    // Create active exam
    $this->exam = Exam::create([
        'title' => 'PAS Fisika Terpadu XII',
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'academic_year_id' => $this->academicYear->id,
        'created_by' => $this->teacher->id,
        'type' => 'uas',
        'start_time' => now()->subMinutes(10),
        'end_time' => now()->addHours(2),
        'duration_minutes' => 90,
        'total_questions' => 3,
        'shuffle_questions' => true,
        'shuffle_options' => true,
        'is_secure_mode' => true,
        'max_violations' => 3,
        'passing_score' => 75,
        'status' => 'published',
    ]);

    foreach ($this->questions as $order => $q) {
        $this->exam->questions()->attach($q->id, ['order' => $order + 1, 'weight' => 1.0]);
    }
});

test('siswa dapat mengunduh paket soal terenkripsi untuk zero-downtime pre-fetching', function () {
    Sanctum::actingAs($this->studentUser);

    $response = $this->getJson("/api/v1/student/cbt/exams/{$this->exam->id}/encrypted-payload");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'exam_id',
                'encrypted_data',
                'iv',
                'format',
                'fetched_at',
            ],
        ]);

    expect($response->json('data.format'))->toBe('aes-256-cbc');
    expect($response->json('data.encrypted_data'))->not->toBeEmpty();

    // Pre-fetching is not entering the exam: only validate-token opens an attempt.
    expect(ExamAttempt::where('exam_id', $this->exam->id)->exists())->toBeFalse();
});

test('siswa dapat mengambil kunci dekripsi saat jadwal ujian aktif', function () {
    $this->enterCbtExam($this->studentUser, $this->exam);

    $response = $this->getJson("/api/v1/student/cbt/exams/{$this->exam->id}/decryption-key");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'accessible',
                'exam_id',
                'key',
                'valid_until',
            ],
        ]);

    expect($response->json('data.accessible'))->toBeTrue();
    expect($response->json('data.key'))->not->toBeEmpty();
});

test('kunci dekripsi ditolak jika ujian belum memasuki jadwal', function () {
    $futureExam = Exam::create([
        'title' => 'Ujian Masa Depan',
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'academic_year_id' => $this->academicYear->id,
        'created_by' => $this->teacher->id,
        'type' => 'uas',
        'start_time' => now()->addDays(2),
        'end_time' => now()->addDays(2)->addHours(2),
        'duration_minutes' => 90,
        'total_questions' => 3,
        'status' => 'published',
    ]);

    Sanctum::actingAs($this->studentUser);

    $response = $this->getJson("/api/v1/student/cbt/exams/{$futureExam->id}/decryption-key");

    $response->assertStatus(403);
});

test('siswa dapat melakukan micro-sync jawaban ke backend', function () {
    $this->enterCbtExam($this->studentUser, $this->exam);

    $answers = [
        (string) $this->questions[0]->id => 'A',
        (string) $this->questions[1]->id => 'C',
    ];

    $response = $this->postJson("/api/v1/student/cbt/exams/{$this->exam->id}/micro-sync", [
        'answers' => $answers,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'synced_count' => 2,
                'status' => 'saved',
            ],
        ]);

    $attempt = ExamAttempt::where('exam_id', $this->exam->id)
        ->where('student_id', $this->student->id)
        ->first();

    expect($attempt)->not->toBeNull();
    expect($attempt->answers[(string) $this->questions[0]->id])->toBe('A');
});

test('siswa dapat mengunggah foto lembar jawaban esai tulis tangan', function () {
    Storage::fake('public');
    $this->enterCbtExam($this->studentUser, $this->exam);

    $fakeImage = UploadedFile::fake()->image('coretan_fisika.jpg', 800, 600);

    $response = $this->postJson("/api/v1/student/cbt/exams/{$this->exam->id}/upload-essay", [
        'question_id' => $this->questions[0]->id,
        'file' => $fakeImage,
        'student_notes' => 'Hitungan gaya gesek nomor 1',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'submission_id',
                'file_url',
                'question_id',
            ],
        ]);

    $this->assertDatabaseHas('cbt_essay_submissions', [
        'question_id' => $this->questions[0]->id,
        'student_notes' => 'Hitungan gaya gesek nomor 1',
    ]);
});

test('guru pengawas dapat mengirim peringatan intervensi, tambah waktu, dan submit paksa', function () {
    Event::fake();
    Sanctum::actingAs($this->teacher);

    // 1. Broadcast Warning
    $resWarn = $this->postJson("/api/v1/teacher/cbt/exams/{$this->exam->id}/proctor/warning", [
        'student_id' => $this->student->id,
        'message' => 'Teguran: Pandangan mata dilarang menoleh keluar layar!',
    ]);
    $resWarn->assertStatus(200);

    // 2. Extend Time
    $resTime = $this->postJson("/api/v1/teacher/cbt/exams/{$this->exam->id}/proctor/extend-time", [
        'student_id' => $this->student->id,
        'minutes' => 15,
    ]);
    $resTime->assertStatus(200);

    // 3. Force Submit
    $resSubmit = $this->postJson("/api/v1/teacher/cbt/exams/{$this->exam->id}/proctor/force-submit", [
        'student_id' => $this->student->id,
        'reason' => 'Pelanggaran berat: Menggunakan HP kedua di samping meja',
    ]);
    $resSubmit->assertStatus(200);

    // Verify DB Logs
    $this->assertDatabaseHas('cbt_proctor_logs', [
        'exam_id' => $this->exam->id,
        'student_id' => $this->student->id,
        'action_type' => 'warning_modal',
    ]);

    $this->assertDatabaseHas('cbt_proctor_logs', [
        'exam_id' => $this->exam->id,
        'student_id' => $this->student->id,
        'action_type' => 'extend_time',
    ]);

    $this->assertDatabaseHas('cbt_proctor_logs', [
        'exam_id' => $this->exam->id,
        'student_id' => $this->student->id,
        'action_type' => 'force_submit',
    ]);

    Event::assertDispatched(ProctorInterventionEvent::class, 3);
});
