<?php

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use App\Models\User;
use App\Services\Communication\DigitalSignatureService;
use Illuminate\Database\Seeder;

class DigitalSignatureSeeder extends Seeder
{
    public function run(): void
    {
        $signatureService = app(DigitalSignatureService::class);
        $users = User::take(3)->get();

        if ($users->isEmpty()) {
            return;
        }

        $signer = $users->first();

        // Seed Templates
        DocumentTemplate::updateOrCreate(
            ['name' => 'Rapor Akademik Kurikulum Merdeka'],
            [
                'category' => 'academic',
                'blade_template_path' => 'reports.academic_merdeka',
                'required_signers' => ['WaliKelas', 'KepalaSekolah'],
                'is_active' => true,
            ]
        );

        DocumentTemplate::updateOrCreate(
            ['name' => 'Surat Keputusan (SK) Pengangkatan Guru'],
            [
                'category' => 'hr',
                'blade_template_path' => 'letters.sk_teacher',
                'required_signers' => ['KetuaYayasan', 'KepalaSekolah'],
                'is_active' => true,
            ]
        );

        // Seed Pending Signature Request
        $pending = $signatureService->requestSignature(
            'Rapor Akademik',
            101,
            $signer->id,
            'Kepala Sekolah'
        );

        // Seed Executed Signed Document
        $signedRequest = $signatureService->requestSignature(
            'Surat Keputusan (SK)',
            202,
            $signer->id,
            'Kepala Sekolah'
        );

        $signatureService->sign(
            $signedRequest->id,
            $signer->id,
            '127.0.0.1',
            'signatures/demo_stamp.png'
        );
    }
}
