<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\Financial\BudgetPlanningService;
use App\Services\Financial\CostCenterService;
use App\Services\Financial\ProcurementService;
use App\Services\Financial\ScholarshipService;
use Illuminate\Database\Seeder;

class EnterpriseFinancialSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::where('role', 'superadmin')->first() ?? User::factory()->create(['role' => 'superadmin']);
        $student = User::where('role', 'student')->first() ?? User::factory()->create(['role' => 'student']);

        // 1. RAPBS Budget Plan
        $budgetService = app(BudgetPlanningService::class);
        $plan = $budgetService->createPlan([
            'fiscal_year' => '2026/2027',
            'title' => 'RAPBS SMA Islam Sultan Agung 1 Semarang TA 2026/2027',
            'total_revenue_planned' => 2500000000,
            'total_expense_planned' => 2400000000,
        ], $superadmin->id);

        $budgetService->addLineItem($plan, [
            'category' => 'revenue',
            'sub_category' => 'Penerimaan SPP',
            'account_code' => '4-1100',
            'description' => 'Penerimaan SPP Bulanan Siswa Kelas X, XI, XII',
            'planned_amount' => 1800000000,
            'actual_amount' => 1750000000,
            'quarter' => 'annual',
        ]);

        $budgetService->addLineItem($plan, [
            'category' => 'expense',
            'sub_category' => 'Penggajian & Honor Guru',
            'account_code' => '5-1100',
            'description' => 'Gaji Pokok, Tunjangan, & Honor Mengajar Guru',
            'planned_amount' => 1200000000,
            'actual_amount' => 1180000000,
            'quarter' => 'annual',
        ]);

        $budgetService->submitForApproval($plan);
        $budgetService->approvePlan($plan, $superadmin->id);

        // 2. Procurement Request & PO
        $procurementService = app(ProcurementService::class);
        $procReq = $procurementService->createRequest([
            'title' => 'Pengadaan 15 Unit Laptop Lab Komputer CBT',
            'description' => 'Peremajaan perangkat lab komputer pendukung asesmen berbasis komputer.',
            'department' => 'it',
            'urgency' => 'high',
            'estimated_budget' => 150000000,
        ], $superadmin->id);

        $procurementService->submitForApproval($procReq);
        $procurementService->approveRequest($procReq, $superadmin->id, 'Disetujui sesuai pagu RAPBS 2026/2027.');

        $po = $procurementService->createPurchaseOrder($procReq, [
            'vendor_name' => 'PT Sultan Agung Teknologi',
            'vendor_contact' => 'sales@sultanagungtech.co.id',
            'delivery_date' => date('Y-m-d', strtotime('+5 days')),
            'items' => [
                ['name' => 'Laptop Core i5 16GB RAM', 'qty' => 15, 'unit_price' => 10000000],
            ],
            'tax_amount' => 0,
        ], $superadmin->id);

        $procurementService->recordGoodsReceived($po, [
            'received_date' => date('Y-m-d'),
            'items_received' => [['name' => 'Laptop Core i5', 'qty' => 15]],
            'condition_notes' => 'Barang diterima lengkap dan tersegel.',
            'is_complete' => true,
        ], $superadmin->id);

        // 3. Scholarship Program
        $scholarshipService = app(ScholarshipService::class);
        $schProg = $scholarshipService->createProgram([
            'name' => 'Beasiswa Prestasi Tahfidz Al-Qur\'an YBWSA',
            'type' => 'internal_merit',
            'source' => 'yayasan',
            'academic_year' => '2026/2027',
            'total_quota' => 20,
            'amount_per_student' => 3000000,
            'application_start_date' => date('Y-m-d'),
            'application_end_date' => date('Y-m-d', strtotime('+30 days')),
        ], $superadmin->id);

        $scholarshipService->openApplications($schProg);

        $schApp = $scholarshipService->submitApplication($schProg, $student->id, [
            'gpa_score' => 3.85,
            'family_income' => 3000000,
        ]);

        $scholarshipService->reviewApplication($schApp, $superadmin->id, 'approved', 'Memenuhi syarat kelulusan tahfidz 3 Juz.');
        $scholarshipService->disburse($schApp, 'wadiah_credit', $superadmin->id);

        // 4. Cost Centers
        $costService = app(CostCenterService::class);
        $cc1 = $costService->createCostCenter([
            'code' => 'CC-KUR-01',
            'name' => 'Pusat Biaya Kurikulum & Asesmen',
            'department' => 'kurikulum',
            'head_id' => $superadmin->id,
            'annual_budget_allocation' => 300000000,
        ]);

        $costService->recordExpense($cc1, 45000000, 'Pengadaan Kertas & Penggandaan Soal Asesmen Sumatif', $superadmin->id);
    }
}
