<?php

namespace Tests\Feature\Financial;

use App\Models\User;
use App\Services\Financial\ScholarshipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnterpriseFinancialTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'superadmin']);
        $this->student = User::factory()->create(['role' => 'student']);
    }

    /** @test */
    public function test_33_3_scholarship_portal_workflow()
    {
        $service = app(ScholarshipService::class);

        // 1. Create Program & Open
        $prog = $service->createProgram([
            'name' => 'Beasiswa Sultan Agung Need-Based',
            'type' => 'internal_need',
            'source' => 'yayasan',
            'academic_year' => '2026/2027',
            'total_quota' => 10,
            'amount_per_student' => 2000000,
            'application_start_date' => date('Y-m-d'),
            'application_end_date' => date('Y-m-d', strtotime('+15 days')),
        ], $this->admin->id);

        $service->openApplications($prog);

        // 2. Submit Application
        $app = $service->submitApplication($prog, $this->student->id, [
            'gpa_score' => 3.90,
            'family_income' => 2000000,
        ]);

        // 3. Review & Disburse
        $service->reviewApplication($app, $this->admin->id, 'approved');
        $disbursement = $service->disburse($app, 'bank_transfer', $this->admin->id);

        $this->assertEquals('disbursed', $app->fresh()->status);
        $this->assertEquals(2000000, $disbursement->amount);

        // Controller endpoint test
        $response = $this->actingAs($this->admin)->get(route('financial.scholarships.programs'));
        $response->assertStatus(200);
        $response->assertSee('Beasiswa Sultan Agung Need-Based');
    }
}
