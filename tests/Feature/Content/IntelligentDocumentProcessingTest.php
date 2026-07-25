<?php

use App\Models\OcrDocument;
use App\Models\User;
use App\Services\Content\OcrProcessingService;
use Tests\TestCase;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'admin', 'name' => 'Admin PPDB IDP']);
    $this->ocrService = app(OcrProcessingService::class);
});

test('ocr processing service extracts structured fields for ktp and ijazah', function () {
    $ktpDoc = $this->ocrService->processDocument(
        'scans/ktp_ortu.jpg',
        'ktp',
        $this->user->id
    );

    expect($ktpDoc->id)->not()->toBeNull();
    expect($ktpDoc->extracted_data)->toHaveKey('nik');
    expect($ktpDoc->confidence_score)->toBeGreaterThanOrEqual(0.90);

    $ijazahDoc = $this->ocrService->processDocument(
        'scans/ijazah_smp.jpg',
        'ijazah',
        $this->user->id
    );

    expect($ijazahDoc->extracted_data)->toHaveKey('nomor_ijazah');
    expect($ijazahDoc->extracted_data)->toHaveKey('nisn');
});

test('ocr service maps extracted data to student profile format', function () {
    $doc = $this->ocrService->processDocument(
        'scans/ijazah_sample.jpg',
        'ijazah',
        $this->user->id
    );

    $profile = $this->ocrService->mapToStudentProfile($doc->id);
    expect($profile['name'])->toBe('Muhammad Raihan');
    expect($profile['previous_school'])->toBe('SMP Islam Sultan Agung 1 Semarang');
});

test('admin can view ocr console and upload scanned document via controller', function () {
    $indexResponse = $this->actingAs($this->user)->get(route('ocr.index'));
    $indexResponse->assertStatus(200);

    $uploadResponse = $this->actingAs($this->user)->post(route('ocr.upload'), [
        'document_type' => 'ktp',
        'file_name' => 'Scan_KTP_Tes.jpg',
    ]);

    $uploadResponse->assertRedirect();
    expect(OcrDocument::where('document_type', 'ktp')->exists())->toBeTrue();
});

test('user can view side by side review view and apply verified data', function () {
    $doc = $this->ocrService->processDocument('scans/ktp_verif.jpg', 'ktp', $this->user->id);

    $reviewResponse = $this->actingAs($this->user)->get(route('ocr.review', $doc->id));
    $reviewResponse->assertStatus(200);
    $reviewResponse->assertSee('Side-by-Side IDP OCR Verification');

    $applyResponse = $this->actingAs($this->user)->post(route('ocr.apply', $doc->id), [
        'extracted_data' => [
            'nama' => 'H. Bambang Sulistyo (Verified)',
            'nik' => '3374019998887776',
        ],
    ]);

    $applyResponse->assertRedirect(route('ocr.index'));

    $doc->refresh();
    expect($doc->extracted_data['nama'])->toBe('H. Bambang Sulistyo (Verified)');
});
