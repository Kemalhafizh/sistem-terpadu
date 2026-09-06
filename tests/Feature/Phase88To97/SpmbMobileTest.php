<?php

use App\Models\User;

test('public can view spmb waves and tracks', function () {
    $response = $this->getJson('/api/v1/spmb/waves');
    $response->assertStatus(200)
             ->assertJsonStructure(['success', 'data']);
});

test('public can submit spmb registration', function () {
    $response = $this->postJson('/api/v1/spmb/register', [
        'full_name' => 'Fulan bin Fulan',
        'nisn' => '0098712345',
        'school_origin' => 'SMP Islam Sultan Agung 1',
        'phone_number' => '081234567890',
        'track_name' => 'Jalur Tahfidz',
    ]);

    $response->assertStatus(201)
             ->assertJson(['success' => true]);
});

test('public can track spmb registration status', function () {
    $response = $this->getJson('/api/v1/spmb/status/SPMB-2026-TEST1');
    $response->assertStatus(200)
             ->assertJson(['success' => true]);
});
