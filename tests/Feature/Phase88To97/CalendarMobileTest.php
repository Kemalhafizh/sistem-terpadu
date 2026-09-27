<?php

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'student', 'must_change_password' => false]);
});

test('user can fetch academic calendar events', function () {
    $response = $this->actingAs($this->user)->getJson('/api/v1/academic-calendar');
    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'data']);
});

test('user can export ical calendar feed', function () {
    $response = $this->actingAs($this->user)->get('/api/v1/academic-calendar/export-ical');
    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
});
