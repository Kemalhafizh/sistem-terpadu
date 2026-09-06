<?php

use App\Models\User;

beforeEach(function () {
    $this->student = User::factory()->create(['role' => 'student', 'must_change_password' => false]);
});

test('student can view uks visits', function () {
    $response = $this->actingAs($this->student)->getJson('/api/v1/uks/visits/my');
    $response->assertStatus(200)
             ->assertJsonStructure(['success', 'data']);
});

test('student can view health screening details', function () {
    $response = $this->actingAs($this->student)->getJson('/api/v1/uks/screening');
    $response->assertStatus(200)
             ->assertJsonStructure(['success', 'data']);
});
