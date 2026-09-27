<?php

namespace Database\Seeders;

use App\Models\AccreditationIndicator;
use App\Models\AccreditationStandard;
use App\Models\User;
use App\Services\Grc\AccreditationService;
use Illuminate\Database\Seeder;

class AccreditationSeeder extends Seeder
{
    public function run(): void
    {
        $accreditationService = app(AccreditationService::class);
        $user = User::first();

        if (! $user) {
            return;
        }

        $snpList = [
            ['code' => 'SNP-1', 'name' => 'Standar Isi (Kurikulum & Silabus)', 'cat' => 'isi'],
            ['code' => 'SNP-2', 'name' => 'Standar Proses (Pembelajaran & RPP)', 'cat' => 'proses'],
            ['code' => 'SNP-3', 'name' => 'Standar Kompetensi Lulusan (SKL)', 'cat' => 'lulusan'],
            ['code' => 'SNP-4', 'name' => 'Standar Pendidik & Tenaga Kependidikan (PTK)', 'cat' => 'ptk'],
            ['code' => 'SNP-5', 'name' => 'Standar Sarana & Prasarana', 'cat' => 'sarpras'],
            ['code' => 'SNP-6', 'name' => 'Standar Pengelolaan Sekolah', 'cat' => 'pengelolaan'],
            ['code' => 'SNP-7', 'name' => 'Standar Pembiayaan & Anggaran', 'cat' => 'pembiayaan'],
            ['code' => 'SNP-8', 'name' => 'Standar Penilaian Pendidikan', 'cat' => 'penilaian'],
        ];

        foreach ($snpList as $snp) {
            $std = AccreditationStandard::updateOrCreate(
                ['code' => $snp['code']],
                [
                    'standard_name' => $snp['name'],
                    'category' => $snp['cat'],
                    'description' => "Indikator pemenuhan kelayakan {$snp['name']} BAN-SM.",
                    'weight' => 1.0,
                ]
            );

            // Create sample indicators
            $ind1 = AccreditationIndicator::updateOrCreate(
                [
                    'accreditation_standard_id' => $std->id,
                    'indicator_code' => "{$snp['code']}-IND-01",
                ],
                [
                    'indicator_name' => "Ketersediaan Berkas Mutu {$snp['code']}",
                    'description' => "Dokumen resmi pendukung kelayakan {$snp['name']}.",
                    'data_source' => 'manual',
                    'target_value' => '100% Lengkap',
                ]
            );

            // Seed Verified Evidence for first few standards
            $evidence = $accreditationService->uploadEvidence([
                'accreditation_indicator_id' => $ind1->id,
                'evidence_type' => 'document',
                'file_path' => "evidences/Dokumen_Kelayakan_{$snp['code']}.pdf",
                'description' => 'Dokumen terbukti diverifikasi asesmen internal BAN-SM.',
            ], $user->id);

            $accreditationService->verifyEvidence($evidence->id, $user->id);
        }
    }
}
