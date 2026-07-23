<?php

namespace Database\Seeders;

use App\Services\BusinessRuleEngineService;
use Illuminate\Database\Seeder;

class BpaRuleEngineSeeder extends Seeder
{
    public function run(): void
    {
        /** @var BusinessRuleEngineService $ruleEngine */
        $ruleEngine = app(BusinessRuleEngineService::class);

        // Aturan 1: Tiket BK & WA Orang Tua
        $ruleEngine->createRule([
            'name' => 'Tiket BK & WA Otomatis (Presensi Siswa < 80%)',
            'trigger_event' => 'attendance.checked',
            'conditions' => [
                ['field' => 'attendance_percentage', 'operator' => '<', 'value' => 80],
                ['field' => 'tahfidz_status', 'operator' => '==', 'value' => 'Behind'],
            ],
            'actions' => [
                ['action_type' => 'issue_counseling_ticket', 'params' => []],
                ['action_type' => 'send_whatsapp_alert', 'params' => ['recipient' => 'parent']],
            ],
            'is_active' => true,
        ]);

        // Aturan 2: Potongan Payroll Keterlambatan Pegawai
        $ruleEngine->createRule([
            'name' => 'Potongan Payroll Keterlambatan Pegawai > 3 Kali',
            'trigger_event' => 'employee.lateness',
            'conditions' => [
                ['field' => 'employee_lateness_count', 'operator' => '>', 'value' => 3],
            ],
            'actions' => [
                ['action_type' => 'apply_payroll_deduction', 'params' => ['deduction_amount' => 50000]],
            ],
            'is_active' => true,
        ]);

        // Aturan 3: Terbitkan SP-1 Pelanggaran Siswa
        $ruleEngine->createRule([
            'name' => 'Terbitkan SP-1 Pelanggaran Siswa > 50 Poin',
            'trigger_event' => 'student.point_logged',
            'conditions' => [
                ['field' => 'student_penalty_points', 'operator' => '>', 'value' => 50],
            ],
            'actions' => [
                ['action_type' => 'issue_warning_letter_sp1', 'params' => []],
            ],
            'is_active' => true,
        ]);

        // Execute demo evaluations
        $ruleEngine->evaluateAndExecute('attendance.checked', [
            'attendance_percentage' => 75.0,
            'tahfidz_status' => 'Behind',
        ]);
    }
}
