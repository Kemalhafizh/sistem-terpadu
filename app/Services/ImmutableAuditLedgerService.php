<?php

namespace App\Services;

use App\Models\ImmutableAuditLog;

class ImmutableAuditLedgerService
{
    private const GENESIS_HASH = '0000000000000000000000000000000000000000000000000000000000000000';

    /**
     * Record a new immutable cryptographic audit event log.
     */
    public function record(
        string $eventType,
        ?string $auditableType = null,
        ?int $auditableId = null,
        array $oldValues = [],
        array $newValues = []
    ): ImmutableAuditLog {
        $lastLog = ImmutableAuditLog::query()->latest('id')->first();
        $previousHash = $lastLog ? $lastLog->current_hash : self::GENESIS_HASH;

        $userId = auth()->id();
        $ip = request()->ip();
        $ua = request()->userAgent();

        $hashPayload = implode('|', [
            $previousHash,
            $eventType,
            $auditableType ?? '',
            (string) ($auditableId ?? ''),
            json_encode($oldValues),
            json_encode($newValues),
            (string) $userId,
        ]);

        $currentHash = hash('sha256', $hashPayload);

        return ImmutableAuditLog::create([
            'event_type' => $eventType,
            'user_id' => $userId,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $ip,
            'user_agent' => $ua,
            'previous_hash' => $previousHash,
            'current_hash' => $currentHash,
        ]);
    }

    /**
     * Verify full cryptographic SHA-256 Merkle chain integrity.
     */
    public function verifyChainIntegrity(): array
    {
        $logs = ImmutableAuditLog::query()->orderBy('id', 'asc')->get();

        $expectedPreviousHash = self::GENESIS_HASH;
        $tamperedIds = [];

        foreach ($logs as $log) {
            // Check if previous_hash matches expected
            if ($log->previous_hash !== $expectedPreviousHash) {
                $tamperedIds[] = $log->id;
            }

            // Re-calculate expected current_hash
            $hashPayload = implode('|', [
                $log->previous_hash,
                $log->event_type,
                $log->auditable_type ?? '',
                (string) ($log->auditable_id ?? ''),
                json_encode($log->old_values),
                json_encode($log->new_values),
                (string) $log->user_id,
            ]);

            $calculatedHash = hash('sha256', $hashPayload);

            if ($calculatedHash !== $log->current_hash) {
                if (! in_array($log->id, $tamperedIds)) {
                    $tamperedIds[] = $log->id;
                }
            }

            $expectedPreviousHash = $log->current_hash;
        }

        $isValid = empty($tamperedIds);

        return [
            'is_valid' => $isValid,
            'status' => $isValid ? 'VALID (100% UNTAMPERED)' : 'TAMPERED_DETECTED',
            'total_logs' => count($logs),
            'tampered_count' => count($tamperedIds),
            'tampered_ids' => $tamperedIds,
        ];
    }
}
