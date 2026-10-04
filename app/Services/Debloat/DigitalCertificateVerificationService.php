<?php

namespace App\Services\Debloat;

use App\Models\DigitalVerification;
use App\Models\Student;
use App\Support\School;
use DomainException;

class DigitalCertificateVerificationService
{
    /**
     * Issue a pragmatic verifiable digital certificate/diploma.
     */
    public function issueCertificate(
        Student $student,
        string $documentType,
        string $documentNumber,
        ?string $issuerTitle = null,
        ?string $issuerName = null
    ): DigitalVerification {
        // The signer is the principal from the school settings. There is no
        // stand-in: the default used to be a made-up "Drs. H. Sukarno, M.Pd".
        $issuerName ??= School::principalName();
        if (blank($issuerName)) {
            throw new DomainException('Nama kepala sekolah belum diisi di Pengaturan Sekolah, sehingga sertifikat belum bisa ditandatangani.');
        }
        $issuerTitle ??= 'Kepala '.school_name();

        $hashPayload = sprintf(
            '%s|%s|%s|%s|%s|%s',
            $documentNumber,
            $documentType,
            $student->nisn,
            $student->user->name ?? 'Siswa',
            $issuerName,
            now()->toDateString()
        );

        $sha256Hash = hash('sha256', $hashPayload);
        $verificationUrl = "https://sista.sultanagung1.sch.id/verify/{$documentNumber}";

        return DigitalVerification::updateOrCreate(
            ['document_number' => $documentNumber],
            [
                'document_type' => strtoupper($documentType),
                'student_nisn' => $student->nisn,
                'student_name' => $student->user->name ?? 'Siswa',
                'issuer_title' => $issuerTitle,
                'issuer_name' => $issuerName,
                'sha256_hash' => $sha256Hash,
                'qr_verification_url' => $verificationUrl,
                'issued_date' => now()->toDateString(),
                'is_valid' => true,
                'metadata' => [
                    'institution' => school_name(),
                    'curriculum' => 'Kurikulum Merdeka (Fase E/F)',
                    'signature_type' => 'VISUAL_DIGITAL_STAMP_SHA256',
                ],
            ]
        );
    }

    /**
     * Verify document validity by document number or hash.
     */
    public function verifyCertificate(string $documentNumber): ?array
    {
        $doc = DigitalVerification::where('document_number', $documentNumber)
            ->orWhere('sha256_hash', $documentNumber)
            ->first();

        if (! $doc) {
            return [
                'is_valid' => false,
                'message' => 'Dokumen tidak ditemukan dalam arsip resmi '.school_name().'.',
            ];
        }

        return [
            'is_valid' => $doc->is_valid,
            'document_number' => $doc->document_number,
            'document_type' => $doc->document_type,
            'student_name' => $doc->student_name,
            'student_nisn' => $doc->student_nisn,
            'issuer_name' => $doc->issuer_name,
            'issuer_title' => $doc->issuer_title,
            'issued_date' => $doc->issued_date->format('d F Y'),
            'sha256_hash' => $doc->sha256_hash,
            'qr_verification_url' => $doc->qr_verification_url,
            'verified_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Invalidate/Revoke certificate.
     */
    public function revokeCertificate(string $documentNumber, string $reason): bool
    {
        $doc = DigitalVerification::where('document_number', $documentNumber)->first();
        if ($doc) {
            $metadata = $doc->metadata ?? [];
            $metadata['revocation_reason'] = $reason;
            $metadata['revoked_at'] = now()->toIso8601String();

            $doc->update([
                'is_valid' => false,
                'metadata' => $metadata,
            ]);

            return true;
        }

        return false;
    }
}
