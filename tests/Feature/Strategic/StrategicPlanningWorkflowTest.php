<?php

use App\Models\AnnualWorkplan;
use App\Models\KeyPerformanceIndicator;
use App\Models\SatisfactionResponse;
use App\Models\SatisfactionSurvey;
use App\Models\StrategicPlan;
use App\Models\SwotAnalysis;
use App\Models\User;

beforeEach(function () {
    $this->head = User::factory()->create(['role' => 'kepala_sekolah']);
});

function makePlan(array $attributes = []): StrategicPlan
{
    return StrategicPlan::create($attributes + [
        'title' => 'RENSTRA 2026–2030', 'vision' => 'Unggul', 'mission' => 'Mendidik', 'start_year' => 2026, 'end_year' => 2030, 'status' => 'active',
    ]);
}

test('modul strategis tidak lagi terbuka untuk tamu maupun guru', function () {
    // These routes had no auth at all: anyone could read and create plans.
    $this->get(route('strategic.dashboard'))->assertRedirect(route('login'));
    $this->post(route('strategic.plan.store'), ['title' => 'Iseng'])->assertRedirect(route('login'));
    expect(StrategicPlan::count())->toBe(0);

    $this->actingAs(User::factory()->create(['role' => 'guru']))->get(route('strategic.plan.index'))->assertForbidden();
    $this->actingAs($this->head)->get(route('strategic.plan.index'))->assertOk();
});

test('RENSTRA dibuat, diberi sasaran dan KPI, lalu KPI diukur', function () {
    $this->actingAs($this->head)->get(route('strategic.plan.create'))->assertOk();
    $this->post(route('strategic.plan.store'), [
        'title' => 'RENSTRA 2026–2030', 'vision' => 'Sekolah unggul', 'mission' => "Misi 1\nMisi 2",
        'start_year' => 2026, 'end_year' => 2030, 'status' => 'active',
    ])->assertRedirect();
    $plan = StrategicPlan::sole();

    $this->post(route('strategic.plan.objectives.store', $plan->id), [
        'perspective' => 'stakeholder', 'code' => 'S1', 'objective' => 'Kepuasan orang tua meningkat', 'weight' => 25,
    ])->assertRedirect(route('strategic.plan.show', $plan->id));
    $objective = $plan->objectives()->sole();

    $this->post(route('strategic.objectives.kpis.store', $objective->id), [
        'code' => 'K1.1', 'indicator' => 'Indeks kepuasan orang tua', 'unit' => '%', 'baseline_value' => 70,
        'target_value' => 85, 'polarity' => 'higher_is_better', 'frequency' => 'semester',
    ])->assertRedirect(route('strategic.plan.show', $plan->id));
    $kpi = KeyPerformanceIndicator::sole();

    $this->get(route('strategic.plan.show', $plan->id))->assertOk()->assertSee('Indeks kepuasan orang tua');
    $this->get(route('strategic.dashboard'))->assertOk()->assertSee('RENSTRA 2026–2030');

    $this->post(route('strategic.kpi.measure', $kpi->id), ['measurement_date' => '2026-06-30', 'value' => 78])->assertSessionHasNoErrors();
    // The unique (kpi_id, measurement_date) index used to turn this into a 500.
    $this->post(route('strategic.kpi.measure', $kpi->id), ['measurement_date' => '2026-06-30', 'value' => 80])
        ->assertSessionHasErrors('measurement_date');

    expect((float) $kpi->fresh()->current_value)->toBe(78.0);
    $this->get(route('strategic.kpi.report', $kpi->id))->assertOk()->assertSee('Belum tercapai')->assertSee('30 Jun 2026');
});

test('RKS wajib terkait RENSTRA dan tersimpan lewat form', function () {
    $plan = makePlan();
    $this->actingAs($this->head)->get(route('strategic.workplan.create'))->assertOk()->assertSee($plan->title);

    // annual_workplans.strategic_plan_id is NOT NULL: storing without it used to be a 500.
    $this->post(route('strategic.workplan.store'), ['title' => 'RKS 2026', 'fiscal_year' => 2026, 'total_budget' => 1000000])
        ->assertSessionHasErrors('strategic_plan_id');

    $this->actingAs($this->head)->post(route('strategic.workplan.store'), [
        'strategic_plan_id' => $plan->id, 'title' => 'RKS 2026', 'fiscal_year' => 2026, 'total_budget' => 1500000,
    ])->assertRedirect();

    $workplan = AnnualWorkplan::sole();
    expect($workplan->prepared_by)->toBe($this->head->id);
    $this->get(route('strategic.workplan.show', $workplan->id))->assertOk()->assertSee('RKS 2026')->assertSee('Rp 1.500.000');
    $this->get(route('strategic.workplan.index'))->assertOk()->assertSee('Rencana Kerja Sekolah');
});

