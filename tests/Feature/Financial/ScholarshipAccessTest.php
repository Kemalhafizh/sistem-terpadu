<?php

use App\Models\User;
use App\Services\Financial\ScholarshipService;

/*
 * FASE 119: scholarships joined the Keuangan menu. Their routes used to carry
 * no role at all, so any signed-in account (a student included) could create
 * programmes, approve applications and pay them out.
 */

function scholarshipApplication(): array
{
    $service = app(ScholarshipService::class);
    $staff = User::factory()->create(['role' => 'staf_tu']);
    $program = $service->createProgram([
        'name' => 'Beasiswa Prestasi', 'type' => 'internal_need', 'source' => 'yayasan', 'academic_year' => '2026/2027',
        'total_quota' => 5, 'amount_per_student' => 1500000,
        'application_start_date' => now()->toDateString(), 'application_end_date' => now()->addWeek()->toDateString(),
    ], $staff->id);
    $service->openApplications($program);
    $application = $service->submitApplication($program, User::factory()->create(['role' => 'siswa'])->id, ['gpa_score' => 3.8]);

    return [$program, $application];
}

test('siswa, orang tua, guru, dan BK tidak bisa membuka atau mengubah beasiswa', function (string $role) {
    [$program, $application] = scholarshipApplication();
    $this->actingAs(User::factory()->create(['role' => $role]));

    $this->get(route('financial.scholarships.programs'))->assertForbidden();
    $this->get(route('financial.scholarships.report'))->assertForbidden();
    $this->post(route('financial.scholarships.review_action', $application->id), ['status' => 'approved'])->assertForbidden();
    $this->post(route('financial.scholarships.disburse_action', $application->id), ['disbursement_method' => 'bank_transfer'])->assertForbidden();
    $this->post(route('financial.scholarships.submit_application', $program->id), ['student_id' => auth()->id()])->assertForbidden();

    expect($application->fresh()->status)->toBe('pending');
})->with(['siswa', 'student', 'orang_tua', 'guru', 'bk']);

test('kepala sekolah melihat dan menyetujui, tapi tidak menyalurkan dana', function () {
    [, $application] = scholarshipApplication();
    $this->actingAs(User::factory()->create(['role' => 'kepala_sekolah']));

    $this->get(route('financial.scholarships.programs'))->assertOk();
    $this->get(route('financial.scholarships.create_program'))->assertForbidden();
    $this->post(route('financial.scholarships.disburse_action', $application->id), ['disbursement_method' => 'bank_transfer'])->assertForbidden();
    $this->post(route('financial.scholarships.review_action', $application->id), ['status' => 'approved'])->assertRedirect();

    expect($application->fresh()->status)->toBe('approved');
});

test('TU membuat program dan menyalurkan dana', function () {
    [, $application] = scholarshipApplication();
    $application->update(['status' => 'approved']);
    $this->actingAs(User::factory()->create(['role' => 'staf_tu']));

    $this->get(route('financial.scholarships.create_program'))->assertOk();
    $this->post(route('financial.scholarships.disburse_action', $application->id), ['disbursement_method' => 'bank_transfer'])->assertRedirect();

    expect($application->fresh()->status)->toBe('disbursed');
});
