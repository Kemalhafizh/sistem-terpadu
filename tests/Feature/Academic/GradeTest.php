<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;

beforeEach(function () {
    $this->guru = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $this->classroom = Classroom::factory()->create();
    $this->student = Student::factory()->create(['classroom_id' => $this->classroom->id]);
    $this->subject = Subject::create([
        'name' => 'Matematika',
        'code' => 'MTK',
        'category' => 'Nasional',
        'kkm' => 75,
    ]);
});

test('guru can submit grades for students', function () {
    $response = $this->actingAs($this->guru)->post(route('grades.store'), [
        'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
        'semester' => 'ganjil',
        'grades' => [
            $this->student->id => [
                'uh' => 85.5,
            ],
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('grades', [
        'student_id' => $this->student->id,
        'subject_id' => $this->subject->id,
        'type' => 'uh',
        'score' => 85.5,
    ]);
});

test('guru cannot submit grade below 0', function () {
    $response = $this->actingAs($this->guru)->post(route('grades.store'), [
        'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
        'grades' => [
            $this->student->id => [
                'uh' => -5, // Negative score
            ],
        ],
    ]);

    $response->assertSessionHasErrors(['grades.'.$this->student->id.'.uh']);
});

test('guru cannot submit grade above 100', function () {
    $response = $this->actingAs($this->guru)->post(route('grades.store'), [
        'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
        'grades' => [
            $this->student->id => [
                'uh' => 105, // Score > 100
            ],
        ],
    ]);

    $response->assertSessionHasErrors(['grades.'.$this->student->id.'.uh']);
});

test('grade model predikat attribute is calculated correctly based on KKM', function () {
    // 1. Grade above 90 -> A
    $gradeA = Grade::create([
        'student_id' => $this->student->id,
        'subject_id' => $this->subject->id,
        'academic_year_id' => $this->academicYear->id,
        'type' => 'uh',
        'score' => 92.0,
        'recorded_by' => $this->guru->id,
    ]);
    expect($gradeA->predikat)->toBe('A (Sangat Baik)');
    expect($gradeA->is_passed)->toBeTrue();

    // 2. Grade below KKM (75) -> D
    $gradeD = Grade::create([
        'student_id' => $this->student->id,
        'subject_id' => $this->subject->id,
        'academic_year_id' => $this->academicYear->id,
        'type' => 'tugas',
        'score' => 70.0,
        'recorded_by' => $this->guru->id,
    ]);
    expect($gradeD->predikat)->toBe('D (Kurang)');
    expect($gradeD->is_passed)->toBeFalse();
});

test('unauthorized role cannot submit grades', function () {
    $studentUser = User::factory()->create(['role' => 'siswa', 'must_change_password' => false]);

    $response = $this->actingAs($studentUser)->post(route('grades.store'), [
        'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
        'grades' => [
            $this->student->id => [
                'uh' => 85.5,
            ],
        ],
    ]);

    $response->assertStatus(403);
});
