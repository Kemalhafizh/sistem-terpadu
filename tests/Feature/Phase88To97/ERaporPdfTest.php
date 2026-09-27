<?php

use App\Models\User;

beforeEach(function () {
    $this->student = User::factory()->create(['role' => 'student', 'must_change_password' => false]);
    $this->parent = User::factory()->create(['role' => 'parent', 'must_change_password' => false]);
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
    $response = $this->actingAs($this->student)->postJson('/api/v1/rapor/generate-pdf', [
        'student_id' => $this->student->id,
        'semester' => 1,
    ]);
    $response->assertStatus(200)
        ->assertJson(['success' => true]);
});
