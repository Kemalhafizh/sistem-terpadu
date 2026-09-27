<?php

use App\Models\AcademicYear;
use App\Models\EdsAssessment;
use App\Models\EdsIndicator;
use App\Models\EdsPeriod;
use App\Models\EdsStandard;
use App\Models\User;
use App\Services\EdsService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    // Clear EDS tables to ensure clean slate per test
    EdsAssessment::query()->delete();
    EdsIndicator::query()->delete();
    EdsStandard::query()->delete();
    EdsPeriod::query()->delete();

    // Create users with different roles
    $this->waka = User::factory()->create(['role' => 'waka_kurikulum']);
    $this->kepsek = User::factory()->create(['role' => 'kepala_sekolah']);
    $this->guru = User::factory()->create(['role' => 'guru']);
    $this->student = User::factory()->create(['role' => 'student']);
    $this->admin = User::factory()->create(['role' => 'admin']);

    // Create active academic year
    $this->academicYear = AcademicYear::factory()->create([
        'year' => '2026/2027',
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);
});

test('guest cannot access eds index', function () {
    $response = $this->get(route('eds.index'));
    $response->assertRedirect('/login');
});

test('unauthorized roles cannot access eds index', function () {
    $responseGuru = $this->actingAs($this->guru)->get(route('eds.index'));
    $responseGuru->assertStatus(403);

    $responseStudent = $this->actingAs($this->student)->get(route('eds.index'));
    $responseStudent->assertStatus(403);
});

test('authorized roles can access eds index', function () {
    $responseWaka = $this->actingAs($this->waka)->get(route('eds.index'));
    $responseWaka->assertStatus(200);

    $responseKepsek = $this->actingAs($this->kepsek)->get(route('eds.index'));
    $responseKepsek->assertStatus(200);
});

