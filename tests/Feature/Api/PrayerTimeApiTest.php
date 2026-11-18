<?php

use App\Models\Setting;
use App\Models\User;
use App\Support\Access\MobileCapabilities;
use Laravel\Sanctum\Sanctum;

test('prayer times endpoint returns null data when school coordinates are not configured', function () {
    Setting::whereIn('key', ['school_latitude', 'school_longitude'])->delete();

    $student = User::factory()->create(['role' => 'student', 'must_change_password' => false]);
    Sanctum::actingAs($student);

    $response = $this->getJson('/api/v1/prayer-times');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data', null);
});

test('prayer times endpoint calculates Kemenag RI schedule for SMA Islam Sultan Agung 1 Semarang coordinates', function () {
    Setting::set('school_latitude', '-6.9667', 'general', 'text', 'Lintang Sekolah');
    Setting::set('school_longitude', '110.4167', 'general', 'text', 'Bujur Sekolah');
    Setting::set('school_timezone', 'Asia/Jakarta', 'general', 'text', 'Zona Waktu Sekolah');
    Setting::set('school_city', 'Kota Semarang', 'general', 'text', 'Kota Sekolah');

    $teacher = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);
    Sanctum::actingAs($teacher);

    $response = $this->getJson('/api/v1/prayer-times?date=2026-03-21');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.date', '2026-03-21')
        ->assertJsonPath('data.location', 'Kota Semarang')
        ->assertJsonPath('data.timezone', 'Asia/Jakarta')
        ->assertJsonPath('data.method', 'Kemenag RI')
        ->assertJsonStructure([
            'data' => [
                'date',
                'location',
                'timezone',
                'method',
                'coordinates' => ['latitude', 'longitude'],
                'times' => ['subuh', 'terbit', 'dzuhur', 'ashar', 'maghrib', 'isya'],
                'prayers',
            ],
        ]);

    $times = $response->json('data.times');
    expect($times['subuh'])->toMatch('/^04:[34]\d$/')
        ->and($times['dzuhur'])->toMatch('/^11:[45]\d$/')
        ->and($times['ashar'])->toMatch('/^1[45]:\d{2}$/')
        ->and($times['maghrib'])->toMatch('/^17:[45]\d$/')
        ->and($times['isya'])->toMatch('/^1[89]:\d{2}$/');
});

test('prayer_times capability is available to all signed-in mobile roles', function () {
    foreach (['student', 'parent', 'guru', 'kepala_sekolah', 'admin'] as $role) {
        $user = User::factory()->create(['role' => $role, 'must_change_password' => false]);
        $keys = array_column(MobileCapabilities::for($user), 'key');
        expect($keys)->toContain('prayer_times');
    }
});
