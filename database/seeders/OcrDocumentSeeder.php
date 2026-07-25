<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\Content\OcrProcessingService;
use Illuminate\Database\Seeder;

class OcrDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $ocrService = app(OcrProcessingService::class);
        $processor = User::first();

        if (! $processor) {
            return;
        }

        // Seed KTP Document
        $ocrService->processDocument(
            'ocr_scans/Scan_KTP_Ortu_Ahmad.jpg',
            'ktp',
            $processor->id
        );

        // Seed Ijazah Document
        $ocrService->processDocument(
            'ocr_scans/Scan_Ijazah_SMP_Siswa.jpg',
            'ijazah',
            $processor->id
        );

        // Seed KK Document
        $ocrService->processDocument(
            'ocr_scans/Scan_Kartu_Keluarga.jpg',
            'kk',
            $processor->id
        );
    }
}
