<?php

use App\Domains\Academic\Services\GradeCalculationDomainService;
use App\Domains\Finance\Services\WadiahDomainService;
use App\Domains\HumanResources\Services\PayrollDomainService;
use App\Domains\Keislaman\Services\TahfidzDomainService;
use App\Domains\School\Services\SchoolBrandingDomainService;
use App\Models\User;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
});

test('academic domain service calculates final grade correctly', function () {
    $service = new GradeCalculationDomainService();
    $result = $service->calculateFinalGrade(90, 85, 88, 90);

    expect($result['final_score'])->toBe(87.7);
    expect($result['predicate'])->toBe('B (Baik)');
    expect($result['is_passed'])->toBeTrue();
});

test('keislaman domain service evaluates tahfidz progress correctly', function () {
    $service = new TahfidzDomainService();
    $result = $service->evaluateProgress(600, 3);

    expect($result['percentage'])->toBe(100.0);
    expect($result['predicate'])->toBe('Mumtaz (Lulus Target)');
});

test('finance domain service validates canteen purchase correctly', function () {
    $service = new WadiahDomainService();

    // Valid purchase
    $valid = $service->validateCanteenPurchase(50000, 10000, 25000, 10000);
    expect($valid['allowed'])->toBeTrue();

    // Over limit
    $overLimit = $service->validateCanteenPurchase(50000, 20000, 25000, 10000);
    expect($overLimit['allowed'])->toBeFalse();
    expect($overLimit['reason'])->toContain('melebihi limit jajan harian');
});

test('human resources domain service calculates net payroll correctly', function () {
    $service = new PayrollDomainService();
    $result = $service->calculateSalary(4000000, 1000000, 2);

    expect($result['gross_salary'])->toBe(5000000.0);
    expect($result['total_deductions'])->toBe(100000.0);
    expect($result['net_salary'])->toBe(4900000.0);
});

test('school domain service retrieves single school branding profile', function () {
    $service = new SchoolBrandingDomainService();
    $profile = $service->getSchoolProfile();

    expect($profile['school_name'])->toContain('Sultan Agung 1');
    expect($profile['foundation_name'])->toContain('YBWSA');

    $header = $service->getFormalHeaderData();
    expect($header['line_1'])->toContain('YBWSA');
});

test('admin can view and update school branding profile via controller', function () {
    $responseGet = $this->actingAs($this->admin)->get(route('admin.school.branding.index'));
    $responseGet->assertStatus(200);
    $responseGet->assertSee('Single-School Enterprise Core');

    $responsePost = $this->actingAs($this->admin)->post(route('admin.school.branding.update'), [
        'school_name' => 'SMA Islam Sultan Agung 1 Semarang Enterprise',
        'foundation_name' => 'Yayasan Badan Wakaf Sultan Agung (YBWSA)',
        'school_npsn' => '20328888',
        'school_akreditasi' => 'A (Unggul)',
        'school_address' => 'Jl. Mataram No. 657 Semarang, Jawa Tengah 50242',
        'school_phone' => '(024) 8314941',
        'school_email' => 'info@smaislam1-smg.sch.id',
        'principal_name' => 'Drs. H. Ahmad Dahlan, M.Pd.',
        'principal_nip' => '196807151994031005',
        'active_academic_year' => '2026/2027 Semester Ganjil',
    ]);

    $responsePost->assertStatus(302);
    $responsePost->assertRedirect(route('admin.school.branding.index'));

    $service = new SchoolBrandingDomainService();
    $profile = $service->getSchoolProfile();
    expect($profile['school_name'])->toBe('SMA Islam Sultan Agung 1 Semarang Enterprise');
});
