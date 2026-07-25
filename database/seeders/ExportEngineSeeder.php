<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\Content\ExportEngineService;
use Illuminate\Database\Seeder;

class ExportEngineSeeder extends Seeder
{
    public function run(): void
    {
        $exportService = app(ExportEngineService::class);
        $user = User::first();

        if (! $user) {
            return;
        }

        // Seed XLSX Export Job
        $exportService->queueExport(
            'Ekspor Data Pokok Siswa Semester Genap 2026',
            'students',
            'xlsx',
            $user->id
        );

        // Seed PDF/A Export Job
        $exportService->queueExport(
            'Arsip Sertifikat Kelulusan PDF/A ISO 19005',
            'grades',
            'pdf_a',
            $user->id
        );

        // Seed XML Dapodik Export Job
        $exportService->queueExport(
            'Sinkronisasi Dataset XML Dapodik Kemdikbud',
            'teachers',
            'xml_dapodik',
            $user->id
        );
    }
}
