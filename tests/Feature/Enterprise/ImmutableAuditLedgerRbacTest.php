<?php

use App\Models\ImmutableAuditLog;
use App\Models\User;
use App\Services\ImmutableAuditLedgerService;
use App\Services\PermissionService;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->ledgerService = new ImmutableAuditLedgerService;
    $this->permissionService = new PermissionService;
});

test('immutable audit ledger records cryptographic sha256 chained log entries', function () {
    $log1 = $this->ledgerService->record('GRADE_UPDATED', 'App\Models\Grade', 1, ['score' => 60], ['score' => 85]);
    $log2 = $this->ledgerService->record('SALARY_UPDATED', 'App\Models\Payroll', 2, ['basic' => 3000000], ['basic' => 4000000]);

    expect($log1->current_hash)->toHaveLength(64);
    expect($log2->previous_hash)->toBe($log1->current_hash);
});

test('verify chain integrity returns valid for untampered log chain', function () {
    $this->ledgerService->record('TEST_EVENT_1', 'App\Models\User', 1, [], ['name' => 'Alice']);
    $this->ledgerService->record('TEST_EVENT_2', 'App\Models\User', 1, ['name' => 'Alice'], ['name' => 'Bob']);

    $integrity = $this->ledgerService->verifyChainIntegrity();

    expect($integrity['is_valid'])->toBeTrue();
    expect($integrity['tampered_count'])->toBe(0);
});

test('verify chain integrity detects tampered log record', function () {
    $log = $this->ledgerService->record('SENSITIVE_MUTATION', 'App\Models\Grade', 99, ['score' => 50], ['score' => 95]);

    // Directly alter database record to simulate unauthorized tampering
    ImmutableAuditLog::where('id', $log->id)->update([
        'new_values' => json_encode(['score' => 100]), // Tampered!
    ]);

    $integrity = $this->ledgerService->verifyChainIntegrity();

    expect($integrity['is_valid'])->toBeFalse();
    expect($integrity['tampered_count'])->toBeGreaterThan(0);
});

test('permission service validates enterprise role and permission matrix', function () {
    expect($this->permissionService->hasPermission('superadmin', 'any.random.permission'))->toBeTrue();
    expect($this->permissionService->hasPermission('admin', 'observability.apm.view'))->toBeTrue();
    expect($this->permissionService->hasPermission('admin', 'academic.grades.edit'))->toBeFalse();
    expect($this->permissionService->hasPermission('student', 'finance.ledger.view'))->toBeFalse();
});

test('admin can view rbac audit ledger dashboard', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.security.rbac.index'));
    $response->assertStatus(200);
    $response->assertSee('Immutable Cryptographic Audit Ledger');
});

test('admin can trigger ledger integrity re-verification via controller', function () {
    $response = $this->actingAs($this->admin)
        ->from(route('admin.security.rbac.index'))
        ->post(route('admin.security.rbac.verify-ledger'));

    $response->assertStatus(302);
    $response->assertRedirect(route('admin.security.rbac.index'));
});
