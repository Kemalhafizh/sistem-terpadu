<?php

/** @var TestCase|stdClass $this */

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Tests\TestCase;

beforeEach(function () {
    $this->guru = User::factory()->create(['role' => 'guru']);
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $this->classroom = Classroom::factory()->create();
    $this->student = Student::factory()->create(['classroom_id' => $this->classroom->id]);
    $this->subject = Subject::factory()->create();
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
