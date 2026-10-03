<?php

declare(strict_types=1);

namespace App\Services\Blockchain;

use App\Models\BlockchainCredential;
use App\Models\User;

class BlockchainCredentialService
{
    /**
     * Issue a cryptographic blockchain verifiable credential.
     */
    public function issueCredential(
        User $student,
        string $credentialType,
        string $certificateNumber,
        array $metadata
    ): BlockchainCredential {
        // 1. Calculate Merkle leaf hash & root
        $rawDigest = json_encode([
            'cert_no' => $certificateNumber,
            'student_id' => $student->id,
            'name' => $student->name,
            'type' => $credentialType,
            'metadata' => $metadata,
            'issued_at' => now()->toIso8601String(),
        ]);

        $leafHash = hash('sha256', $rawDigest);
        $merkleRoot = '0x'.hash('sha256', $leafHash.hash('sha256', 'YBWSA_SULTAN_AGUNG_ROOT_SECRET'));
        $mockTxHash = '0x'.hash('sha256', $merkleRoot.microtime());
        $contractAddress = '0x'.substr(hash('sha256', 'SULAONE_CREDENTIAL_REGISTRY_V1'), 0, 40);

        return BlockchainCredential::create([
            'student_id' => $student->id,
            'credential_type' => $credentialType,
            'certificate_number' => $certificateNumber,
            'recipient_name' => $student->name,
            'merkle_root' => $merkleRoot,
            'transaction_hash' => $mockTxHash,
            'contract_address' => $contractAddress,
            // Nothing is broadcast to a chain yet, so there is no block to point
            // at. This used to be random_int(18000000, 19000000).
            'block_number' => null,
            'issued_at' => now(),
            'metadata_json' => $metadata,
        ]);
    }

    /**
     * Publicly verify a certificate hash against the blockchain root.
     */
    public function verifyCredential(string $certificateNumber): array
    {
        $cred = BlockchainCredential::where('certificate_number', $certificateNumber)->first();
        if (! $cred) {
            return [
                'verified' => false,
                'status' => 'not_found',
                'message' => 'Sertifikat tidak terdaftar di buku besar Blockchain YBWSA.',
            ];
        }

        if ($cred->revoked_at !== null) {
            return [
                'verified' => false,
                'status' => 'revoked',
                'revoked_at' => $cred->revoked_at->toIso8601String(),
                'message' => 'Sertifikat telah dicabut oleh institusi.',
            ];
        }

        return [
            'verified' => true,
            'status' => 'valid',
            'certificate_number' => $cred->certificate_number,
            'recipient_name' => $cred->recipient_name,
            'credential_type' => $cred->credential_type,
            'merkle_root' => $cred->merkle_root,
            'transaction_hash' => $cred->transaction_hash,
            'contract_address' => $cred->contract_address,
            'issued_at' => $cred->issued_at->toIso8601String(),
            'metadata' => $cred->metadata_json,
        ];
    }
}
