<?php

use App\Models\Classroom;
use App\Models\ElearningAssignment;
use App\Models\ElearningClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;

beforeEach(function () {
    $this->guru = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);
    $this->student = User::factory()->create(['role' => 'student', 'must_change_password' => false]);
    $this->classroom = Classroom::factory()->create();
    $this->studentProfile = Student::factory()->create([
        'user_id' => $this->student->id,
        'classroom_id' => $this->classroom->id,
    ]);
    $this->subject = Subject::factory()->create();
    $this->elearningClass = ElearningClass::create([
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'teacher_id' => $this->guru->id,
        'name' => 'Fisika Kuantum XII',
        'is_archived' => false,
    ]);
    $this->assignment = ElearningAssignment::create([
        'elearning_class_id' => $this->elearningClass->id,
        'title' => 'Tugas 1 Efek Fotolistrik',
        'description' => 'Kerjakan halaman 40-42',
        'due_at' => now()->addDays(3),
        'allow_late' => true,
    ]);
});

test('student can view elearning classes', function () {
    $response = $this->actingAs($this->student)->getJson('/api/v1/elearning/classes/student');
    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'data']);
});

test('student can view class materials', function () {
    $response = $this->actingAs($this->student)->getJson("/api/v1/elearning/classes/{$this->elearningClass->id}/materials");
    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'data']);
});

test('student can view class assignments', function () {
    $response = $this->actingAs($this->student)->getJson("/api/v1/elearning/classes/{$this->elearningClass->id}/assignments");
    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'data']);
});

test('student can submit assignment', function () {
    $response = $this->actingAs($this->student)->postJson("/api/v1/elearning/assignments/{$this->assignment->id}/submit", [
        'content' => 'Jawaban tugas siswa telah selesai.',
    ]);
    $response->assertStatus(200)
        ->assertJson(['success' => true]);
});
