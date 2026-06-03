<?php

/** @var TestCase|stdClass $this */

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Tests\TestCase;

beforeEach(function () {
    $this->guru = User::factory()->create(['role' => 'guru']);
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $this->classroom = Classroom::factory()->create();
    $this->student = Student::factory()->create(['classroom_id' => $this->classroom->id]);
});

test('guru can submit attendance for students in a class', function () {
    $response = $this->actingAs($this->guru)->post(route('attendances.store'), [
        'classroom_id' => $this->classroom->id,
        'date' => now()->format('Y-m-d'),
        'attendances' => [
            $this->student->id => [
                'status' => 'H',
                'notes' => '',
            ],
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('attendances', [
        'student_id' => $this->student->id,
        'status' => 'H',
        'date' => now()->format('Y-m-d'),
    ]);
});
