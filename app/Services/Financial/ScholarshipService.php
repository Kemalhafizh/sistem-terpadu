<?php

namespace App\Services\Financial;

use App\Models\ScholarshipApplication;
use App\Models\ScholarshipDisbursement;
use App\Models\ScholarshipProgram;
use Illuminate\Support\Str;

class ScholarshipService
{
    public function createProgram(array $data, int $userId): ScholarshipProgram
    {
        return ScholarshipProgram::create([
            'name' => $data['name'],
            'type' => $data['type'],
            'source' => $data['source'],
            'academic_year' => $data['academic_year'],
            'total_quota' => $data['total_quota'],
            'amount_per_student' => $data['amount_per_student'],
            'eligibility_criteria' => $data['eligibility_criteria'] ?? [],
            'application_start_date' => $data['application_start_date'],
            'application_end_date' => $data['application_end_date'],
            'status' => 'draft',
            'created_by' => $userId,
        ]);
    }

    public function openApplications(ScholarshipProgram $program): ScholarshipProgram
    {
        $program->update(['status' => 'open']);

        return $program;
    }

    public function submitApplication(ScholarshipProgram $program, int $studentId, array $data): ScholarshipApplication
    {
        return ScholarshipApplication::create([
            'scholarship_program_id' => $program->id,
            'student_id' => $studentId,
            'application_data' => $data['application_data'] ?? [],
            'supporting_documents' => $data['supporting_documents'] ?? [],
            'gpa_score' => $data['gpa_score'] ?? null,
            'family_income' => $data['family_income'] ?? null,
            'recommendation_letter_path' => $data['recommendation_letter_path'] ?? null,
            'status' => 'pending',
        ]);
    }

    public function reviewApplication(ScholarshipApplication $application, int $reviewerId, string $status, ?string $notes = null): ScholarshipApplication
    {
        $application->update([
            'status' => $status,
            'reviewed_by' => $reviewerId,
            'review_notes' => $notes,
        ]);

        return $application;
    }

    public function disburse(ScholarshipApplication $application, string $method, int $officerId): ScholarshipDisbursement
    {
        $program = $application->program;
        $refNo = 'DISB-'.strtoupper(Str::random(6)).'-'.date('Ymd');

        $disbursement = ScholarshipDisbursement::create([
            'scholarship_application_id' => $application->id,
            'amount' => $program->amount_per_student,
            'disbursement_method' => $method,
            'reference_number' => $refNo,
            'disbursed_by' => $officerId,
            'disbursed_at' => now(),
        ]);

        $application->update([
            'status' => 'disbursed',
            'disbursed_at' => now(),
        ]);

        return $disbursement;
    }

    public function getProgramSummary(ScholarshipProgram $program): array
    {
        $totalApps = $program->applications()->count();
        $approvedApps = $program->applications()->where('status', 'approved')->count();
        $disbursedApps = $program->applications()->where('status', 'disbursed')->count();
        $totalDisbursedAmount = $disbursedApps * $program->amount_per_student;

        return [
            'total_quota' => $program->total_quota,
            'total_applications' => $totalApps,
            'approved_count' => $approvedApps,
            'disbursed_count' => $disbursedApps,
            'total_disbursed_amount' => $totalDisbursedAmount,
            'quota_remaining' => max(0, $program->total_quota - $approvedApps - $disbursedApps),
        ];
    }
}
