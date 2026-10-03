<?php

use App\Models\OcrDocument;
use App\Models\User;
use App\Services\Content\OcrProcessingService;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'admin', 'name' => 'Admin PPDB IDP']);
    $this->ocrService = app(OcrProcessingService::class);
});

test('tanpa mesin OCR, dokumen disimpan menunggu input manual tanpa data karangan', function () {
    // This used to "extract" the same invented person for every file, with a
    // random NIK / KK / NISN / ijazah / akta number.
    foreach (['ktp' => 'nik', 'kk' => 'no_kk', 'ijazah' => 'nisn', 'akta' => 'no_akta'] as $type => $numberField) {
        $doc = $this->ocrService->processDocument("scans/{$type}.jpg", $type, $this->user->id);

        expect($doc->status)->toBe('pending')
            ->and($doc->confidence_score)->toBeNull()
            ->and($doc->processed_at)->toBeNull()
            ->and($doc->extracted_data)->toHaveKey($numberField)
            ->and(array_filter($doc->extracted_data, fn ($value) => $value !== null))->toBe([]);
    }

    // Same input, same output: nothing random left.
    expect($this->ocrService->extractFields('a.jpg', 'ktp'))->toBe($this->ocrService->extractFields('b.jpg', 'ktp'));
});

test('profil siswa dari dokumen OCR hanya berisi data yang diverifikasi petugas', function () {
    $doc = $this->ocrService->processDocument('scans/ijazah_sample.jpg', 'ijazah', $this->user->id);

    expect($this->ocrService->mapToStudentProfile($doc->id))->each->toBe('');

    $this->ocrService->validateExtraction($doc->id, ['nama_siswa' => 'Aisyah Putri', 'nisn' => '0081234567', 'sekolah_asal' => 'SMP Negeri 2']);

    $profile = $this->ocrService->mapToStudentProfile($doc->id);
    expect($profile['name'])->toBe('Aisyah Putri')
        ->and($profile['nisn'])->toBe('0081234567')
        ->and($profile['previous_school'])->toBe('SMP Negeri 2')
        // The birth place used to default to "Semarang".
        ->and($profile['birth_place'])->toBe('')
        ->and($doc->fresh()->status)->toBe('completed');
});

test('admin can view ocr console and upload scanned document via controller', function () {
    $indexResponse = $this->actingAs($this->user)->get(route('ocr.index'));
    $indexResponse->assertStatus(200);

    $uploadResponse = $this->actingAs($this->user)->post(route('ocr.upload'), [
        'document_type' => 'ktp',
        'file_name' => 'Scan_KTP_Tes.jpg',
    ]);

    $uploadResponse->assertRedirect()->assertSessionHas('info');
    expect(OcrDocument::where('document_type', 'ktp')->sole()->status)->toBe('pending');
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
