<?php

namespace App\Services\Content;

use App\Models\OcrDocument;

class OcrProcessingService
{
    /**
     * Fields staff fill in per document type.
     *
     * No OCR engine is installed. This service used to "extract" the same
     * invented person for every upload, with a random NIK/KK/NISN/ijazah/akta
     * number. Now an upload is stored as pending with these fields empty, and
     * staff type the values from the scan on the review screen.
     */
    public const FIELDS = [
        'ktp' => ['nik', 'nama', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'alamat', 'agama'],
        'kk' => ['no_kk', 'nama_kepala_keluarga', 'alamat'],
        'ijazah' => ['nomor_ijazah', 'nama_siswa', 'sekolah_asal', 'nisn', 'tahun_lulus', 'nilai_rata_rata'],
        'akta' => ['no_akta', 'nama_anak', 'nama_ayah', 'nama_ibu', 'tanggal_lahir'],
        'other' => [],
    ];

    /**
     * Register an uploaded document, awaiting manual data entry.
     */
    public function processDocument(string $filePath, string $docType, int $userId): OcrDocument
    {
        $extractedFields = $this->extractFields($filePath, $docType);

        return OcrDocument::create([
            'original_file_path' => $filePath,
            'document_type' => $docType,
            'extracted_data' => $extractedFields['data'],
            'confidence_score' => $extractedFields['confidence'],
            'status' => 'pending',
            'processed_by' => $userId,
            'processed_at' => null,
        ]);
    }

    /**
     * Without an OCR engine nothing can be read from the scan: every field is
     * empty and there is no confidence score.
     *
     * @return array{confidence: null, data: array<string, null>}
     */
    public function extractFields(string $filePath, string $docType): array
    {
        return [
            'confidence' => null,
            'data' => array_fill_keys(self::FIELDS[$docType] ?? [], null),
        ];
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
            'birth_place' => $data['tempat_lahir'] ?? '',
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
        $doc->processed_at = now();
        $doc->save();

        return $doc;
    }
}
