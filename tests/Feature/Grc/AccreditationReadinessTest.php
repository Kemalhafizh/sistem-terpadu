<?php

use App\Models\AccreditationEvidence;
use App\Models\AccreditationIndicator;
use App\Models\AccreditationStandard;
use App\Models\User;
use App\Services\Grc\AccreditationService;
use Tests\TestCase;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'admin', 'name' => 'Assessor Kesiapan BAN-SM']);
    $this->accreditationService = app(AccreditationService::class);
});

test('accreditation service calculates 8 snp scores and overall grade', function () {
    $std = AccreditationStandard::create([
        'code' => 'SNP-TEST',
        'standard_name' => 'Standar Uji Coba',
        'category' => 'test',
        'weight' => 1.0,
    ]);

    $ind = AccreditationIndicator::create([
        'accreditation_standard_id' => $std->id,
        'indicator_code' => 'IND-01',
        'indicator_name' => 'Indikator Kunci',
        'target_value' => '100%',
    ]);

    $calcBefore = $this->accreditationService->calculateStandardScore($std->id);
    expect($calcBefore['score_percent'])->toBe(0.0);

    $evidence = $this->accreditationService->uploadEvidence([
        'accreditation_indicator_id' => $ind->id,
        'file_path' => 'evidences/sk_kurikulum.pdf',
    ], $this->user->id);

    $this->accreditationService->verifyEvidence($evidence->id, $this->user->id);

    $calcAfter = $this->accreditationService->calculateStandardScore($std->id);
    expect($calcAfter['score_percent'])->toBe(100.0);

    $overall = $this->accreditationService->calculateOverallScore();
    expect($overall['overall_score'])->toBeGreaterThan(0);
});

test('admin can view accreditation dashboard and standard detail via controller', function () {
    $dashboardResponse = $this->actingAs($this->user)->get(route('accreditation.dashboard'));
    $dashboardResponse->assertStatus(200);

    $std = AccreditationStandard::create([
        'code' => 'SNP-VIEW',
        'standard_name' => 'Standar Tampilan View',
        'category' => 'view',
    ]);

    $detailResponse = $this->actingAs($this->user)->get(route('accreditation.standard_detail', $std->id));
    $detailResponse->assertStatus(200);
    $detailResponse->assertSee('SNP-VIEW');
});

test('user can upload evidence and verifier approves via controller', function () {
    $std = AccreditationStandard::create(['code' => 'SNP-UPL', 'standard_name' => 'Standar Upload', 'category' => 'upl']);
    $ind = AccreditationIndicator::create(['accreditation_standard_id' => $std->id, 'indicator_code' => 'UPL-01', 'indicator_name' => 'Bukti Upload', 'target_value' => '100%']);

    $uploadResponse = $this->actingAs($this->user)->post(route('accreditation.upload_evidence'), [
        'accreditation_indicator_id' => $ind->id,
        'evidence_type' => 'document',
        'file_path' => 'evidences/bukti_fisik_teruji.pdf',
    ]);

    $uploadResponse->assertRedirect();
    $evidence = AccreditationEvidence::where('file_path', 'evidences/bukti_fisik_teruji.pdf')->first();
    expect($evidence)->not()->toBeNull();

    $verifyResponse = $this->actingAs($this->user)->post(route('accreditation.verify_evidence', $evidence->id));
    $verifyResponse->assertRedirect();

    $evidence->refresh();
    expect($evidence->isVerified())->toBeTrue();
});

test('admin can view formal accreditation readiness report', function () {
    $reportResponse = $this->actingAs($this->user)->get(route('accreditation.report'));
    $reportResponse->assertStatus(200);
    $reportResponse->assertSee('Laporan Resmi Kesiapan Akreditasi BAN-SM');
});
