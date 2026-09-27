<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentHealthRecord;
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

    $this->healthRecord = StudentHealthRecord::create([
        'student_id' => $this->student->id,
        'blood_type' => 'O',
        'allergies' => ['Kacang'],
        'chronic_conditions' => [],
        'medications' => [],
        'emergency_contact_name' => 'Budi',
        'emergency_contact_phone' => '081299887766',
        'height_cm' => 170.0,
        'weight_kg' => 60.0,
        'vision_status' => 'normal',
    ]);
});

test('admin can access health records list page', function () {
    $response = $this->actingAs($this->admin)->get(route('uks.health-records.index'));
    $response->assertStatus(200);
});

test('admin can view student health record details', function () {
    $response = $this->actingAs($this->admin)->get(route('uks.health-records.show', $this->student->id));
    $response->assertStatus(200);
});

test('admin can update student health record details', function () {
    $response = $this->actingAs($this->admin)->post(route('uks.health-records.update'), [
        'student_id' => $this->student->id,
        'blood_type' => 'AB',
        'allergies' => 'Kacang, Seafood',
        'chronic_conditions' => 'Asma',
        'medications' => 'Inhaler',
        'medical_notes' => 'Alergi kacang parah.',
        'emergency_contact_name' => 'Budi Hermanto',
        'emergency_contact_phone' => '081299887766',
        'insurance_number' => 'BPJS-12345',
        'height_cm' => 172.5,
        'weight_kg' => 62.0,
        'vision_status' => 'minus',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('student_health_records', [
        'student_id' => $this->student->id,
        'blood_type' => 'AB',
        'insurance_number' => 'BPJS-12345',
        'vision_status' => 'minus',
    ]);
});
