<?php

namespace Tests\Feature\Financial;

use App\Models\User;
use App\Services\Financial\BudgetPlanningService;
use App\Services\Financial\CostCenterService;
use App\Services\Financial\ProcurementService;
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
    public function test_33_1_rapbs_budget_planning_lifecycle()
    {
        $service = app(BudgetPlanningService::class);

        // 1. Create plan
        $plan = $service->createPlan([
            'fiscal_year' => '2026/2027',
            'title' => 'RAPBS Testing 2026',
            'total_revenue_planned' => 1000000000,
            'total_expense_planned' => 900000000,
        ], $this->admin->id);

        $this->assertEquals('draft', $plan->status);

        // 2. Add Line Items
        $service->addLineItem($plan, [
            'category' => 'revenue',
            'sub_category' => 'SPP',
            'account_code' => '4-1000',
            'description' => 'Penerimaan SPP',
            'planned_amount' => 1000000000,
            'actual_amount' => 950000000,
        ]);

        $service->addLineItem($plan, [
            'category' => 'expense',
            'sub_category' => 'Gaji',
            'account_code' => '5-1000',
            'description' => 'Gaji Guru',
            'planned_amount' => 900000000,
            'actual_amount' => 880000000,
        ]);

        // 3. Approval
        $service->submitForApproval($plan);
        $service->approvePlan($plan, $this->admin->id);

        $this->assertEquals('approved', $plan->fresh()->status);

        // 4. Variance report
        $report = $service->getVarianceReport($plan->fresh());
        $this->assertEquals(1000000000, $report['revenue']['planned']);
        $this->assertEquals(950000000, $report['revenue']['actual']);

        // 5. Controller HTTP view test
        $response = $this->actingAs($this->admin)->get(route('financial.budget.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('RAPBS Multi-Year Budget Planning');
    }

    /** @test */
    public function test_33_2_procurement_po_grn_workflow()
    {
        $service = app(ProcurementService::class);

        // 1. Request
        $procReq = $service->createRequest([
            'title' => 'Pengadaan Proyektor Kelas',
            'description' => 'Proyektor interaktif kelas XI',
            'department' => 'facilities',
            'urgency' => 'normal',
            'estimated_budget' => 25000000,
        ], $this->admin->id);

        $service->submitForApproval($procReq);
        $service->approveRequest($procReq, $this->admin->id);

        // 2. Issue PO
        $po = $service->createPurchaseOrder($procReq, [
            'vendor_name' => 'CV Media Sarana',
            'delivery_date' => date('Y-m-d', strtotime('+3 days')),
            'items' => [['name' => 'Proyektor HD', 'qty' => 2, 'unit_price' => 12500000]],
        ], $this->admin->id);

        $this->assertEquals('issued', $po->status);

        // 3. Record GRN
        $grn = $service->recordGoodsReceived($po, [
            'received_date' => date('Y-m-d'),
            'items_received' => [['name' => 'Proyektor HD', 'qty' => 2]],
            'is_complete' => true,
        ], $this->admin->id);

        $this->assertEquals('delivered', $po->fresh()->status);
        $this->assertEquals('received', $procReq->fresh()->status);

        // Controller endpoint test
        $response = $this->actingAs($this->admin)->get(route('financial.procurement.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Procurement');
        $response->assertSee('Purchase Order (PO) Workflow');
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

    /** @test */
    public function test_33_4_cost_center_budget_allocation()
    {
        $service = app(CostCenterService::class);

        $cc = $service->createCostCenter([
            'code' => 'CC-SAR-01',
            'name' => 'Pusat Biaya Pemeliharaan Sarpras',
            'department' => 'sarpras',
            'head_id' => $this->admin->id,
            'annual_budget_allocation' => 100000000,
        ]);

        $service->recordExpense($cc, 15000000, 'Perbaikan Atap Gedung B', $this->admin->id);

        $util = $service->getUtilization($cc->fresh());
        $this->assertEquals(100000000, $util['allocation']);
        $this->assertEquals(15000000, $util['spent']);
        $this->assertEquals(15, $util['utilization_rate']);

        // Controller endpoint test
        $response = $this->actingAs($this->admin)->get(route('financial.cost-centers.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('CC-SAR-01');
    }
}
