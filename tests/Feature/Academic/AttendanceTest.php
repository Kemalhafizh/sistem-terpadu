<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use App\Notifications\StudentAttendanceNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->guru = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);
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

test('guru cannot submit attendance with invalid classroom id', function () {
    $response = $this->actingAs($this->guru)->post(route('attendances.store'), [
        'classroom_id' => 999999, // Invalid
        'date' => now()->format('Y-m-d'),
        'attendances' => [
            $this->student->id => [
                'status' => 'H',
                'notes' => '',
            ],
        ],
    ]);

    $response->assertSessionHasErrors(['classroom_id']);
});

test('guru cannot submit attendance with future date', function () {
    $response = $this->actingAs($this->guru)->post(route('attendances.store'), [
        'classroom_id' => $this->classroom->id,
        'date' => now()->addDay()->format('Y-m-d'), // Future date
        'attendances' => [
            $this->student->id => [
                'status' => 'H',
                'notes' => '',
            ],
        ],
    ]);

    $response->assertSessionHasErrors(['date']);
});

test('submitting attendance as Alpa logs BK penalty and sends notification to parent', function () {
    Notification::fake();

    // Create a mock parent user and associate directly with student parent_id
    $parentUser = User::factory()->create(['role' => 'orang_tua', 'must_change_password' => false]);
    $this->student->update(['parent_id' => $parentUser->id]);

    $response = $this->actingAs($this->guru)->post(route('attendances.store'), [
        'classroom_id' => $this->classroom->id,
        'date' => now()->format('Y-m-d'),
        'attendances' => [
            $this->student->id => [
                'status' => 'A', // Alpa
                'notes' => 'Tanpa keterangan',
            ],
        ],
    ]);

    $response->assertRedirect();

    // 1. Assert attendance is recorded
    $this->assertDatabaseHas('attendances', [
        'student_id' => $this->student->id,
        'status' => 'A',
        'date' => now()->format('Y-m-d'),
    ]);

    // 2. Assert BK penalty point is logged
    $this->assertDatabaseHas('student_point_logs', [
        'student_id' => $this->student->id,
        'points' => 10,
        'type' => 'penalty',
    ]);

    // 3. Assert notification is sent to parent
    Notification::assertSentTo(
        $parentUser,
        StudentAttendanceNotification::class
    );
});