test('survei dibuat dengan pertanyaan, diisi responden sekali, dan hasilnya terhitung', function () {
    $this->actingAs($this->head)->post(route('strategic.survey.store'), [
        'title' => 'IKM Orang Tua', 'survey_type' => 'ikm_orangtua', 'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addWeek()->toDateString(), 'status' => 'active', 'is_anonymous' => '1',
        'questions' => [
            ['dimension' => 'Layanan', 'text' => 'Puas dengan layanan TU?', 'scale' => 'likert_5'],
            ['dimension' => 'Layanan', 'text' => 'Guru mudah dihubungi?', 'scale' => 'yes_no'],
            ['dimension' => 'Saran', 'text' => 'Saran Anda', 'scale' => 'open_text'],
        ],
    ])->assertRedirect();

    $survey = SatisfactionSurvey::with('dimensions.questions')->sole();
    expect($survey->dimensions)->toHaveCount(2);
    [$likert, $yesNo, $open] = $survey->dimensions->flatMap->questions->all();

    $parent = User::factory()->create(['role' => 'parent']);
    $this->actingAs($parent)->get(route('strategic.survey.respond', $survey->id))->assertOk()->assertSee('Puas dengan layanan TU?');
    $answers = ['answers' => [$likert->id => 4, $yesNo->id => 1, $open->id => 'Parkir diperluas']];

    // storeResponse used to redirect with "saved" without saving anything.
    $this->post(route('strategic.survey.storeResponse', $survey->id), $answers)->assertRedirect();
    $this->post(route('strategic.survey.storeResponse', $survey->id), $answers)->assertSessionHas('info');
    expect(SatisfactionResponse::count())->toBe(3);

    $this->actingAs(User::factory()->create(['role' => 'guru']))
        ->post(route('strategic.survey.storeResponse', $survey->id), ['answers' => [$yesNo->id => 1]])
        ->assertSessionHasErrors("answers.{$likert->id}");

    $this->actingAs($this->head)->get(route('strategic.survey.results', $survey->id))->assertOk()
        ->assertSee('Parkir diperluas')
        ->assertSee('rata-rata 4', false);
    $this->get(route('strategic.survey.index'))->assertOk()->assertSee('Survei Kepuasan');
});

test('survei draf atau di luar periode menolak tanggapan', function () {
    $survey = SatisfactionSurvey::create([
        'title' => 'Draf', 'survey_type' => 'custom', 'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
        'status' => 'draft', 'created_by' => $this->head->id,
    ]);

    $this->actingAs(User::factory()->create(['role' => 'student']))
        ->post(route('strategic.survey.storeResponse', $survey->id), ['answers' => []])
        ->assertForbidden();
});

test('analisis SWOT menyimpan faktor per baris per kuadran', function () {
    $this->actingAs($this->head)->get(route('strategic.swot.create'))->assertOk();
    $this->post(route('strategic.swot.store'), [
        'title' => 'SWOT 2026', 'analysis_year' => 2026,
        'items' => ['strength' => "Guru tersertifikasi\n\nAlumni kuat", 'threat' => 'Sekolah pesaing baru'],
        'conclusion' => 'Perkuat branding',
    ])->assertRedirect();

    $swot = SwotAnalysis::with('items')->sole();
    expect($swot->items->where('quadrant', 'strength')->pluck('description')->all())->toBe(['Guru tersertifikasi', 'Alumni kuat'])
        ->and($swot->items->where('quadrant', 'threat'))->toHaveCount(1);

    $this->get(route('strategic.swot.show', $swot->id))->assertOk()->assertSee('Alumni kuat')->assertSee('Perkuat branding');
    $this->get(route('strategic.swot.index'))->assertOk()->assertSee('Analisis SWOT');
});
