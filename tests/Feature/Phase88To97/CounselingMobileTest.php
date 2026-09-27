<?php

use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;

beforeEach(function () {
    $this->counselor = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);
    $this->student = User::factory()->create(['role' => 'student', 'must_change_password' => false]);
    $this->classroom = Classroom::factory()->create();
    $this->studentProfile = Student::factory()->create([
        'user_id' => $this->student->id,
        'classroom_id' => $this->classroom->id,
    ]);
});

test('counselor can view dashboard', function () {
    $response = $this->actingAs($this->counselor)->getJson('/api/v1/counseling/dashboard');
    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'data']);
});

test('student can request counseling appointment', function () {
    $response = $this->actingAs($this->student)->postJson('/api/v1/counseling/appointments/request', [
        'topic' => 'Konsultasi pemilihan jurusan kuliah',
        'category' => 'karir',
    ]);
    $response->assertStatus(201)
        ->assertJson(['success' => true]);
});

test('counselor can view at risk students', function () {
    $response = $this->actingAs($this->counselor)->getJson('/api/v1/counseling/at-risk');
    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'data']);
});
