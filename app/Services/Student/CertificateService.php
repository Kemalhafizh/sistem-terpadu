<?php

namespace App\Services\Student;

use App\Models\IssuedCertificate;
use Illuminate\Support\Str;

class CertificateService
{
    public function issueCertificate(int $studentId, int $templateId, array $metadata = []): IssuedCertificate
    {
        return IssuedCertificate::create([
            'student_id' => $studentId,
            'template_id' => $templateId,
            'verification_code' => 'CERT-'.strtoupper(Str::random(10)),
            'metadata' => $metadata,
            'issued_at' => now(),
        ]);
    }
}
