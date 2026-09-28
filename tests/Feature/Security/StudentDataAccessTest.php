<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\DailyAssessment;
use App\Models\ElearningAssignment;
use App\Models\ElearningClass;
use App\Models\ElearningSubmission;
use App\Models\Student;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

/*
 * Mobile endpoints that change or reveal a student's grades used to accept
 * any logged-in account: a student could grade their own e-learning work,
 * enter their own daily-assessment score, or read another child's report
 * card by changing an id. Each case below is the old attack, now refused.
 */

beforeEach(function () {
    $this->classroom = Classroom::factory()->create();
    $this->teacher = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);
    $this->otherTeacher = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);

    $this->studentUser = User::factory()->create(['role' => 'student', 'must_change_password' => false]);
    $this->parent = User::factory()->create(['role' => 'parent', 'must_change_password' => false]);
    $this->studentRecord = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'classroom_id' => $this->classroom->id,
        'parent_id' => $this->parent->id,
    ]);

    $this->otherStudentUser = User::factory()->create(['role' => 'student', 'must_change_password' => false]);
    Student::factory()->create(['user_id' => $this->otherStudentUser->id]);

    $this->class = ElearningClass::factory()->create([
        'teacher_id' => $this->teacher->id,
        'classroom_id' => $this->classroom->id,
    ]);
    $this->assignment = ElearningAssignment::create([
        'elearning_class_id' => $this->class->id,
        'title' => 'Tugas 1',
        'description' => 'Kerjakan soal 1-5.',
        'due_at' => now()->addDay(),
    ]);
    $this->submission = ElearningSubmission::create([
        'elearning_assignment_id' => $this->assignment->id,
        'student_id' => $this->studentRecord->id,
        'content' => 'jawaban',
        'submitted_at' => now(),
        'status' => 'submitted',
    ]);
    $this->assessment = DailyAssessment::factory()->create([
        'teacher_id' => $this->teacher->id,
        'classroom_id' => $this->classroom->id,
        'academic_year_id' => AcademicYear::factory(),
    ]);
});

test('a student cannot grade their own e-learning submission', function () {
    Sanctum::actingAs($this->studentUser);
    $this->postJson("/api/v1/elearning/submissions/{$this->submission->id}/grade", ['score' => 100])->assertForbidden();

    expect($this->submission->fresh()->score)->toBeNull();
});

test('a teacher cannot grade or post into another teacher\'s class', function () {
    Sanctum::actingAs($this->otherTeacher);
    $this->postJson("/api/v1/elearning/submissions/{$this->submission->id}/grade", ['score' => 90])->assertForbidden();
    $this->getJson("/api/v1/elearning/assignments/{$this->assignment->id}/submissions")->assertForbidden();
    $this->postJson("/api/v1/elearning/classes/{$this->class->id}/assignments", ['title' => 'X', 'description' => 'Y', 'due_at' => now()->addDay()->toDateString()])->assertForbidden();
});

test('the owning teacher can grade', function () {
    Sanctum::actingAs($this->teacher);
    $this->postJson("/api/v1/elearning/submissions/{$this->submission->id}/grade", ['score' => 88])->assertOk();

    expect((float) $this->submission->fresh()->score)->toBe(88.0);
});

test('a student cannot enter daily-assessment scores', function () {
    Sanctum::actingAs($this->studentUser);
    $this->postJson("/api/v1/assessments/{$this->assessment->id}/batch-scores", [
        'scores' => [['student_id' => $this->studentUser->id, 'score' => 100]],
    ])->assertForbidden();
});

test('only the owning teacher enters daily-assessment scores', function () {
    Sanctum::actingAs($this->otherTeacher);
    $this->postJson("/api/v1/assessments/{$this->assessment->id}/batch-scores", [
        'scores' => [['student_id' => $this->studentUser->id, 'score' => 80]],
    ])->assertForbidden();

    Sanctum::actingAs($this->teacher);
    $this->postJson("/api/v1/assessments/{$this->assessment->id}/batch-scores", [
        'scores' => [['student_id' => $this->studentRecord->id, 'score' => 80]],
    ])->assertOk();

    // …and only for students of that classroom.
    $outsider = Student::factory()->create();
    $this->postJson("/api/v1/assessments/{$this->assessment->id}/batch-scores", [
        'scores' => [['student_id' => $outsider->id, 'score' => 80]],
    ])->assertStatus(422);
});

test('students only see materials of their own class', function () {
    Sanctum::actingAs($this->studentUser);
    $this->getJson("/api/v1/elearning/classes/{$this->class->id}/materials")->assertOk();

    Sanctum::actingAs($this->otherStudentUser);
    $this->getJson("/api/v1/elearning/classes/{$this->class->id}/materials")->assertForbidden();
});

test('a parent reads only their own child\'s report card', function () {
    Sanctum::actingAs($this->parent);
    $this->getJson("/api/v1/rapor/child/{$this->studentUser->id}")->assertOk();
    $this->getJson("/api/v1/rapor/child/{$this->otherStudentUser->id}")->assertForbidden();
});

test('a student cannot read another child\'s report card or make its PDF', function () {
    Sanctum::actingAs($this->studentUser);
    $this->getJson("/api/v1/rapor/child/{$this->otherStudentUser->id}")->assertForbidden();
    $this->postJson('/api/v1/rapor/generate-pdf', ['student_id' => $this->otherStudentUser->id])->assertForbidden();
});

test('generating a report card without a template says so instead of inventing one', function () {
    Sanctum::actingAs($this->studentUser);
    $this->postJson('/api/v1/rapor/generate-pdf')->assertStatus(422);

    expect(AcademicYear::where('year', '2025/2026')->exists())->toBeFalse();
});

test('students cannot use the question bank', function () {
    Sanctum::actingAs($this->studentUser);
    $this->getJson('/api/v1/question-bank/items')->assertForbidden();
});
