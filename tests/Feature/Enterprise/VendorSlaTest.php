<?php

use App\Models\User;
use App\Services\VendorSlaProbeService;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->probeService = app(VendorSlaProbeService::class);
});

test('vendor sla probe service probes fonnte, midtrans, dapodik, and emis endpoints', function () {
    $metrics = $this->probeService->probeAllVendors();

    expect($metrics)->toHaveKeys(['fonnte', 'midtrans', 'dapodik', 'emis']);
    expect($metrics['fonnte']->vendor_name)->toBe('Fonnte WhatsApp Gateway');
    expect($metrics['midtrans']->vendor_name)->toBe('Midtrans Payment Gateway');
    expect($metrics['dapodik']->vendor_name)->toBe('Kemendikbud Dapodik Portal');
    expect($metrics['emis']->vendor_name)->toBe('Kemenag EMIS Portal');
});

test('vendor sla probe service computes scorecard summary with uptime and p95 latency', function () {
    $this->probeService->probeAllVendors();
    $summary = $this->probeService->getVendorSlaSummary();

    expect($summary)->toHaveKeys(['fonnte', 'midtrans', 'dapodik', 'emis']);
    expect($summary['fonnte'])->toHaveKeys(['uptime_percentage', 'p95_latency_ms', 'error_rate_percentage', 'is_sla_compliant']);
    expect($summary['fonnte']['uptime_percentage'])->toBeGreaterThanOrEqual(0.0);
});

test('admin can view vendor sla scorecard dashboard', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.vendor-sla.index'));
    $response->assertStatus(200);
    $response->assertSee('Enterprise Vendor SLA');
    $response->assertSee('Fonnte WhatsApp Gateway');
});

test('admin can trigger vendor sla probe test via controller', function () {
    $response = $this->actingAs($this->admin)
        ->from(route('admin.vendor-sla.index'))
        ->post(route('admin.vendor-sla.probe'));

    $response->assertStatus(302);
    $response->assertRedirect(route('admin.vendor-sla.index'));
});

test('artisan command vendor:probe-sla executes successfully', function () {
    $this->artisan('vendor:probe-sla')
        ->assertExitCode(0);
});
