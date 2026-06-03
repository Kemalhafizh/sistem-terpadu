<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\ReportCard;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\ReportCardService;

beforeEach(function () {
    $this->service = new ReportCardService;
    $this->guru = User::factory()->create(['role' => 'guru']);
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $this->classroom = Classroom::factory()->create();
    $this->subject1 = Subject::factory()->create();
    $this->subject2 = Subject::factory()->create();

    // Create 3 students with different score totals
    $this->student1 = Student::factory()->create(['classroom_id' => $this->classroom->id]);
    $this->student2 = Student::factory()->create(['classroom_id' => $this->classroom->id]);
    $this->student3 = Student::factory()->create(['classroom_id' => $this->classroom->id]);
});

test('syncClassroomReportCards calculates total and average scores', function () {
    // Student1: scores 90 + 80 = 170, avg 85
    Grade::create([
        'student_id' => $this->student1->id,
        'subject_id' => $this->subject1->id,
        'academic_year_id' => $this->academicYear->id,
        'type' => 'uh',
        'score' => 90,
        'recorded_by' => $this->guru->id,
    ]);
    Grade::create([
        'student_id' => $this->student1->id,
        'subject_id' => $this->subject2->id,
        'academic_year_id' => $this->academicYear->id,
        'type' => 'uh',
        'score' => 80,
        'recorded_by' => $this->guru->id,
    ]);

    $this->service->syncClassroomReportCards($this->classroom->id, $this->academicYear->id);

    $reportCard = ReportCard::where('student_id', $this->student1->id)->first();

    expect($reportCard)->not->toBeNull();
    expect((float) $reportCard->total_score)->toBe(170.0);
    expect((float) $reportCard->average_score)->toBe(85.0);
});

test('syncClassroomReportCards determines ranking correctly', function () {
    // Student1 total = 170
    Grade::create([
        'student_id' => $this->student1->id,
        'subject_id' => $this->subject1->id,
        'academic_year_id' => $this->academicYear->id,
        'type' => 'uh', 'score' => 90,
        'recorded_by' => $this->guru->id,
    ]);
    Grade::create([
        'student_id' => $this->student1->id,
        'subject_id' => $this->subject2->id,
        'academic_year_id' => $this->academicYear->id,
        'type' => 'uh', 'score' => 80,
        'recorded_by' => $this->guru->id,
    ]);

    // Student2 total = 150
    Grade::create([
        'student_id' => $this->student2->id,
        'subject_id' => $this->subject1->id,
        'academic_year_id' => $this->academicYear->id,
        'type' => 'uh', 'score' => 70,
        'recorded_by' => $this->guru->id,
    ]);
    Grade::create([
        'student_id' => $this->student2->id,
        'subject_id' => $this->subject2->id,
        'academic_year_id' => $this->academicYear->id,
        'type' => 'uh', 'score' => 80,
        'recorded_by' => $this->guru->id,
    ]);

    // Student3 total = 180 (highest)
    Grade::create([
        'student_id' => $this->student3->id,
        'subject_id' => $this->subject1->id,
        'academic_year_id' => $this->academicYear->id,
        'type' => 'uh', 'score' => 95,
        'recorded_by' => $this->guru->id,
    ]);
    Grade::create([
        'student_id' => $this->student3->id,
        'subject_id' => $this->subject2->id,
        'academic_year_id' => $this->academicYear->id,
        'type' => 'uh', 'score' => 85,
        'recorded_by' => $this->guru->id,
    ]);

    $this->service->syncClassroomReportCards($this->classroom->id, $this->academicYear->id);

    $rc1 = ReportCard::where('student_id', $this->student1->id)->first();
    $rc2 = ReportCard::where('student_id', $this->student2->id)->first();
    $rc3 = ReportCard::where('student_id', $this->student3->id)->first();

    // Student3 (180) → rank 1, Student1 (170) → rank 2, Student2 (150) → rank 3
    expect($rc3->rank_in_class)->toBe(1);
    expect($rc1->rank_in_class)->toBe(2);
    expect($rc2->rank_in_class)->toBe(3);
});

test('syncClassroomReportCards handles student with no grades', function () {
    // Student1 has no grades at all

    $this->service->syncClassroomReportCards($this->classroom->id, $this->academicYear->id);

    $reportCard = ReportCard::where('student_id', $this->student1->id)->first();

    expect($reportCard)->not->toBeNull();
    expect((float) $reportCard->total_score)->toBe(0.0);
    expect((float) $reportCard->average_score)->toBe(0.0);
});

test('syncClassroomReportCards uses updateOrCreate for idempotency', function () {
    Grade::create([
        'student_id' => $this->student1->id,
        'subject_id' => $this->subject1->id,
        'academic_year_id' => $this->academicYear->id,
        'type' => 'uh', 'score' => 80,
        'recorded_by' => $this->guru->id,
    ]);

    // Run sync twice
    $this->service->syncClassroomReportCards($this->classroom->id, $this->academicYear->id);
    $this->service->syncClassroomReportCards($this->classroom->id, $this->academicYear->id);

    // Should still only have 1 report card per student, not duplicated
    $count = ReportCard::where('student_id', $this->student1->id)
        ->where('classroom_id', $this->classroom->id)
        ->where('academic_year_id', $this->academicYear->id)
        ->count();

    expect($count)->toBe(1);
});
