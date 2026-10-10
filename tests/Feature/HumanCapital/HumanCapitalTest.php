<?php

namespace Tests\Feature\HumanCapital;

use App\Models\User;
use App\Services\HumanCapital\Evaluation360Service;
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
}
