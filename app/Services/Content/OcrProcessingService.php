<?php

namespace App\Services\Content;

use App\Models\OcrDocument;

class OcrProcessingService
{
    /**
     * Process document via IDP OCR engine.
     */
    public function processDocument(string $filePath, string $docType, int $userId): OcrDocument
    {
        $extractedFields = $this->extractFields($filePath, $docType);

        return OcrDocument::create([
            'original_file_path' => $filePath,
            'document_type' => $docType,
            'extracted_data' => $extractedFields['data'],
            'confidence_score' => $extractedFields['confidence'],
            'status' => 'completed',
            'processed_by' => $userId,
            'processed_at' => now(),
        ]);
    }

    /**
     * Extract structured fields from scanned document based on document type.
     */
    public function extractFields(string $filePath, string $docType): array
    {
        // Simulated Intelligent Document Processing (IDP) OCR Extraction Pipeline
        switch ($docType) {
            case 'ktp':
                return [
                    'confidence' => 0.96,
                    'data' => [
                        'nik' => '337401'.rand(10000000, 99999999),
                        'nama' => 'H. Bambang Sulistyo, S.E.',
                        'tempat_lahir' => 'Semarang',
                        'tanggal_lahir' => '1980-05-12',
                        'jenis_kelamin' => 'Laki-laki',
                        'alamat' => 'Jl. Sultan Agung No. 105, Candi, Semarang',
                        'agama' => 'Islam',
                    ],
                ];
            case 'kk':
                return [
                    'confidence' => 0.94,
                    'data' => [
                        'no_kk' => '337401'.rand(10000000, 99999999),
                        'nama_kepala_keluarga' => 'H. Bambang Sulistyo, S.E.',
                        'alamat' => 'Jl. Sultan Agung No. 105, Candi, Semarang',
                        'anggota' => [
                            ['nama' => 'H. Bambang Sulistyo', 'peran' => 'Kepala Keluarga'],
                            ['nama' => 'Hj. Siti Aminah', 'peran' => 'Istri'],
                            ['nama' => 'Muhammad Raihan', 'peran' => 'Anak'],
                        ],
                    ],
                ];
            case 'ijazah':
                return [
                    'confidence' => 0.98,
                    'data' => [
                        'nomor_ijazah' => 'DN-03/D-SMP/26/'.rand(100000, 999999),
                        'nama_siswa' => 'Muhammad Raihan',
                        'sekolah_asal' => 'SMP Islam Sultan Agung 1 Semarang',
                        'nisn' => '0089'.rand(100000, 999999),
                        'tahun_lulus' => '2026',
                        'nilai_rata_rata' => 91.50,
                    ],
                ];
            case 'akta':
            default:
                return [
                    'confidence' => 0.92,
                    'data' => [
                        'no_akta' => '3374-LU-'.rand(100000, 999999),
                        'nama_anak' => 'Muhammad Raihan',
                        'nama_ayah' => 'H. Bambang Sulistyo',
                        'nama_ibu' => 'Hj. Siti Aminah',
                        'tanggal_lahir' => '2011-08-20',
                    ],
                ];
        }
    }

    /**
     * Map OCR extracted data to student PPDB registration profile.
     */
    public function mapToStudentProfile(int $ocrDocId): array
    {
        $doc = OcrDocument::findOrFail($ocrDocId);
        $data = $doc->extracted_data ?? [];

        return [
            'name' => $data['nama_siswa'] ?? $data['nama_anak'] ?? $data['nama'] ?? '',
            'nisn' => $data['nisn'] ?? '',
            'nik' => $data['nik'] ?? '',
            'birth_place' => $data['tempat_lahir'] ?? 'Semarang',
            'birth_date' => $data['tanggal_lahir'] ?? '',
            'address' => $data['alamat'] ?? '',
            'parent_name' => $data['nama_kepala_keluarga'] ?? $data['nama_ayah'] ?? '',
            'previous_school' => $data['sekolah_asal'] ?? '',
        ];
    }

    /**
     * Validate and save verified extraction changes.
     */
    public function validateExtraction(int $ocrDocId, array $verifiedData): OcrDocument
    {
        $doc = OcrDocument::findOrFail($ocrDocId);
        $doc->extracted_data = array_merge($doc->extracted_data ?? [], $verifiedData);
        $doc->status = 'completed';
        $doc->save();

        return $doc;
    }
}
