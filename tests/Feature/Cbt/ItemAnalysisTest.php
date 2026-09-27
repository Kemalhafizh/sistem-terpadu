<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\QuestionBank;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\ItemAnalysisService;

beforeEach(function () {
    // 1. Setup Users
    $this->guru = User::factory()->create(['role' => 'guru', 'name' => 'Ahmad Guru']);
    $this->guruLain = User::factory()->create(['role' => 'guru', 'name' => 'Siti Guru']);
    $this->wakakur = User::factory()->create(['role' => 'waka_kurikulum', 'name' => 'Bambang Wakakur']);
    $this->studentUser = User::factory()->create(['role' => 'siswa']);
    $this->student = Student::factory()->create(['user_id' => $this->studentUser->id]);

    // 2. Setup Academic Data
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true, 'year' => '2026/2027']);
    $this->classroom = Classroom::factory()->create(['academic_year' => $this->academicYear->year]);
    $this->student->update(['classroom_id' => $this->classroom->id]);
    $this->subject = Subject::factory()->create(['name' => 'Fisika']);

    // 3. Create Questions in Question Bank
    $this->q1 = QuestionBank::create([
        'subject_id' => $this->subject->id,
        'created_by' => $this->guru->id,
        'question_text' => 'Berapakah 2 + 2?',
        'type' => 'multiple_choice',
        'options' => ['A' => '2', 'B' => '4', 'C' => '6', 'D' => '8'],
        'correct_answer' => 'B',
        'difficulty' => 'easy',
        'grade_level' => 'X',
    ]);

    $this->q2 = QuestionBank::create([
        'subject_id' => $this->subject->id,
        'created_by' => $this->guru->id,
        'question_text' => 'Berapakah 3 x 3?',
        'type' => 'multiple_choice',
        'options' => ['A' => '6', 'B' => '9', 'C' => '12', 'D' => '15'],
        'correct_answer' => 'B',
        'difficulty' => 'medium',
        'grade_level' => 'X',
    ]);

    // 4. Create Exam
    $this->exam = Exam::factory()->create([
        'title' => 'Ujian Harian Matematika',
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'academic_year_id' => $this->academicYear->id,
        'created_by' => $this->guru->id,
        'type' => 'ulangan_harian',
        'start_time' => now()->subDays(1),
        'end_time' => now()->addDays(1),
        'duration_minutes' => 60,
        'total_questions' => 2,
        'shuffle_questions' => false,
        'shuffle_options' => false,
        'passing_score' => 75.0,
        'status' => 'published',
    ]);

    // Attach questions with weight
    $this->exam->questions()->attach($this->q1->id, ['order' => 1, 'weight' => 1.00]);
    $this->exam->questions()->attach($this->q2->id, ['order' => 2, 'weight' => 1.00]);
});

// =========================================================
// 🔒 AUTHORIZATION TESTS
// =========================================================

test('authorized users can access item analysis views', function () {
    // 1. Guru who created the exam can access
    $response = $this->actingAs($this->guru)->get(route('cbt.exams.analysis', $this->exam));
    $response->assertStatus(200);

    // 2. Wakakur can access
    $response = $this->actingAs($this->wakakur)->get(route('cbt.exams.analysis', $this->exam));
    $response->assertStatus(200);
});

test('unauthorized users cannot access item analysis views', function () {
    // 1. Siswa is forbidden (403)
    $response = $this->actingAs($this->studentUser)->get(route('cbt.exams.analysis', $this->exam));
    $response->assertStatus(403);

    // 2. Guru who did not create the exam is forbidden (403)
    $response = $this->actingAs($this->guruLain)->get(route('cbt.exams.analysis', $this->exam));
    $response->assertStatus(403);
});

// =========================================================
// 📊 SERVICE & METRIC CALCULATIONS
// =========================================================

test('calculates correct difficulty index and text', function () {
    // Create 4 student attempts
    // Student 1: Both correct (Score 100)
    // Student 2: Both correct (Score 100)
    // Student 3: Q1 correct, Q2 wrong (Score 50)
    // Student 4: Both wrong (Score 0)
    // Total participants: 4
    // Q1 Correct count: 3. Difficulty index = 3/4 = 0.75 (Mudah)
    // Q2 Correct count: 2. Difficulty index = 2/4 = 0.50 (Sedang)

    $students = Student::factory()->count(4)->create(['classroom_id' => $this->classroom->id]);

    $answers = [
        ['q1' => 'B', 'q2' => 'B', 'score' => 100, 'correct' => 2, 'wrong' => 0],
        ['q1' => 'B', 'q2' => 'B', 'score' => 100, 'correct' => 2, 'wrong' => 0],
        ['q1' => 'B', 'q2' => 'A', 'score' => 50,  'correct' => 1, 'wrong' => 1],
        ['q1' => 'A', 'q2' => 'A', 'score' => 0,   'correct' => 0, 'wrong' => 2],
    ];

    foreach ($students as $idx => $student) {
        $data = $answers[$idx];
        ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'student_id' => $student->id,
            'answers' => [
                $this->q1->id => $data['q1'],
                $this->q2->id => $data['q2'],
            ],
            'score' => $data['score'],
            'correct_count' => $data['correct'],
            'wrong_count' => $data['wrong'],
            'status' => 'graded',
        ]);
    }

    $service = new ItemAnalysisService;
    $diffMap = $service->calculateDifficultyIndex($this->exam->id);

    $q1Diff = $diffMap->get($this->q1->id);
    $q2Diff = $diffMap->get($this->q2->id);

    $this->assertEquals(0.75, $q1Diff['difficulty_index']);
    $this->assertEquals('Mudah', $q1Diff['difficulty_text']);

    $this->assertEquals(0.50, $q2Diff['difficulty_index']);
    $this->assertEquals('Sedang', $q2Diff['difficulty_text']);
});

