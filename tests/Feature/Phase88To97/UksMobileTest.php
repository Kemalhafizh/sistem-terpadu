<?php

use App\Models\Student;
use App\Models\User;

beforeEach(function () {
    $this->student = User::factory()->create(['role' => 'student', 'must_change_password' => false]);
    // The endpoints read the logged-in student's own record; a bare student
    // user with no Student row correctly gets 404 (see the last test).
    $this->studentRecord = Student::factory()->create(['user_id' => $this->student->id]);
});

test('student can view uks visits', function () {
    $response = $this->actingAs($this->student)->getJson('/api/v1/uks/visits/my');
    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'data']);
});

test('student can view health screening details', function () {
    $response = $this->actingAs($this->student)->getJson('/api/v1/uks/screening');
    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'data'])
        ->assertJsonPath('data.student_id', $this->studentRecord->id)
             // No screening recorded yet: honest nulls, not invented measurements.
        ->assertJsonPath('data.height_cm', null)
        ->assertJsonPath('data.bmi', null)
        ->assertJsonPath('data.last_screened_at', null);
});

test('student account without a student record gets 404 instead of invented data', function () {
    $orphan = User::factory()->create(['role' => 'student', 'must_change_password' => false]);

    $this->actingAs($orphan)->getJson('/api/v1/uks/visits/my')->assertStatus(404);
    $this->actingAs($orphan)->getJson('/api/v1/uks/screening')->assertStatus(404);
});
