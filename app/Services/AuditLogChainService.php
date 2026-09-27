<?php

namespace App\Services;

use App\Models\ImmutableAuditChain;
use Illuminate\Support\Facades\Log;

class AuditLogChainService
{
    public const GENESIS_HASH = '0000000000000000000000000000000000000000000000000000000000000000';

    /**
     * Record a new cryptographic chained audit log entry.
     */
    public function recordEntry(?int $userId, string $action, array $payload, ?string $ip = null): ImmutableAuditChain
    {
        $lastRecord = ImmutableAuditChain::orderBy('sequence', 'desc')->first();

        $sequence = $lastRecord ? $lastRecord->sequence + 1 : 1;
        $previousHash = $lastRecord ? $lastRecord->entry_hash : self::GENESIS_HASH;
        $ipAddress = $ip ?? request()->ip();

        $entryHash = $this->calculateHash($sequence, $previousHash, $userId, $action, $payload);

        return ImmutableAuditChain::create([
            'sequence' => $sequence,
            'user_id' => $userId,
            'action' => $action,
            'payload' => $payload,
            'previous_hash' => $previousHash,
            'entry_hash' => $entryHash,
            'ip_address' => $ipAddress,
        ]);
    }

    /**
     * Verify the cryptographic chain integrity of all audit entries.
     */
    public function verifyChainIntegrity(): array
    {
        $records = ImmutableAuditChain::orderBy('sequence', 'asc')->get();

        if ($records->isEmpty()) {
            return [
                'valid' => true,
                'total_records' => 0,
                'message' => 'Rantai audit kosong.',
            ];
        }

        $expectedPreviousHash = self::GENESIS_HASH;

        foreach ($records as $record) {
            // 1. Check previous hash continuity
            if ($record->previous_hash !== $expectedPreviousHash) {
                Log::error("SECURITY BREACH DETECTED: Chain broken at sequence #{$record->sequence}!");

                return [
                    'valid' => false,
                    'tampered_sequence' => $record->sequence,
                    'reason' => "Mismatch previous hash pada sequence #{$record->sequence}.",
                ];
            }

            // 2. Recalculate entry hash
            $recalculatedHash = $this->calculateHash(
                $record->sequence,
                $record->previous_hash,
                $record->user_id,
                $record->action,
                $record->payload
            );

            if ($record->entry_hash !== $recalculatedHash) {
                Log::error("SECURITY BREACH DETECTED: Content tampered at sequence #{$record->sequence}!");

                return [
                    'valid' => false,
                    'tampered_sequence' => $record->sequence,
                    'reason' => "Payload/Konten telah dimanipulasi pada sequence #{$record->sequence}.",
                ];
            }

            $expectedPreviousHash = $record->entry_hash;
        }

        return [
            'valid' => true,
            'total_records' => count($records),
            'message' => 'Seluruh rantai audit log terverifikasi 100% valid dan utuh.',
        ];
    }

    /**
     * Calculate SHA-256 hash string for an entry.
     */
    public function calculateHash(int $sequence, string $previousHash, ?int $userId, string $action, array $payload): string
    {
        $payloadJson = json_encode($payload);
        $dataToHash = "{$sequence}:{$previousHash}:".($userId ?? 'null').":{$action}:{$payloadJson}";

        return hash('sha256', $dataToHash);
    }
}
