<?php

namespace Database\Seeders;

use App\Models\BenefitClaim;
use App\Models\CompensationStructure;
use App\Models\Employee;
use App\Models\ExitInterview;
use App\Models\JobApplicant;
use App\Models\JobVacancy;
use App\Models\TurnoverAnalytic;
use App\Models\User;
use App\Models\WellnessLog;
use App\Models\WellnessProgram;
use Illuminate\Database\Seeder;

class AdvancedHrSeeder extends Seeder
{
    public function run()
    {
        $user = User::first() ?? User::factory()->create();
        $employee = Employee::first() ?? Employee::create(['user_id' => $user->id, 'employee_id' => 'EMP-001', 'position' => 'Teacher', 'department' => 'Academic', 'status' => 'active']);

        $vacancy = JobVacancy::create([
            'position_title' => 'Guru Matematika',
            'department' => 'Akademik',
            'requirements' => 'S1 Matematika, berpengalaman 2 tahun',
            'status' => 'open',
            'closing_date' => now()->addDays(30),
            'created_by' => $user->id,
        ]);

        JobApplicant::create([
            'job_vacancy_id' => $vacancy->id,
            'full_name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'pipeline_stage' => 'applied',
        ]);

        CompensationStructure::create([
            'employee_id' => $employee->id,
            'base_salary' => 5000000.00,
            'allowances' => ['transport' => 500000],
            'deductions' => ['tax' => 250000],
            'effective_date' => now(),
        ]);

        BenefitClaim::create([
            'employee_id' => $employee->id,
            'claim_type' => 'medical',
            'amount' => 1000000.00,
            'description' => 'Kacamata',
            'status' => 'pending',
        ]);

        $program = WellnessProgram::create([
            'program_name' => 'Senam Pagi Bersama',
            'category' => 'physical_health',
            'description' => 'Senam pagi setiap jumat',
            'points_reward' => 10,
            'is_active' => true,
        ]);

        WellnessLog::create([
            'employee_id' => $employee->id,
            'wellness_program_id' => $program->id,
            'participation_date' => now(),
            'feedback_note' => 'Sangat menyegarkan',
        ]);

        ExitInterview::create([
            'employee_id' => $employee->id,
            'interviewer_id' => $user->id,
            'interview_date' => now()->addDays(10),
            'reasons_for_leaving' => ['career_change' => true],
            'status' => 'scheduled',
        ]);

        TurnoverAnalytic::create([
            'department' => 'Akademik',
            'year' => date('Y'),
            'month' => date('n'),
            'headcount_start' => 50,
            'terminations' => 1,
            'turnover_rate' => 2.00,
        ]);
    }
}
