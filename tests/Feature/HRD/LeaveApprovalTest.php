<?php

use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowRequest;
use App\Services\PayrollService;
use Database\Seeders\WorkflowDefinitionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/*
 * A leave request used to be stored as "pending" and stay there: nothing
 * started its approval flow, so no one could approve it, and payroll charged
 * the days as absences. It now enters the leave_approval workflow (Waka
 * Kurikulum, then Kepala Sekolah) and the last approval marks it approved.
 */

beforeEach(function () {
    $this->seed(WorkflowDefinitionSeeder::class);
    Storage::fake('public');

    $this->teacher = User::factory()->create(['role' => 'guru']);
    $this->employee = Employee::create(['user_id' => $this->teacher->id, 'position' => 'Guru Biologi', 'hire_date' => '2026-01-01', 'base_salary' => 3000000]);
    $this->waka = User::factory()->create(['role' => 'waka_kurikulum']);
    $this->principal = User::factory()->create(['role' => 'kepala_sekolah']);
});

function submitLeave(object $test, array $overrides = [])
{
    return $test->actingAs($test->teacher)->post(route('my-hrd.leaves'), [
        'type' => 'sakit',
        'start_date' => now()->addDay()->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
        'reason' => 'Demam tinggi',
        ...$overrides,
    ]);
}

test('pengajuan cuti masuk alur persetujuan Waka lalu Kepala Sekolah', function () {
    submitLeave($this, ['attachment' => UploadedFile::fake()->create('surat-dokter.pdf', 50, 'application/pdf')])
        ->assertRedirect()->assertSessionHas('success');

    $leave = LeaveRequest::sole();
    $workflow = WorkflowRequest::sole();
    expect($leave->status)->toBe('pending')
        ->and($workflow->requestable->is($leave))->toBeTrue()
        ->and($workflow->approvals->pluck('role')->all())->toBe(['waka_kurikulum', 'kepala_sekolah']);
    Storage::disk('public')->assertExists($leave->attachment);

    // The employee sees who it is waiting on.
    $this->actingAs($this->teacher)->get(route('my-hrd.portal'))->assertOk()->assertSee('Menunggu Waka Kurikulum');

    // The approver sees the real type and the attachment, not a made-up "Cuti Umum".
    $this->actingAs($this->waka)->get(route('workflows.show', $workflow))
        ->assertOk()->assertSee('Sakit')->assertDontSee('Cuti Umum')->assertSee('Lihat lampiran');

    // The principal cannot skip the first step.
    $this->actingAs($this->principal)->post(route('workflows.approve', $workflow))->assertForbidden();

    $this->actingAs($this->waka)->post(route('workflows.approve', $workflow))->assertRedirect();
    expect($leave->fresh()->status)->toBe('pending');
    $this->actingAs($this->teacher)->get(route('my-hrd.portal'))->assertSee('Menunggu Kepala Sekolah');

    $this->actingAs($this->principal)->post(route('workflows.approve', $workflow->fresh()))->assertRedirect();
    expect($leave->fresh())->status->toBe('approved')->approved_by->toBe($this->principal->id);
});

test('cuti yang disetujui lewat alur tidak dipotong di payroll', function () {
    app(PayrollService::class)->saveRates(0, 100000);
    $colleague = Employee::create(['user_id' => User::factory()->create(['role' => 'guru'])->id, 'position' => 'Staf', 'hire_date' => '2026-01-01']);
    $tomorrow = now()->addDay();
    foreach ([$tomorrow, $tomorrow->copy()->addDay()] as $day) {
        EmployeeAttendance::create(['employee_id' => $colleague->id, 'date' => $day->toDateString(), 'status' => 'hadir']);
    }
    config(['hrd.work_days' => [1, 2, 3, 4, 5, 6, 7]]);

    submitLeave($this);
    $workflow = WorkflowRequest::sole();
    $this->actingAs($this->waka)->post(route('workflows.approve', $workflow));
    $this->actingAs($this->principal)->post(route('workflows.approve', $workflow->fresh()));

    $months = collect([$tomorrow, $tomorrow->copy()->addDay()])->groupBy(fn ($day) => $day->format('Y-m'));
    foreach ($months as $days) {
        $result = app(PayrollService::class)->calculatePayrollForEmployee($this->employee, $days[0]->format('m'), $days[0]->format('Y'));
        expect($result['leave_days'])->toBe($days->count())
            ->and($result['absent_days'])->toBe(0)
            ->and((float) $result['attendance_deductions'])->toBe(0.0);
    }
});

test('pengajuan yang ditolak berstatus ditolak', function () {
    submitLeave($this);
    $workflow = WorkflowRequest::sole();

    $this->actingAs($this->waka)->post(route('workflows.reject', $workflow), ['remarks' => 'Bentrok jadwal ujian'])->assertRedirect();

    expect(LeaveRequest::sole()->status)->toBe('rejected');
    $this->actingAs($this->teacher)->get(route('my-hrd.portal'))->assertSee('Ditolak');
});

test('tanpa alur persetujuan aktif, pengajuan tidak disimpan dan pegawai diberi tahu', function () {
    WorkflowDefinition::where('code', LeaveRequest::WORKFLOW)->update(['is_active' => false]);

    submitLeave($this)->assertRedirect()->assertSessionHas('error', 'Alur persetujuan cuti belum diatur. Hubungi Tata Usaha.');

    expect(LeaveRequest::count())->toBe(0)->and(WorkflowRequest::count())->toBe(0);
});

test('akun yang bukan pegawai tidak bisa mengajukan cuti', function () {
    $this->actingAs(User::factory()->create(['role' => 'guru']))
        ->post(route('my-hrd.leaves'), ['type' => 'izin', 'start_date' => now()->addDay()->toDateString(), 'end_date' => now()->addDay()->toDateString(), 'reason' => 'x'])
        ->assertSessionHas('error');

    expect(LeaveRequest::count())->toBe(0);
});

test('jenis cuti di luar daftar ditolak', function () {
    submitLeave($this, ['type' => 'liburan'])->assertSessionHasErrors('type');

    expect(LeaveRequest::count())->toBe(0);
});
