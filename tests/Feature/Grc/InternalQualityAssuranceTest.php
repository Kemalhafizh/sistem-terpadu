<?php

use App\Models\AccreditationStandard;
use App\Models\SelfEvaluationCycle;
use App\Models\SelfEvaluationResponse;
use App\Models\User;
use App\Services\Grc\QualityAssuranceService;
use Tests\TestCase;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'admin', 'name' => 'Assessor SPMI / EDS']);
    $this->qaService = app(QualityAssuranceService::class);
});

test('quality assurance service creates cycles and processes evaluation responses', function () {
    $cycle = $this->qaService->createCycle([
        'academic_year' => '2025/2026',
        'semester' => 'genap',
    ], $this->user->id);

    expect($cycle->id)->not()->toBeNull();

    $std = AccreditationStandard::create([
        'code' => 'SNP-QA',
        'standard_name' => 'Standar Uji QA',
        'category' => 'qa',
    ]);

    $resp = $this->qaService->submitResponse([
        'cycle_id' => $cycle->id,
        'standard_id' => $std->id,
        'score' => 4,
        'evidence_notes' => 'Catatan lengkap.',
    ], $this->user->id);

    expect($resp->score)->toBe(4);

    $results = $this->qaService->calculateResults($cycle->id);
    expect($results['overall_mean'])->toBeGreaterThan(0);
});

test('admin can view eds cycles and fill evaluation form via controller', function () {
    $cyclesResponse = $this->actingAs($this->user)->get(route('quality.cycles'));
    $cyclesResponse->assertStatus(200);

    $cycle = $this->qaService->createCycle([], $this->user->id);

    $fillResponse = $this->actingAs($this->user)->get(route('quality.fill_evaluation', $cycle->id));
    $fillResponse->assertStatus(200);
});

test('user can submit evaluation response and view results via controller', function () {
    $cycle = $this->qaService->createCycle([], $this->user->id);
    $std = AccreditationStandard::create(['code' => 'SNP-CTRL', 'standard_name' => 'Standar Controller', 'category' => 'ctrl']);

    $submitResponse = $this->actingAs($this->user)->post(route('quality.submit_evaluation', $cycle->id), [
        'standard_id' => $std->id,
        'score' => 3,
        'evidence_notes' => 'Catatan Controller',
    ]);

    $submitResponse->assertRedirect();
    expect(SelfEvaluationResponse::where('score', 3)->exists())->toBeTrue();

    $resultsResponse = $this->actingAs($this->user)->get(route('quality.results', $cycle->id));
    $resultsResponse->assertStatus(200);
});

test('qa service compares trends between two eds cycles', function () {
    $cycle1 = $this->qaService->createCycle(['academic_year' => '2024/2025'], $this->user->id);
    $cycle2 = $this->qaService->createCycle(['academic_year' => '2025/2026'], $this->user->id);
    $std = AccreditationStandard::create(['code' => 'SNP-CMP', 'standard_name' => 'Standar Komparasi', 'category' => 'cmp']);

    $this->qaService->submitResponse(['cycle_id' => $cycle1->id, 'standard_id' => $std->id, 'score' => 2], $this->user->id);
    $this->qaService->submitResponse(['cycle_id' => $cycle2->id, 'standard_id' => $std->id, 'score' => 4], $this->user->id);

    $comparison = $this->qaService->compareWithPrevious($cycle2->id, $cycle1->id);
    expect($comparison['comparison'][0]['difference'])->toBe(2.0);
    expect($comparison['comparison'][0]['trend'])->toBe('improving');
});
