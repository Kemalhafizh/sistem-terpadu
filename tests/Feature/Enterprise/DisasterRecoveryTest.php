<?php

use App\Models\User;
use App\Services\DisasterRecoveryService;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->drService = app(DisasterRecoveryService::class);
});

test('disaster recovery service creates database snapshot with sha256 checksum', function () {
    $meta = $this->drService->createSnapshot('unit_test');

    expect($meta)->toHaveKeys(['filename', 'label', 'created_at', 'size_bytes', 'checksum_sha256', 'status']);
    expect($meta['label'])->toBe('unit_test');
    expect($meta['checksum_sha256'])->not()->toBeEmpty();

    // Clean up
    $this->drService->deleteSnapshot($meta['filename']);
});

test('disaster recovery service verifies checksum integrity', function () {
    $meta = $this->drService->createSnapshot('verify_test');
    $isValid = $this->drService->verifyChecksum($meta['filename']);

    expect($isValid)->toBeTrue();

    // Clean up
    $this->drService->deleteSnapshot($meta['filename']);
});

test('disaster recovery service runs database integrity audit', function () {
    $audit = $this->drService->runIntegrityAudit();

    expect($audit)->toHaveKeys(['timestamp', 'status', 'checks']);
    expect($audit['checks'])->toHaveKeys(['foreign_keys', 'database_structure', 'storage_health']);
    expect($audit['checks']['foreign_keys']['status'])->toBe('PASSED');
});

test('admin can view disaster recovery dashboard', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.disaster-recovery.index'));
    $response->assertStatus(200);
    $response->assertSee('Disaster Recovery');
    $response->assertSee('Hasil Audit Integritas Database Terakhir');
});

test('admin can trigger snapshot creation via controller', function () {
    $response = $this->actingAs($this->admin)
        ->from(route('admin.disaster-recovery.index'))
        ->post(route('admin.disaster-recovery.create'), [
            'label' => 'controller_test',
        ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('admin.disaster-recovery.index'));
});

test('admin can run manual audit via controller', function () {
    $response = $this->actingAs($this->admin)
        ->from(route('admin.disaster-recovery.index'))
        ->post(route('admin.disaster-recovery.audit'));

    $response->assertStatus(302);
    $response->assertRedirect(route('admin.disaster-recovery.index'));
});
