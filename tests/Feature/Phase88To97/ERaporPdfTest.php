<?php

use App\Models\AcademicYear;
use App\Models\ReportCardTemplate;
use App\Models\Student;
use App\Models\User;

beforeEach(function () {
    $this->student = User::factory()->create(['role' => 'student', 'must_change_password' => false]);
    $this->parent = User::factory()->create(['role' => 'parent', 'must_change_password' => false]);
    // The parent really is this student's parent (rapor/child checks it).
    Student::factory()->create(['user_id' => $this->student->id, 'parent_id' => $this->parent->id]);
});

test('student can view current rapor', function () {
    $response = $this->actingAs($this->student)->getJson('/api/v1/rapor/current');
    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'data']);
});

test('parent can view child rapor', function () {
    $response = $this->actingAs($this->parent)->getJson("/api/v1/rapor/child/{$this->student->id}");
    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'data']);
});

test('system can generate pdf for rapor', function () {
    // The school sets up the template; the endpoint no longer invents one.
    ReportCardTemplate::create([
        'name' => 'Rapor Semester Ganjil',
        'academic_year_id' => AcademicYear::factory()->create()->id,
        'semester' => 'ganjil',
        'phase' => 'F',
        'created_by' => User::factory()->create(['role' => 'admin'])->id,
    ]);

    $response = $this->actingAs($this->student)->postJson('/api/v1/rapor/generate-pdf', [
        'student_id' => $this->student->id,
        'semester' => 1,
    ]);
    $response->assertStatus(200)
        ->assertJson(['success' => true]);
});
