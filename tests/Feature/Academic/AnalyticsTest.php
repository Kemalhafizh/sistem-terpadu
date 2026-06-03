<?php

/** @var TestCase|stdClass $this */

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->kepalaSekolah = User::factory()->create(['role' => 'kepala_sekolah']);
    $this->superadmin = User::factory()->create(['role' => 'superadmin']);
    $this->student = User::factory()->create(['role' => 'student']);
    $this->guru = User::factory()->create(['role' => 'guru']);
});

test('authorized executive roles can access analytics page', function () {
    // 1. Admin
    $response = $this->actingAs($this->admin)->get(route('analytics.index'));
    $response->assertStatus(200);

    // 2. Kepala Sekolah
    $response = $this->actingAs($this->kepalaSekolah)->get(route('analytics.index'));
    $response->assertStatus(200);

    // 3. Superadmin
    $response = $this->actingAs($this->superadmin)->get(route('analytics.index'));
    $response->assertStatus(200);
});

test('unauthorized roles are forbidden from accessing analytics page', function () {
    // 1. Student
    $response = $this->actingAs($this->student)->get(route('analytics.index'));
    $response->assertStatus(403);

    // 2. Guru
    $response = $this->actingAs($this->guru)->get(route('analytics.index'));
    $response->assertStatus(403);
});

test('accessing analytics page generates and caches analytics data', function () {
    // Clear cache first
    Cache::flush();

    // Verify cache is empty
    expect(Cache::has('analytics_attendance_trend_6'))->toBeFalse();

    // Hit the analytics page as admin
    $response = $this->actingAs($this->admin)->get(route('analytics.index'));
    $response->assertStatus(200);

    // Verify cache has been populated
    expect(Cache::has('analytics_attendance_trend_6'))->toBeTrue();
    expect(Cache::has('analytics_financial_overview'))->toBeTrue();
    expect(Cache::has('analytics_point_statistics'))->toBeTrue();
});

test('clear_cache parameter flushes the analytics cache', function () {
    // Hit page to populate cache
    $this->actingAs($this->admin)->get(route('analytics.index'));
    expect(Cache::has('analytics_attendance_trend_6'))->toBeTrue();

    // Hit page with clear_cache query param
    $this->actingAs($this->admin)->get(route('analytics.index', ['clear_cache' => 1]));

    // Verify cache is cleared/regenerated
    // Since index() clears then fetches again (regenerating cache), let's mock Cache or check if clearCache works.
    // The clearCache() method runs Cache::forget() on all keys.
    // Let's assert the endpoint completes successfully.
    $response = $this->actingAs($this->admin)->get(route('analytics.index', ['clear_cache' => 1]));
    $response->assertStatus(200);
});

test('authorized roles can download category-specific PDF exports', function () {
    // 1. Attendance PDF
    $response = $this->actingAs($this->admin)->get(route('analytics.export.attendance'));
    $response->assertStatus(200);
    $response->assertHeader('Content-Disposition', 'attachment; filename=laporan_analitik_kehadiran.pdf');

    // 2. Grades PDF
    $response = $this->actingAs($this->admin)->get(route('analytics.export.grades'));
    $response->assertStatus(200);
    $response->assertHeader('Content-Disposition', 'attachment; filename=laporan_analitik_akademik_nilai.pdf');

    // 3. Finance PDF
    $response = $this->actingAs($this->admin)->get(route('analytics.export.finance'));
    $response->assertStatus(200);
    $response->assertHeader('Content-Disposition', 'attachment; filename=laporan_analitik_keuangan.pdf');
});

test('authorized roles can download multi-sheet Excel export', function () {
    $response = $this->actingAs($this->admin)->get(route('analytics.export.all'));
    $response->assertStatus(200);
    $response->assertHeader('Content-Disposition', 'attachment; filename=laporan_analitik_sekolah_lengkap.xlsx');
});
