<?php

namespace App\Services\Communication;

use App\Models\DocumentSignature;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DigitalSignatureService
{
    /**
     * Request digital signature for an official institution document.
     */
    public function requestSignature(
        string $documentType,
        int $documentId,
        int $signerId,
        string $signerRole = 'Kepala Sekolah'
    ): DocumentSignature {
        $verificationCode = $this->generateVerificationCode();

        return DocumentSignature::create([
            'document_type' => $documentType,
            'document_id' => $documentId,
            'signer_id' => $signerId,
            'signer_role' => $signerRole,
            'qr_verification_code' => $verificationCode,
            'signed_at' => null,
        ]);
    }

    /**
     * Perform digital signature (E-Stamp) with SHA-256 cryptographic hash and IP audit log.
     */
    public function sign(
        int $signatureId,
        int $userId,
        string $ipAddress,
        ?string $signatureImagePath = null
    ): DocumentSignature {
        $signature = DocumentSignature::where('id', $signatureId)
            ->where('signer_id', $userId)
            ->firstOrFail();

        $signedAt = now();
        $rawContent = "SISTA-DOC:{$signature->document_type}:{$signature->document_id}:SIGNER:{$userId}:TIME:{$signedAt->timestamp}:CODE:{$signature->qr_verification_code}";
        $cryptoHash = hash('sha256', $rawContent);

        $signature->signature_hash = $cryptoHash;
        $signature->signature_image_path = $signatureImagePath ?? "signatures/stamp_signed_{$signature->id}.png";
        $signature->signed_at = $signedAt;
        $signature->ip_address = $ipAddress;
        $signature->save();

        return $signature;
    }

    /**
     * Verify document authenticity via QR Verification Code (Public / No Auth).
     */
    public function verify(string $qrVerificationCode): ?array
    {
        $signature = DocumentSignature::with('signer')
            ->where('qr_verification_code', $qrVerificationCode)
            ->first();

        if (! $signature || ! $signature->isSigned()) {
            return [
                'is_valid' => false,
                'status' => 'INVALID_OR_NOT_SIGNED',
                'message' => 'Dokumen tidak ditemukan atau belum ditandatangani secara resmi.',
            ];
        }

        return [
            'is_valid' => true,
            'status' => 'VALID_AUTHENTIC_DOCUMENT',
            'document_type' => $signature->document_type,
            'document_id' => $signature->document_id,
            'signer_name' => optional($signature->signer)->name ?? 'Pejabat Berwenang',
            'signer_role' => $signature->signer_role,
            'signed_at' => $signature->signed_at->format('d M Y H:i:s T'),
            'signature_hash' => $signature->signature_hash,
            'institution' => 'SMA Islam Sultan Agung 1 Semarang',
            'ip_address' => $signature->ip_address,
            'qr_code' => $signature->qr_verification_code,
        ];
    }

    /**
     * Get list of pending signatures assigned to a user.
     */
    public function getPendingSignatures(int $userId): Collection
    {
        return DocumentSignature::where('signer_id', $userId)
            ->whereNull('signed_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get signature history log for a document or user.
     */
    public function getSignatureHistory(?string $documentType = null, ?int $documentId = null): Collection
    {
        $query = DocumentSignature::with('signer')->whereNotNull('signed_at');

        if ($documentType && $documentId) {
            $query->where('document_type', $documentType)->where('document_id', $documentId);
        }

        return $query->orderBy('signed_at', 'desc')->take(50)->get();
    }

    /**
     * Generate unique 20-character verification QR Code string.
     */
    public function generateVerificationCode(): string
    {
        do {
            $code = 'SISTA-DOC-' . strtoupper(Str::random(12));
        } while (DocumentSignature::where('qr_verification_code', $code)->exists());

        return $code;
    }
}