test('waka kurikulum can store a new eds period', function () {
    $response = $this->actingAs($this->waka)->post(route('eds.store'), [
        'name' => 'EDS Ganjil Pertama',
        'start_date' => '2026-06-01',
        'end_date' => '2026-06-30',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('eds_periods', [
        'name' => 'EDS Ganjil Pertama',
        'academic_year_id' => $this->academicYear->id,
        'status' => 'draft',
    ]);
});

test('viewing a period dashboard auto-seeds default standards and transitions status', function () {
    $period = EdsPeriod::create([
        'name' => 'EDS Ganjil Pertama',
        'academic_year_id' => $this->academicYear->id,
        'status' => 'draft',
    ]);

    // Ensure database is initially empty of standards
    expect(EdsStandard::count())->toBe(0);

    $response = $this->actingAs($this->waka)->get(route('eds.show', $period->id));
    $response->assertStatus(200);

    // Verify database has been seeded with standard 8 SNP
    expect(EdsStandard::count())->toBe(8);
    expect(EdsIndicator::count())->toBe(18); // Default total indicator count

    // Verify period status transitioned to 'in_progress'
    $period->refresh();
    expect($period->status)->toBe('in_progress');
});

test('authorized roles can store batch assessments', function () {
    // Seed standard
    $service = app(EdsService::class);
    $service->seedDefaultStandards();

    $period = EdsPeriod::create([
        'name' => 'EDS Ganjil Pertama',
        'academic_year_id' => $this->academicYear->id,
        'status' => 'in_progress',
    ]);

    $indicator = EdsIndicator::first();
    $file = UploadedFile::fake()->create('bukti.pdf', 100);

    $response = $this->actingAs($this->waka)->post(route('eds.assess.store', $period->id), [
        'assessments' => [
            $indicator->id => [
                'score' => 4,
                'evidence' => 'Semua RPP terdokumentasi dengan baik.',
                'recommendation' => 'Pertahankan kualitas.',
                'files' => [$file],
            ],
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('eds_assessments', [
        'period_id' => $period->id,
        'indicator_id' => $indicator->id,
        'score' => 4,
        'evidence' => 'Semua RPP terdokumentasi dengan baik.',
        'recommendation' => 'Pertahankan kualitas.',
        'assessed_by' => $this->waka->id,
    ]);

    $assessment = EdsAssessment::where('period_id', $period->id)->where('indicator_id', $indicator->id)->first();
    expect($assessment->document_paths)->toBeArray();
    expect(count($assessment->document_paths))->toBe(1);

    // Check file physical existence on disk
    $path = str_replace('/storage/', '', $assessment->document_paths[0]);
    Storage::disk('public')->assertExists($path);
});

test('assessments calculate mathematical averages correctly', function () {
    $service = app(EdsService::class);
    $service->seedDefaultStandards();

    $period = EdsPeriod::create([
        'name' => 'EDS Ganjil Pertama',
        'academic_year_id' => $this->academicYear->id,
        'status' => 'in_progress',
    ]);

    $indicators = EdsIndicator::all();

    // Total indicators is 18
    // Let's assess 3 indicators: score 4, score 3, score 2
    // Math overall average = (4 + 3 + 2 + (15 * 0)) / 18 = 9 / 18 = 0.50
    EdsAssessment::create([
        'period_id' => $period->id,
        'indicator_id' => $indicators[0]->id,
        'score' => 4,
        'assessed_by' => $this->waka->id,
    ]);

    EdsAssessment::create([
        'period_id' => $period->id,
        'indicator_id' => $indicators[1]->id,
        'score' => 3,
        'assessed_by' => $this->waka->id,
    ]);

    EdsAssessment::create([
        'period_id' => $period->id,
        'indicator_id' => $indicators[2]->id,
        'score' => 2,
        'assessed_by' => $this->waka->id,
    ]);

    $scoresData = $service->getStandardScores($period);

    expect($scoresData['overall_average'])->toBe(0.50);
    expect($scoresData['overall_progress'])->toEqual(17); // 3 of 18 is 16.66% -> rounded to 17%
    expect($scoresData['assessed_indicators'])->toBe(3);
    expect($scoresData['total_indicators'])->toBe(18);
});

test('gap analysis correctly captures scores under 3', function () {
    $service = app(EdsService::class);
    $service->seedDefaultStandards();

    $period = EdsPeriod::create([
        'name' => 'EDS Ganjil Pertama',
        'academic_year_id' => $this->academicYear->id,
        'status' => 'in_progress',
    ]);

    $indicators = EdsIndicator::all();

    // 1 indicator with score 4 (above target)
    EdsAssessment::create([
        'period_id' => $period->id,
        'indicator_id' => $indicators[0]->id,
        'score' => 4,
        'evidence' => 'Aman',
        'recommendation' => 'Aman',
        'assessed_by' => $this->waka->id,
    ]);

    // 1 indicator with score 2 (under target)
    EdsAssessment::create([
        'period_id' => $period->id,
        'indicator_id' => $indicators[1]->id,
        'score' => 2,
        'evidence' => 'Masalah',
        'recommendation' => 'Solusi',
        'assessed_by' => $this->waka->id,
    ]);

    $gaps = $service->getGapAnalysis($period);

    // Unassessed indicators (16 indicators) count as score 0, which is < 3
    // Total gaps should be 17 (16 unassessed + 1 assessed as 2)
    expect(count($gaps))->toBe(17);

    // Verify assessed indicator in gaps
    $assessedGap = collect($gaps)->where('indicator.id', $indicators[1]->id)->first();
    expect($assessedGap)->not->toBeNull();
    expect($assessedGap['score'])->toBe(2);
    expect($assessedGap['evidence'])->toBe('Masalah');
    expect($assessedGap['recommendation'])->toBe('Solusi');

    // Verify that indicator with score 4 is NOT in gaps
    $goodGap = collect($gaps)->where('indicator.id', $indicators[0]->id)->first();
    expect($goodGap)->toBeNull();
});

test('can download pdf report', function () {
    $service = app(EdsService::class);
    $service->seedDefaultStandards();

    $period = EdsPeriod::create([
        'name' => 'EDS Ganjil Pertama',
        'academic_year_id' => $this->academicYear->id,
        'status' => 'in_progress',
    ]);

    $response = $this->actingAs($this->waka)->get(route('eds.export-pdf', $period->id));
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('comparison view displays correct baseline vs target metrics', function () {
    $service = app(EdsService::class);
    $service->seedDefaultStandards();

    $periodA = EdsPeriod::create([
        'name' => 'EDS Periode A',
        'academic_year_id' => $this->academicYear->id,
        'status' => 'completed',
    ]);

    $periodB = EdsPeriod::create([
        'name' => 'EDS Periode B',
        'academic_year_id' => $this->academicYear->id,
        'status' => 'completed',
    ]);

    $indicators = EdsIndicator::all();

    // Baseline (Period A): All indicators score 2 -> overall average 2.00
    foreach ($indicators as $ind) {
        EdsAssessment::create([
            'period_id' => $periodA->id,
            'indicator_id' => $ind->id,
            'score' => 2,
            'assessed_by' => $this->waka->id,
        ]);
    }

    // Target (Period B): All indicators score 3 -> overall average 3.00
    foreach ($indicators as $ind) {
        EdsAssessment::create([
            'period_id' => $periodB->id,
            'indicator_id' => $ind->id,
            'score' => 3,
            'assessed_by' => $this->waka->id,
        ]);
    }

    // Hit the comparison route
    $response = $this->actingAs($this->waka)->get(route('eds.comparison', [
        'previous_period_id' => $periodA->id,
        'current_period_id' => $periodB->id,
    ]));

    $response->assertStatus(200);

    // Test the Service comparison logic directly
    $comparisonData = $service->getComparisonData($periodB, $periodA);

    expect($comparisonData['previous_overall_average'])->toBe(2.00);
    expect($comparisonData['current_overall_average'])->toBe(3.00);
    expect($comparisonData['overall_difference'])->toBe(1.00);

    // Verify standards comparison details
    foreach ($comparisonData['standards'] as $code => $stdData) {
        expect($stdData['previous_score'])->toBe(2.00);
        expect($stdData['current_score'])->toBe(3.00);
        expect($stdData['difference'])->toBe(1.00);
    }
});

test('waka kurikulum can manually complete period', function () {
    $period = EdsPeriod::create([
        'name' => 'EDS Ganjil Pertama',
        'academic_year_id' => $this->academicYear->id,
        'status' => 'in_progress',
    ]);

    $response = $this->actingAs($this->waka)->post(route('eds.complete', $period->id));
    $response->assertRedirect();
    $response->assertSessionHas('success');

    $period->refresh();
    expect($period->status)->toBe('completed');
});
