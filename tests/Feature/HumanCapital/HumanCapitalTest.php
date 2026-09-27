<?php

namespace Tests\Feature\HumanCapital;

use App\Models\CpdCategory;
use App\Models\User;
use App\Services\HumanCapital\CpdTrackerService;
use App\Services\HumanCapital\Evaluation360Service;
use App\Services\HumanCapital\SuccessionPlanningService;
use App\Services\HumanCapital\WorkloadBalancingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HumanCapitalTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $teacher;

    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'superadmin']);
        $this->teacher = User::factory()->create(['role' => 'guru']);
        $this->student = User::factory()->create(['role' => 'student']);
    }

    /** @test */
    public function test_36_1_360_degree_teacher_evaluation_and_edom()
    {
        $service = app(Evaluation360Service::class);

        // 1. Create Cycle & Instrument
        $cycle = $service->createCycle([
            'title' => 'Siklus Evaluasi Test',
            'academic_year' => '2026/2027',
        ], $this->admin->id);

        $inst = $service->createInstrument($cycle, ['evaluator_type' => 'student']);

        // 2. Submit response
        $resp = $service->submitResponse($inst, $this->teacher->id, $this->student->id, [5, 5, 4, 5], 'Sangat baik');
        $this->assertEquals($this->teacher->id, $resp->evaluatee_id);

        // 3. Summary
        $summary = $service->generateSummary($cycle->id, $this->teacher->id);
        $this->assertGreaterThan(0, $summary->overall_score);

        // HTTP View Test
        $response = $this->actingAs($this->admin)->get(route('hc.evaluation.cycles'));
        $response->assertStatus(200);
        $response->assertSee('360-Degree Teacher Evaluation');
    }

    /** @test */
    public function test_36_2_cpd_pkb_credit_tracker()
    {
        $service = app(CpdTrackerService::class);

        $cat = CpdCategory::create([
            'name' => 'Pengembangan Diri',
            'description' => 'Diklat fungsional',
            'credit_weight' => 1.0,
        ]);

        // Log Activity
        $act = $service->logActivity([
            'cpd_category_id' => $cat->id,
            'title' => 'Diklat Kurikulum Merdeka',
            'description' => 'Pelatihan 32 JP',
            'hours' => 32,
            'credits_earned' => 4.0,
        ], $this->teacher->id);

        $this->assertEquals('pending', $act->status);

        // Verify
        $verifiedAct = $service->verifyActivity($act, $this->admin->id, true);
        $this->assertEquals('verified', $verifiedAct->status);

        // HTTP View Test
        $response = $this->actingAs($this->admin)->get(route('hc.cpd.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Continuing Professional Development');
    }

    /** @test */
    public function test_36_3_school_leadership_succession_planning()
    {
        $service = app(SuccessionPlanningService::class);

        // Position & Candidate
        $pos = $service->identifyPosition('waka_kurikulum', $this->teacher->id);
        $candTeacher = User::factory()->create(['role' => 'guru']);

        $cand = $service->addCandidate($pos, $candTeacher->id, 'ready_1yr', ['Disiplin'], ['Manajemen'], $this->admin->id);
        $this->assertEquals('ready_1yr', $cand->readiness_level);

        // HTTP View Test
        $response = $this->actingAs($this->admin)->get(route('hc.succession.positions'));
        $response->assertStatus(200);
        $response->assertSee('School Leadership Succession Planning');
    }

    /** @test */
    public function test_36_4_teacher_workload_balancing()
    {
        $service = app(WorkloadBalancingService::class);

        $workload = $service->calculateWorkload($this->teacher->id, 24, ['Wali Kelas'], ['Pembina Ekskul']);
        $this->assertEquals('balanced', $workload->status);

        // HTTP View Test
        $response = $this->actingAs($this->admin)->get(route('hc.workload.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Teacher Workload Balancing');
    }
}
