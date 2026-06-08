<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Models\DailyAssessment;
use App\Models\DailyAssessmentScore;

beforeEach(function () {
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $this->classroom = Classroom::factory()->create();
    
    // Create teacher
    $this->guru = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);
    
    // Create student
    $this->studentUser = User::factory()->create(['role' => 'student', 'must_change_password' => false]);
    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'classroom_id' => $this->classroom->id
    ]);

    // Create parent
    $this->parentUser = User::factory()->create(['role' => 'parent', 'must_change_password' => false]);
    $this->student->update(['parent_id' => $this->parentUser->id]);

    $this->subject = Subject::create([
        'name' => 'Matematika',
        'code' => 'MTK',
        'category' => 'Nasional',
        'kkm' => 75
    ]);
});

test('guru can view daily assessments list', function () {
    $response = $this->actingAs($this->guru)->get(route('daily-assessments.index'));
    $response->assertStatus(200);
});

test('guru can create daily assessment', function () {
    $response = $this->actingAs($this->guru)->post(route('daily-assessments.store'), [
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'title' => 'Ulangan Harian Trigonometri',
        'type' => 'ulangan_harian',
        'assessment_date' => now()->format('Y-m-d'),
        'max_score' => 100,
        'kkm' => 75,
        'description' => 'Evaluasi Bab 3 Trigonometri'
    ]);

    // Should redirect to input scores page
    $assessment = DailyAssessment::latest()->first();
    $response->assertRedirect(route('daily-assessments.input-scores', $assessment->id));

    $this->assertDatabaseHas('daily_assessments', [
        'title' => 'Ulangan Harian Trigonometri',
        'type' => 'ulangan_harian',
        'classroom_id' => $this->classroom->id,
    ]);

    // Check that empty score draft was created for student
    $this->assertDatabaseHas('daily_assessment_scores', [
        'daily_assessment_id' => $assessment->id,
        'student_id' => $this->student->id,
        'score' => null,
        'needs_remedial' => false,
    ]);
});

test('guru can batch input student scores and auto detect remedial', function () {
    $assessment = DailyAssessment::create([
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'teacher_id' => $this->guru->id,
        'academic_year_id' => $this->academicYear->id,
        'title' => 'Kuis Logaritma',
        'type' => 'kuis',
        'assessment_date' => now()->format('Y-m-d'),
        'max_score' => 100,
        'kkm' => 75,
    ]);

    // Student score is below KKM (e.g. 60)
    $response = $this->actingAs($this->guru)->post(route('daily-assessments.store-scores', $assessment->id), [
        'grades' => [
            $this->student->id => [
                'score' => 60,
                'notes' => 'Kurang konsentrasi'
            ]
        ]
    ]);

    $response->assertRedirect(route('daily-assessments.show', $assessment->id));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('daily_assessment_scores', [
        'daily_assessment_id' => $assessment->id,
        'student_id' => $this->student->id,
        'score' => 60,
        'needs_remedial' => true,
        'remedial_status' => 'pending'
    ]);
});

test('remedial scores cap at KKM for final score calculation', function () {
    $assessment = DailyAssessment::create([
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'teacher_id' => $this->guru->id,
        'academic_year_id' => $this->academicYear->id,
        'title' => 'Kuis Logaritma',
        'type' => 'kuis',
        'assessment_date' => now()->format('Y-m-d'),
        'max_score' => 100,
        'kkm' => 75,
    ]);

    $scoreRecord = DailyAssessmentScore::create([
        'daily_assessment_id' => $assessment->id,
        'student_id' => $this->student->id,
        'score' => 60,
        'needs_remedial' => true,
        'remedial_status' => 'pending'
    ]);

    // Input remedial score of 90 (above KKM 75)
    $response = $this->actingAs($this->guru)->post(route('daily-assessments.store-remedial', $scoreRecord->id), [
        'remedial_score' => 90,
        'notes' => 'Sudah remedial'
    ]);

    $response->assertRedirect(route('daily-assessments.remedial'));
    $response->assertSessionHas('success');

    $scoreRecord->refresh();
    expect($scoreRecord->remedial_score)->toEqual(90.0);
    expect($scoreRecord->remedial_status)->toBe('completed');
    // Final score must be capped at KKM = 75
    expect($scoreRecord->final_score)->toEqual(75.0);
    expect($scoreRecord->is_passed)->toBeTrue();
});

