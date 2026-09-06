<?php

use App\Models\User;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Subject;
use App\Models\DailyAssessment;
use App\Models\DailyAssessmentScore;

beforeEach(function () {
    $this->guru = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);
    $this->student = User::factory()->create(['role' => 'student', 'must_change_password' => false]);
    $this->classroom = Classroom::factory()->create();
    $this->studentProfile = Student::factory()->create([
        'user_id' => $this->student->id,
        'classroom_id' => $this->classroom->id,
    ]);
    $this->academicYear = AcademicYear::firstOrCreate(
        ['year' => '2025/2026', 'semester' => 'Ganjil'],
        ['is_active' => true]
    );
    $this->subject = Subject::factory()->create();
    $this->assessment = DailyAssessment::create([
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'teacher_id' => $this->guru->id,
        'academic_year_id' => $this->academicYear->id,
        'title' => 'UH 1 Dinamika Rotasi',
        'type' => 'ulangan_harian',
        'assessment_date' => now()->toDateString(),
        'max_score' => 100,
        'kkm' => 75,
    ]);
    DailyAssessmentScore::create([
        'daily_assessment_id' => $this->assessment->id,
        'student_id' => $this->studentProfile->id,
        'score' => 60,
    ]);
});

test('teacher can view assessment list', function () {
    $response = $this->actingAs($this->guru)->getJson('/api/v1/assessments/teacher');
    $response->assertStatus(200)
             ->assertJsonStructure(['success', 'data']);
});

test('teacher can trigger auto remedial calculation', function () {
    $response = $this->actingAs($this->guru)->postJson("/api/v1/assessments/{$this->assessment->id}/auto-remedial", [
        'passing_score' => 75,
    ]);
    $response->assertStatus(200)
             ->assertJson(['success' => true]);
});

test('student can view assigned remedials', function () {
    $response = $this->actingAs($this->student)->getJson('/api/v1/assessments/student/remedials');
    $response->assertStatus(200)
             ->assertJsonStructure(['success', 'data']);
});
