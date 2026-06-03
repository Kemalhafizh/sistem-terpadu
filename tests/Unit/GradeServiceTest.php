<?php

/** @var TestCase|stdClass $this */

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\GradeService;
use Tests\TestCase;

beforeEach(function () {
    $this->service = new GradeService;
    $this->guru = User::factory()->create(['role' => 'guru']);
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $this->classroom = Classroom::factory()->create();
    $this->subject = Subject::factory()->create();
    $this->student1 = Student::factory()->create(['classroom_id' => $this->classroom->id]);
    $this->student2 = Student::factory()->create(['classroom_id' => $this->classroom->id]);
});

test('recordBulkGrades saves grades for multiple students', function () {
    $gradesData = [
        $this->student1->id => ['tugas' => 85, 'uh' => 90],
        $this->student2->id => ['tugas' => 75, 'uh' => 80],
    ];

    $count = $this->service->recordBulkGrades($gradesData, $this->subject->id, $this->guru->id);

    expect($count)->toBe(4); // 2 students × 2 types

    $this->assertDatabaseHas('grades', [
        'student_id' => $this->student1->id,
        'type' => 'tugas',
        'score' => 85,
    ]);

    $this->assertDatabaseHas('grades', [
        'student_id' => $this->student2->id,
        'type' => 'uh',
        'score' => 80,
    ]);
});

test('recordBulkGrades skips empty values', function () {
    $gradesData = [
        $this->student1->id => ['tugas' => 85, 'uh' => ''], // uh is empty
    ];

    $count = $this->service->recordBulkGrades($gradesData, $this->subject->id, $this->guru->id);

    expect($count)->toBe(1); // Only tugas saved

    $this->assertDatabaseHas('grades', [
        'student_id' => $this->student1->id,
        'type' => 'tugas',
    ]);

    $this->assertDatabaseMissing('grades', [
        'student_id' => $this->student1->id,
        'type' => 'uh',
    ]);
});

test('recordBulkGrades uses updateOrCreate to avoid duplicates', function () {
    // First insertion
    $this->service->recordBulkGrades(
        [$this->student1->id => ['tugas' => 85]],
        $this->subject->id,
        $this->guru->id
    );

    // Second insertion with different score — should UPDATE, not create new
    $this->service->recordBulkGrades(
        [$this->student1->id => ['tugas' => 95]],
        $this->subject->id,
        $this->guru->id
    );

    // Should only have ONE record
    $count = Grade::where('student_id', $this->student1->id)
        ->where('type', 'tugas')
        ->count();

    expect($count)->toBe(1);

    // Score should be the latest value
    $grade = Grade::where('student_id', $this->student1->id)
        ->where('type', 'tugas')
        ->first();

    expect($grade->score)->toEqual(95);
});

test('recordBulkGrades throws exception when no active academic year', function () {
    // Deactivate all academic years
    $this->academicYear->update(['is_active' => false]);

    expect(function () {
        $this->service->recordBulkGrades(
            [$this->student1->id => ['tugas' => 85]],
            $this->subject->id,
            $this->guru->id
        );
    })->toThrow(Exception::class, 'Tahun ajaran aktif tidak ditemukan.');
});
