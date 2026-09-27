<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\IssuedCertificate;

class CertificateController extends Controller
{
    public function index()
    {
        $certificates = IssuedCertificate::with(['student', 'template'])->latest()->get();

        return view('certificates.templates', compact('certificates'));
    }

    public function verifyPage(string $code)
    {
        $cert = IssuedCertificate::with(['student', 'template'])->where('verification_code', $code)->firstOrFail();

        return view('certificates.verify', compact('cert'));
    }
}
