<?php

/** @var TestCase|stdClass $this */

use App\Models\User;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->student = User::factory()->create(['role' => 'student']);
});

test('authenticated user can save their dashboard layout', function () {
    $layout = [
        ['id' => 'general_stats', 'name' => 'Statistik Utama', 'visible' => true],
        ['id' => 'quick_access', 'name' => 'Akses Cepat', 'visible' => true],
        ['id' => 'financial_trend', 'name' => 'Tren Keuangan', 'visible' => false],
        ['id' => 'live_feed', 'name' => 'Aktivitas Terbaru', 'visible' => true],
        ['id' => 'spmb_analytics', 'name' => 'Analisis SPMB', 'visible' => false],
    ];

    $response = $this->actingAs($this->admin)->postJson(route('dashboard.save-layout'), [
        'layout' => $layout,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'message' => 'Tata letak dasbor berhasil disimpan!',
        ]);

    $this->admin->refresh();
    expect($this->admin->dashboard_layout)->toBe($layout);
});

test('guest users are blocked from saving dashboard layout', function () {
    $layout = [
        ['id' => 'general_stats', 'name' => 'Statistik Utama', 'visible' => true],
    ];

    $response = $this->postJson(route('dashboard.save-layout'), [
        'layout' => $layout,
    ]);

    $response->assertStatus(401);
});

test('saving dashboard layout validates input correctly', function () {
    // Missing required field 'visible'
    $invalidLayout = [
        ['id' => 'general_stats', 'name' => 'Statistik Utama'],
    ];

    $response = $this->actingAs($this->admin)->postJson(route('dashboard.save-layout'), [
        'layout' => $invalidLayout,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['layout.0.visible']);
});

test('dashboard loads customized layout correctly', function () {
    $layout = [
        ['id' => 'general_stats', 'name' => 'Statistik Utama', 'visible' => true],
        ['id' => 'quick_access', 'name' => 'Akses Cepat', 'visible' => false],
        ['id' => 'financial_trend', 'name' => 'Tren Keuangan', 'visible' => false],
        ['id' => 'live_feed', 'name' => 'Aktivitas Terbaru', 'visible' => false],
        ['id' => 'spmb_analytics', 'name' => 'Analisis SPMB', 'visible' => false],
    ];

    $this->admin->dashboard_layout = $layout;
    $this->admin->save();

    $response = $this->actingAs($this->admin)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertViewHas('dashboard_layout', $layout);
});

test('dashboard falls back to default layout if empty', function () {
    expect($this->admin->dashboard_layout)->toBeNull();

    $response = $this->actingAs($this->admin)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertViewHas('dashboard_layout', $this->admin->getDefaultDashboardLayout());
});
