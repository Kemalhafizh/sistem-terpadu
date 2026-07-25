<?php

use App\Models\Incident;
use App\Models\RiskRegister;
use App\Models\User;
use App\Services\Grc\RiskManagementService;
use Tests\TestCase;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'admin', 'name' => 'Risk & Incident Manager']);
    $this->riskService = app(RiskManagementService::class);
});

test('risk management service calculates risk score and levels accurately', function () {
    $calcLow = $this->riskService->calculateRiskScore('rare', 'insignificant');
    expect($calcLow['score'])->toBe(1);
    expect($calcLow['level'])->toBe('Low');

    $calcExtreme = $this->riskService->calculateRiskScore('almost_certain', 'catastrophic');
    expect($calcExtreme['score'])->toBe(25);
    expect($calcExtreme['level'])->toBe('Extreme');

    $risk = $this->riskService->assessRisk([
        'title' => 'Serangan Siber Test',
        'description' => 'Test Deskripsi Risiko',
        'category' => 'IT/Cyber',
        'likelihood' => 'likely', // 4
        'impact' => 'major', // 4 => Score 16
    ], $this->user->id);

    expect($risk->risk_score)->toBe(16);
    expect($risk->risk_level)->toBe('Extreme');
});

test('risk service generates 5x5 heatmap matrix structure', function () {
    $this->riskService->assessRisk([
        'title' => 'Sample Risk Matrix Item',
        'description' => 'Item Deskripsi',
        'likelihood' => 'possible',
        'impact' => 'moderate',
    ], $this->user->id);

    $matrix = $this->riskService->generateRiskMatrix();
    expect($matrix['likelihoods'])->toHaveCount(5);
    expect($matrix['impacts'])->toHaveCount(5);
    expect($matrix['total_risks'])->toBeGreaterThan(0);
});

test('user can report and resolve incidents', function () {
    $incident = $this->riskService->createIncident([
        'title' => 'Macet Genset Ujian',
        'description' => 'Genset tidak menyala otomatis',
        'severity' => 'high',
    ], $this->user->id);

    expect($incident->id)->not()->toBeNull();
    expect($incident->isResolved())->toBeFalse();

    $resolved = $this->riskService->resolveIncident($incident->id, 'Bahan bakar telah diisi ulang.', $this->user->id);
    expect($resolved->isResolved())->toBeTrue();
    expect($resolved->resolution_notes)->toBe('Bahan bakar telah diisi ulang.');
});

test('admin can access risk register, matrix, and incident controller endpoints', function () {
    $regResponse = $this->actingAs($this->user)->get(route('risk.register'));
    $regResponse->assertStatus(200);

    $createRiskResponse = $this->actingAs($this->user)->post(route('risk.create'), [
        'title' => 'Risiko Controller Test',
        'description' => 'Deskripsi Controller Test',
        'category' => 'Financial',
        'likelihood' => 'possible',
        'impact' => 'moderate',
    ]);
    $createRiskResponse->assertRedirect();
    expect(RiskRegister::where('title', 'Risiko Controller Test')->exists())->toBeTrue();

    $matrixResponse = $this->actingAs($this->user)->get(route('risk.matrix'));
    $matrixResponse->assertStatus(200);

    $incidentsResponse = $this->actingAs($this->user)->get(route('risk.incidents'));
    $incidentsResponse->assertStatus(200);

    $createIncResponse = $this->actingAs($this->user)->post(route('risk.create_incident'), [
        'title' => 'Insiden Controller Test',
        'description' => 'Deskripsi Insiden',
        'category' => 'Safety',
        'severity' => 'critical',
    ]);
    $createIncResponse->assertRedirect();

    $inc = Incident::where('title', 'Insiden Controller Test')->first();
    expect($inc)->not()->toBeNull();

    $resolveResponse = $this->actingAs($this->user)->post(route('risk.resolve_incident', $inc->id), [
        'resolution_notes' => 'Telah diatasi oleh tim IT',
    ]);
    $resolveResponse->assertRedirect();
    expect($inc->fresh()->isResolved())->toBeTrue();
});
