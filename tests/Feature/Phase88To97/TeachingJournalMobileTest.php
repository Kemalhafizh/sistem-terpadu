<?php

use App\Models\User;

beforeEach(function () {
    $this->guru = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);
});

test('teacher can view journals list', function () {
    $response = $this->actingAs($this->guru)->getJson('/api/v1/teaching-journals/my');
    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'data']);
});

test('teacher can store teaching journal', function () {
    $response = $this->actingAs($this->guru)->postJson('/api/v1/teaching-journals', [
        'class_name' => 'XII MIPA 1',
        'subject_name' => 'Fisika',
        'topic' => 'Kajian Relativitas Khusus',
        'date' => '2026-09-06',
        'attendance_present' => 35,
        'attendance_absent' => 1,
    ]);
    $response->assertStatus(201)
        ->assertJson(['success' => true]);
});