test('calculates discrimination index using 27 percent rule', function () {
    // Create 10 student attempts to test group division
    // Sort scores:
    // Top group size = round(0.27 * 10) = 3 students
    // Bottom group size = round(0.27 * 10) = 3 students
    // Upper group scores: 100, 100, 100
    // Lower group scores: 0, 0, 0
    // If Q1 is correct for all 3 upper group and 0 lower group, D = 3/3 - 0/3 = 1.0 (Baik)
    // If Q2 is correct for all 3 upper group and 1 lower group, D = 3/3 - 1/3 = 0.67 (Baik)

    $students = Student::factory()->count(10)->create(['classroom_id' => $this->classroom->id]);

    // 3 high performers, 4 average, 3 low performers
    $performance = [
        ['q1' => 'B', 'q2' => 'B', 'score' => 100], // Upper
        ['q1' => 'B', 'q2' => 'B', 'score' => 100], // Upper
        ['q1' => 'B', 'q2' => 'B', 'score' => 100], // Upper
        ['q1' => 'B', 'q2' => 'A', 'score' => 50],
        ['q1' => 'B', 'q2' => 'A', 'score' => 50],
        ['q1' => 'B', 'q2' => 'A', 'score' => 50],
        ['q1' => 'B', 'q2' => 'A', 'score' => 50],
        ['q1' => 'A', 'q2' => 'B', 'score' => 50], // Lower (correct Q2, wrong Q1)
        ['q1' => 'A', 'q2' => 'A', 'score' => 0],  // Lower
        ['q1' => 'A', 'q2' => 'A', 'score' => 0],  // Lower
    ];

    foreach ($students as $idx => $student) {
        $data = $performance[$idx];
        ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'student_id' => $student->id,
            'answers' => [
                $this->q1->id => $data['q1'],
                $this->q2->id => $data['q2'],
            ],
            'score' => $data['score'],
            'correct_count' => $data['score'] == 100 ? 2 : ($data['score'] == 50 ? 1 : 0),
            'wrong_count' => $data['score'] == 100 ? 0 : ($data['score'] == 50 ? 1 : 2),
            'status' => 'graded',
        ]);
    }

    $service = new ItemAnalysisService;
    $discMap = $service->calculateDiscriminationIndex($this->exam->id);

    $q1Disc = $discMap->get($this->q1->id);
    $q2Disc = $discMap->get($this->q2->id);

    // Upper group correct Q1: 3/3 = 1.0. Lower group correct Q1: 0/3 = 0.0. D = 1.0
    $this->assertEquals(1.00, $q1Disc['discrimination_index']);
    $this->assertEquals('Baik', $q1Disc['discrimination_text']);

    // Upper group correct Q2: 3/3 = 1.0. Lower group correct Q2: 1/3 = 0.33. D = 0.67
    $this->assertEquals(0.67, $q2Disc['discrimination_index']);
    $this->assertEquals('Baik', $q2Disc['discrimination_text']);
});

test('analyzes distractor effectiveness properly', function () {
    // If we have 20 participants:
    // Option A (distractor) selected by 2 students -> 2/20 = 10% (Effective >= 5%)
    // Option C (distractor) selected by 0 students -> 0% (Ineffective)
    // Option D (distractor) selected by 3 students -> 15% (Effective >= 5%)

    $students = Student::factory()->count(20)->create(['classroom_id' => $this->classroom->id]);

    // q1 answer distribution:
    // 15 B (Correct)
    // 2 A (Distractor 1)
    // 0 C (Distractor 2)
    // 3 D (Distractor 3)
    $selections = array_merge(
        array_fill(0, 15, 'B'),
        array_fill(0, 2, 'A'),
        array_fill(0, 0, 'C'),
        array_fill(0, 3, 'D')
    );

    foreach ($students as $idx => $student) {
        $ans = $selections[$idx];
        ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'student_id' => $student->id,
            'answers' => [
                $this->q1->id => $ans,
                $this->q2->id => 'B',
            ],
            'score' => $ans === 'B' ? 100 : 50,
            'correct_count' => $ans === 'B' ? 2 : 1,
            'wrong_count' => $ans === 'B' ? 0 : 1,
            'status' => 'graded',
        ]);
    }

    $service = new ItemAnalysisService;
    $distMap = $service->analyzeDistractors($this->exam->id);

    $q1Distractors = $distMap->get($this->q1->id);

    $this->assertTrue($q1Distractors['A']['is_effective']);
    $this->assertFalse($q1Distractors['C']['is_effective']);
    $this->assertTrue($q1Distractors['D']['is_effective']);
    $this->assertNull($q1Distractors['B']['is_effective']); // Correct answer has null is_effective
});

// =========================================================
// 🖨️ PDF EXPORT & INTEGRATION
// =========================================================

test('authorized users can download item analysis PDF report', function () {
    // Seed some attempts first
    $students = Student::factory()->count(5)->create(['classroom_id' => $this->classroom->id]);
    foreach ($students as $student) {
        ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'student_id' => $student->id,
            'answers' => [$this->q1->id => 'B', $this->q2->id => 'A'],
            'score' => 50,
            'correct_count' => 1,
            'wrong_count' => 1,
            'status' => 'graded',
        ]);
    }

    $response = $this->actingAs($this->guru)->get(route('cbt.exams.analysis.pdf', $this->exam));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});
