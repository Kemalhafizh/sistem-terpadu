<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->academicYear = AcademicYear::factory()->create([
        'is_active' => true,
    ]);

    $this->classroom = Classroom::create([
        'name' => 'Kelas X-A',
        'academic_year' => $this->academicYear->year,
        'grade_level' => 'X',
        'major' => 'Umum',
    ]);

    $this->student = Student::factory()->create([
        'classroom_id' => $this->classroom->id,
    ]);
});

test('admin can access osis members list', function () {
    $response = $this->actingAs($this->admin)->get(route('osis.members'));
    $response->assertStatus(200);
});

test('admin can add student to osis committee', function () {
    $response = $this->actingAs($this->admin)->post(route('osis.members.store'), [
        'student_id' => $this->student->id,
        'position' => 'Ketua OSIS',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('osis_members', [
        'student_id' => $this->student->id,
        'position' => 'Ketua OSIS',
        'academic_year_id' => $this->academicYear->id,
    ]);
});
