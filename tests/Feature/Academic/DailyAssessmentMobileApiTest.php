<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\DailyAssessment;
use App\Models\DailyAssessmentScore;
use App\Models\RemedialSession;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;

beforeEach(function () {
    $this->guru = User::factory()->create(['role' => 'guru', 'must_change_password' => false, 'name' => 'Bu Guru']);
    $this->classroom = Classroom::factory()->create(['name' => 'XI MIPA 2']);
    $this->subject = Subject::factory()->create(['name' => 'Fisika']);
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);

    $this->ani = Student::factory()->create([
        'user_id' => User::factory()->create(['role' => 'student', 'name' => 'Ani'])->id,
        'classroom_id' => $this->classroom->id,
    ]);
    $this->budi = Student::factory()->create([
        'user_id' => User::factory()->create(['role' => 'student', 'name' => 'Budi'])->id,
        'classroom_id' => $this->classroom->id,
    ]);

    $this->assessment = DailyAssessment::create([
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'teacher_id' => $this->guru->id,
        'academic_year_id' => $this->academicYear->id,
        'title' => 'UH 1 Dinamika',
        'type' => 'ulangan_harian',
        'assessment_date' => '2026-09-21',
        'max_score' => 100,
        'kkm' => 75,
    ]);
    DailyAssessmentScore::create([
        'daily_assessment_id' => $this->assessment->id,
        'student_id' => $this->ani->id,
        'score' => 82.5,
        'notes' => 'Rapi',
    ]);
});

test('teacher gets the class roster with stored scores and blanks for the rest', function () {
    $response = $this->actingAs($this->guru)->getJson("/api/v1/assessments/{$this->assessment->id}/scores");

    $response->assertOk()
        ->assertJsonPath('data.assessment.title', 'UH 1 Dinamika')
        ->assertJsonPath('data.assessment.kkm', 75)
        ->assertJsonPath('data.assessment.max_score', 100)
        ->assertJsonPath('data.assessment.classroom_name', 'XI MIPA 2')
        ->assertJsonPath('data.assessment.assessment_date', '2026-09-21')
        ->assertJsonCount(2, 'data.students');

    $rows = collect($response->json('data.students'))->keyBy('student_id');
    expect($rows[$this->ani->id]['score'])->toEqual(82.5)
        ->and($rows[$this->ani->id]['notes'])->toBe('Rapi')
        ->and($rows[$this->budi->id]['score'])->toBeNull()
        ->and($rows[$this->ani->id]['name'])->toBe('Ani');
});

test('another teacher cannot read the scores of an assessment they do not own', function () {
    $other = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);

    $this->actingAs($other)->getJson("/api/v1/assessments/{$this->assessment->id}/scores")->assertForbidden();
});

test('students cannot read an assessment roster', function () {
    $this->actingAs(User::find($this->ani->user_id))
        ->getJson("/api/v1/assessments/{$this->assessment->id}/scores")
        ->assertForbidden();
});

test('a student sees their own scores, looked up by students.id', function () {
    $response = $this->actingAs(User::find($this->ani->user_id))->getJson('/api/v1/assessments/student');

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.original_score'))->toEqual(82.5)
        ->and($response->json('data.0.final_score'))->toEqual(82.5);
});

test('a student never sees the scores of the student whose students.id equals their users.id', function () {
    // Make a stranger's students.id equal to Ani's users.id — the old lookup by
    // users.id returned exactly this stranger's scores to Ani.
    $strangerId = $this->ani->user_id;
    expect(Student::whereKey($strangerId)->exists())->toBeFalse();
    $stranger = Student::factory()->create([
        'id' => $strangerId,
        'user_id' => User::factory()->create(['role' => 'student'])->id,
        'classroom_id' => $this->classroom->id,
    ]);
    DailyAssessmentScore::create([
        'daily_assessment_id' => $this->assessment->id,
        'student_id' => $stranger->id,
        'score' => 40,
    ]);

    $response = $this->actingAs(User::find($this->ani->user_id))->getJson('/api/v1/assessments/student');

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.original_score'))->toEqual(82.5);
});

test('scores not yet entered are not shown to the student as zero', function () {
    DailyAssessmentScore::create([
        'daily_assessment_id' => DailyAssessment::create([
            'subject_id' => $this->subject->id,
            'classroom_id' => $this->classroom->id,
            'teacher_id' => $this->guru->id,
            'academic_year_id' => $this->academicYear->id,
            'title' => 'UH 2',
            'type' => 'ulangan_harian',
            'assessment_date' => '2026-09-28',
            'max_score' => 100,
            'kkm' => 75,
        ])->id,
        'student_id' => $this->budi->id,
        'score' => null,
    ]);

    $this->actingAs(User::find($this->budi->user_id))
        ->getJson('/api/v1/assessments/student')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

test('a completed remedial raises the final score up to the KKM', function () {
    $lowScore = DailyAssessmentScore::where('student_id', $this->ani->id)->first();
    $lowScore->update(['score' => 60]);
    RemedialSession::create([
        'daily_assessment_id' => $this->assessment->id,
        'student_id' => $this->ani->user_id,
        'assigned_by' => $this->guru->id,
        'original_score' => 60,
        'kkm' => 75,
        'remedial_score' => 90,
        'status' => 'completed',
    ]);

    $response = $this->actingAs(User::find($this->ani->user_id))->getJson('/api/v1/assessments/student');

    expect($response->json('data.0.original_score'))->toEqual(60)
        ->and($response->json('data.0.final_score'))->toEqual(75)
        ->and($response->json('data.0.is_tuntas'))->toBeTrue();
});

test('the teacher list reports entered scores against the class size', function () {
    // A blank row, as the web creates for every student, is not a score.
    DailyAssessmentScore::create([
        'daily_assessment_id' => $this->assessment->id,
        'student_id' => $this->budi->id,
        'score' => null,
    ]);

    $response = $this->actingAs($this->guru)->getJson('/api/v1/assessments/teacher');

    $response->assertOk()
        ->assertJsonPath('data.assessments.0.scored_count', 1)
        ->assertJsonPath('data.assessments.0.classroom.students_count', 2)
        ->assertJsonPath('data.assessments.0.classroom.name', 'XI MIPA 2')
        ->assertJsonPath('data.assessments.0.subject.name', 'Fisika');
});
