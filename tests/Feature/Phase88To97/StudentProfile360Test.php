<?php

use App\Models\User;

beforeEach(function () {
    $this->student = User::factory()->create(['role' => 'student', 'must_change_password' => false]);
});

test('student can view comprehensive 360 profile', function () {
    $response = $this->actingAs($this->student)->getJson('/api/v1/profile/student/me');
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'biodata',
                'akademik_summary',
                'ibadah_summary',
                'disiplin_summary',
                'ekskul_list',
                'prestasi_list',
                'kesehatan_summary',
            ],
        ]);
});

test('student can view academic and ibadah breakdown', function () {
    $response = $this->actingAs($this->student)->getJson('/api/v1/profile/student/me/academic');
    $response->assertStatus(200)->assertJson(['success' => true]);

    $resIbadah = $this->actingAs($this->student)->getJson('/api/v1/profile/student/me/ibadah');
    $resIbadah->assertStatus(200)->assertJson(['success' => true]);
});