test('student can view their daily assessments transcript', function () {
    $assessment = DailyAssessment::create([
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'teacher_id' => $this->guru->id,
        'academic_year_id' => $this->academicYear->id,
        'title' => 'Tugas Aljabar',
        'type' => 'tugas',
        'assessment_date' => now()->format('Y-m-d'),
        'max_score' => 100,
        'kkm' => 75,
    ]);

    DailyAssessmentScore::create([
        'daily_assessment_id' => $assessment->id,
        'student_id' => $this->student->id,
        'score' => 85,
        'needs_remedial' => false,
        'remedial_status' => 'not_needed'
    ]);

    $response = $this->actingAs($this->studentUser)->get(route('student.daily-assessments'));
    $response->assertStatus(200);
    $response->assertSee('Tugas Aljabar');
    $response->assertSee('85');
});

test('parent can view child daily assessments transcript', function () {
    $assessment = DailyAssessment::create([
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'teacher_id' => $this->guru->id,
        'academic_year_id' => $this->academicYear->id,
        'title' => 'Praktikum Kimia',
        'type' => 'praktikum',
        'assessment_date' => now()->format('Y-m-d'),
        'max_score' => 100,
        'kkm' => 75,
    ]);

    DailyAssessmentScore::create([
        'daily_assessment_id' => $assessment->id,
        'student_id' => $this->student->id,
        'score' => 92,
        'needs_remedial' => false,
        'remedial_status' => 'not_needed'
    ]);

    $response = $this->actingAs($this->parentUser)->get(route('parent-portal.daily-assessments', $this->student));
    $response->assertStatus(200);
    $response->assertSee('Praktikum Kimia');
    $response->assertSee('92');
});

test('unauthorized users cannot view or create daily assessments', function () {
    // Student trying to view guru index
    $response = $this->actingAs($this->studentUser)->get(route('daily-assessments.index'));
    $response->assertStatus(403);

    // Student trying to create assessment
    $response = $this->actingAs($this->studentUser)->post(route('daily-assessments.store'), [
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'title' => 'Illegal assessment',
    ]);
    $response->assertStatus(403);
});

test('analytics is calculated and displays correctly', function () {
    $assessment = DailyAssessment::create([
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'teacher_id' => $this->guru->id,
        'academic_year_id' => $this->academicYear->id,
        'title' => 'Project Statistik',
        'type' => 'project',
        'assessment_date' => now()->format('Y-m-d'),
        'max_score' => 100,
        'kkm' => 75,
    ]);

    DailyAssessmentScore::create([
        'daily_assessment_id' => $assessment->id,
        'student_id' => $this->student->id,
        'score' => 88,
        'needs_remedial' => false,
        'remedial_status' => 'not_needed'
    ]);

    $response = $this->actingAs($this->guru)->get(route('daily-assessments.analytics', [
        'classroom' => $this->classroom->id,
        'subject_id' => $this->subject->id
    ]));

    $response->assertStatus(200);
    $response->assertSee('Project Statistik');
});

test('guru can export daily assessment scores to PDF', function () {
    $assessment = DailyAssessment::create([
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'teacher_id' => $this->guru->id,
        'academic_year_id' => $this->academicYear->id,
        'title' => 'Presentasi Geometri',
        'type' => 'presentasi',
        'assessment_date' => now()->format('Y-m-d'),
        'max_score' => 100,
        'kkm' => 75,
    ]);

    DailyAssessmentScore::create([
        'daily_assessment_id' => $assessment->id,
        'student_id' => $this->student->id,
        'score' => 90,
        'needs_remedial' => false,
        'remedial_status' => 'not_needed'
    ]);

    $response = $this->actingAs($this->guru)->get(route('daily-assessments.pdf', $assessment->id));
    
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});
