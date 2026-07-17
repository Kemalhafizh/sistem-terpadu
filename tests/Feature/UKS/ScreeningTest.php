<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use App\Models\HealthScreening;
use App\Models\HealthScreeningResult;

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

test('admin can access screenings list page', function () {
    $response = $this->actingAs($this->admin)->get(route('uks.screenings.index'));
    $response->assertStatus(200);
});

test('admin can schedule a new health screening', function () {
    $response = $this->actingAs($this->admin)->post(route('uks.screenings.store'), [
        'title' => 'Pemeriksaan Kesehatan Massal Kelas X',
        'screening_date' => '2026-08-20',
        'academic_year_id' => $this->academicYear->id,
        'target_classrooms' => [$this->classroom->id],
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('health_screenings', [
        'title' => 'Pemeriksaan Kesehatan Massal Kelas X',
        'academic_year_id' => $this->academicYear->id,
    ]);
});

test('admin can input health screening results', function () {
    $screening = HealthScreening::create([
        'title' => 'Screening Mata',
        'screening_date' => '2026-08-20',
        'academic_year_id' => $this->academicYear->id,
        'target_classrooms' => [$this->classroom->id],
        'status' => 'planned',
    ]);

    $response = $this->actingAs($this->admin)->post(route('uks.screenings.results.store'), [
        'screening_id' => $screening->id,
        'results' => [
            [
                'student_id' => $this->student->id,
                'height_cm' => 171.0,
                'weight_kg' => 61.5,
                'vision_left' => 'normal',
                'vision_right' => 'normal',
                'dental_status' => 'sehat',
                'findings' => 'Tidak ada kelainan.',
                'recommendations' => 'Jaga kebersihan gigi.',
            ]
        ]
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('health_screening_results', [
        'screening_id' => $screening->id,
        'student_id' => $this->student->id,
        'height_cm' => 171.0,
    ]);
});
