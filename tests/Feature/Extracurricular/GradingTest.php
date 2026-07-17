<?php

use App\Models\Extracurricular;
use App\Models\ExtracurricularMember;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->teacher = User::factory()->create([
        'role' => 'guru',
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

    $this->club = Extracurricular::create([
        'name' => 'English Club',
        'advisor_id' => $this->teacher->id,
        'schedule_day' => 'Selasa',
        'schedule_time' => '14:30',
        'location' => 'Kelas X-A',
        'max_members' => 30,
        'is_active' => true,
    ]);

    $this->member = ExtracurricularMember::create([
        'extracurricular_id' => $this->club->id,
        'student_id' => $this->student->id,
        'role' => 'anggota',
        'status' => 'active',
        'joined_at' => now(),
    ]);
});

test('admin can access grading page for extracurricular club', function () {
    $response = $this->actingAs($this->admin)->get(route('ekskul.grading', $this->club));
    $response->assertStatus(200);
});

test('admin can input extracurricular grades for members', function () {
    $response = $this->actingAs($this->admin)->post(route('ekskul.grading.store', $this->club), [
        'grades' => [
            [
                'member_id' => $this->member->id,
                'grade' => 'A',
                'description' => 'Sangat aktif berpartisipasi dalam debat bahasa Inggris.',
            ]
        ]
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('extracurricular_grades', [
        'extracurricular_member_id' => $this->member->id,
        'academic_year_id' => $this->academicYear->id,
        'grade' => 'A',
    ]);
});
