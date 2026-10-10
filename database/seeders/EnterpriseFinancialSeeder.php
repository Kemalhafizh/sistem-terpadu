<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\Financial\BudgetPlanningService;
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

        // 2. Scholarship Program
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

    }
}
