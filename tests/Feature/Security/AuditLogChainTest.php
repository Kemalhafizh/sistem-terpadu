<?php

use App\Models\ImmutableAuditChain;
use App\Models\User;
use App\Services\AuditLogChainService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->auditService = app(AuditLogChainService::class);
});

test('audit log chain service records entries and verifies unbroken cryptographic chain', function () {
    $entry1 = $this->auditService->recordEntry($this->user->id, 'USER_LOGIN', ['method' => 'passkey']);
    $entry2 = $this->auditService->recordEntry($this->user->id, 'GRADE_UPDATED', ['subject' => 'Math', 'score' => 95]);

    expect($entry1->sequence)->toBe(1);
    expect($entry1->previous_hash)->toBe(AuditLogChainService::GENESIS_HASH);
    expect($entry2->sequence)->toBe(2);
    expect($entry2->previous_hash)->toBe($entry1->entry_hash);

    $verification = $this->auditService->verifyChainIntegrity();
    expect($verification['valid'])->toBeTrue();
    expect($verification['total_records'])->toBe(2);
});

test('audit log chain service detects tampered record content', function () {
    $entry1 = $this->auditService->recordEntry($this->user->id, 'PAYMENT_RECEIVED', ['amount' => 500000]);

    // Manually tamper payload in DB
    $record = ImmutableAuditChain::find($entry1->id);
    $record->payload = ['amount' => 10000000]; // Tampered amount
    $record->save();

    $verification = $this->auditService->verifyChainIntegrity();
    expect($verification['valid'])->toBeFalse();
    expect($verification['tampered_sequence'])->toBe(1);
});
