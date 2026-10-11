<?php

use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\User;
use App\Services\PayrollService;

/*
 * Payroll reads attendance from EmployeeAttendanceRecap. It used to count
 * every weekday of the month and charge each one without a check-in as an
 * absence (Rp 100.000 by default), so a national holiday, the days before the
 * fingerprint log was imported, or a whole month without data cut a full
 * salary. Approved leave never existed either: nobody could approve a request.
 */

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->service = app(PayrollService::class);
    $this->service->saveRates(25000, 100000);

    $this->employee = payrollEmployee('Guru Kimia', 3000000);
    // A colleague whose scans show which days the school was open.
    $this->colleague = payrollEmployee('Staf TU', 2500000);
});

function payrollEmployee(string $position, float $salary, ?string $hired = '2026-01-01'): Employee
{
    return Employee::create([
        'user_id' => User::factory()->create(['role' => 'guru'])->id,
        'position' => $position,
        'employment_status' => 'gty',
        'hire_date' => $hired,
        'base_salary' => $salary,
    ]);
}

function scan(Employee $employee, string $date, string $status = 'hadir'): void
{
    EmployeeAttendance::create([
        'employee_id' => $employee->id,
        'date' => $date,
        'check_in_time' => $status === 'terlambat' ? '07:20:00' : '06:50:00',
        'status' => $status,
        'source' => 'fingerprint',
    ]);
}

/** Mon 4 – Fri 15 May 2026: ten school days. 1 May (Hari Buruh) is a holiday nobody scans on. */
function mayWorkingDays(): array
{
    return ['2026-05-04', '2026-05-05', '2026-05-06', '2026-05-07', '2026-05-08', '2026-05-11', '2026-05-12', '2026-05-13', '2026-05-14', '2026-05-15'];
}

test('hari libur dan hari tanpa data presensi bukan alfa', function () {
    foreach (mayWorkingDays() as $i => $date) {
        scan($this->colleague, $date);
        if ($i < 8) {
            scan($this->employee, $date);
        } elseif ($i === 8) {
            scan($this->employee, $date, 'terlambat');
        }
        // The tenth day: no scan, no leave.
    }

    $result = $this->service->calculatePayrollForEmployee($this->employee, '05', '2026');

    // Used to be 21 weekdays with 12 "absences" (1 May and 18–29 May included): Rp 1.225.000 off.
    expect($result)->toMatchArray([
        'working_days' => 10, 'present_days' => 9, 'late_days' => 1, 'absent_days' => 1, 'leave_days' => 0,
    ])
        ->and((float) $result['attendance_deductions'])->toBe(125000.0)
        ->and((float) $result['net_salary'])->toBe(2875000.0);
});

test('pengajuan cuti yang disetujui bukan alfa, yang belum disetujui tetap alfa', function () {
    foreach (mayWorkingDays() as $date) {
        scan($this->colleague, $date);
    }
    foreach (array_slice(mayWorkingDays(), 0, 6) as $date) {
        scan($this->employee, $date);
    }
    LeaveRequest::create(['employee_id' => $this->employee->id, 'start_date' => '2026-05-12', 'end_date' => '2026-05-13', 'type' => 'sakit', 'reason' => 'Demam', 'status' => 'approved']);
    LeaveRequest::create(['employee_id' => $this->employee->id, 'start_date' => '2026-05-14', 'end_date' => '2026-05-15', 'type' => 'izin', 'reason' => 'Keluarga', 'status' => 'pending']);

    $result = $this->service->calculatePayrollForEmployee($this->employee, '05', '2026');

    expect($result)->toMatchArray(['working_days' => 10, 'present_days' => 6, 'leave_days' => 2, 'absent_days' => 2])
        ->and((float) $result['attendance_deductions'])->toBe(200000.0);
});

test('hari sebelum tanggal mulai kerja tidak dihitung', function () {
    $newcomer = payrollEmployee('Guru Baru', 2000000, '2026-05-11');
    foreach (mayWorkingDays() as $date) {
        scan($this->colleague, $date);
    }
    foreach (array_slice(mayWorkingDays(), 5) as $date) {
        scan($newcomer, $date);
    }

    $result = $this->service->calculatePayrollForEmployee($newcomer, '05', '2026');

    expect($result)->toMatchArray(['working_days' => 5, 'present_days' => 5, 'absent_days' => 0])
        ->and((float) $result['attendance_deductions'])->toBe(0.0);
});

test('scan di hari Sabtu tidak menjadikan Sabtu hari kerja bagi pegawai lain', function () {
    scan($this->colleague, '2026-05-04');
    scan($this->employee, '2026-05-04');
    scan($this->colleague, '2026-05-09'); // Saturday school event

    $result = $this->service->calculatePayrollForEmployee($this->employee, '05', '2026');

    expect($result)->toMatchArray(['working_days' => 1, 'present_days' => 1, 'absent_days' => 0]);
});

test('bulan tanpa data presensi: angka kehadiran null dan tanpa potongan kehadiran', function () {
    $response = $this->actingAs($this->admin)->post(route('hrd.payrolls.generate'), ['month' => '06', 'year' => '2026']);

    $response->assertRedirect()->assertSessionHas('warning');
    $payroll = Payroll::where('employee_id', $this->employee->id)->where('month', '06')->firstOrFail();

    // Used to be 22 weekdays, all "alfa": Rp 2.200.000 off a Rp 3.000.000 salary.
    expect($payroll->working_days)->toBeNull()
        ->and($payroll->absent_days)->toBeNull()
        ->and($payroll->present_days)->toBeNull()
        ->and((float) $payroll->attendance_deductions)->toBe(0.0)
        ->and((float) $payroll->net_salary)->toBe(3000000.0);

    $this->actingAs($this->admin)->get(route('hrd.payrolls.show', $payroll))
        ->assertOk()
        ->assertSee('Belum ada data presensi saat slip dihitung');
});

test('tarif potongan yang belum diatur tidak memotong apa pun', function () {
    $this->service->saveRates(null, null);
    scan($this->colleague, '2026-05-04');

    $result = $this->service->calculatePayrollForEmployee($this->employee, '05', '2026');

    expect($result['absent_days'])->toBe(1)
        ->and((float) $result['attendance_deductions'])->toBe(0.0);

    $this->actingAs($this->admin)->get(route('hrd.payrolls.create'))
        ->assertOk()
        ->assertSee('Belum ada potongan (tarif belum diatur).')
        ->assertDontSee('Rp 25.000')
        ->assertDontSee('07:15');
});

test('halaman payroll menampilkan aturan yang benar-benar dipakai, dan tarif bisa diubah', function () {
    $this->actingAs($this->admin)->get(route('hrd.payrolls.create'))
        ->assertOk()
        ->assertSee('Rp 25.000')
        ->assertSee('Rp 100.000')
        ->assertSee('07:00');

    $this->actingAs($this->admin)->post(route('hrd.payrolls.rules'), ['late_deduction' => 10000, 'absent_deduction' => ''])
        ->assertRedirect(route('hrd.payrolls.create'));

    expect($this->service->rates())->toBe(['late' => 10000.0, 'absent' => null]);
});

test('hitung ulang mempertahankan tunjangan dan potongan lain, dan tidak menyentuh slip yang sudah terbit', function () {
    foreach (mayWorkingDays() as $date) {
        scan($this->colleague, $date);
        scan($this->employee, $date);
    }
    $this->actingAs($this->admin)->post(route('hrd.payrolls.generate'), ['month' => '05', 'year' => '2026']);
    $mine = Payroll::where('employee_id', $this->employee->id)->firstOrFail();
    $theirs = Payroll::where('employee_id', $this->colleague->id)->firstOrFail();

    $this->actingAs($this->admin)->put(route('hrd.payrolls.update', $mine), ['allowances' => 500000, 'other_deductions' => 150000, 'notes' => 'Kasbon'])->assertRedirect();
    $this->actingAs($this->admin)->post(route('hrd.payrolls.approve', $theirs))->assertRedirect();
    $theirs->update(['net_salary' => 1234567]); // what was issued stays what was issued

    // A late import adds an absence for both.
    EmployeeAttendance::where('employee_id', $this->employee->id)->whereDate('date', '2026-05-15')->delete();
    EmployeeAttendance::where('employee_id', $this->colleague->id)->whereDate('date', '2026-05-15')->delete();
    scan(payrollEmployee('Satpam', 2000000), '2026-05-15');

    $this->actingAs($this->admin)->post(route('hrd.payrolls.generate'), ['month' => '05', 'year' => '2026'])
        ->assertSessionHas('success', fn (string $message) => str_contains($message, '1 slip yang sudah diterbitkan'));

    $mine->refresh();
    expect((float) $mine->allowances)->toBe(500000.0)
        ->and((float) $mine->other_deductions)->toBe(150000.0)
        ->and((float) $mine->attendance_deductions)->toBe(100000.0)
        ->and((float) $mine->deductions)->toBe(250000.0)
        ->and((float) $mine->net_salary)->toBe(3250000.0)
        ->and($mine->notes)->toBe('Kasbon')
        ->and((float) $theirs->fresh()->net_salary)->toBe(1234567.0);
});

test('penyesuaian slip menghitung ulang gaji bersih, slip yang sudah dibayar terkunci', function () {
    $payroll = Payroll::create([
        'employee_id' => $this->employee->id, 'month' => '05', 'year' => '2026', 'base_salary' => 3000000,
        'attendance_deductions' => 50000, 'deductions' => 50000, 'net_salary' => 2950000, 'status' => 'draft',
    ]);

    $this->actingAs($this->admin)->put(route('hrd.payrolls.update', $payroll), ['allowances' => 500000, 'other_deductions' => 100000, 'notes' => 'Tunjangan kinerja'])
        ->assertRedirect()->assertSessionHas('success');

    $payroll->refresh();
    expect((float) $payroll->deductions)->toBe(150000.0)
        ->and((float) $payroll->net_salary)->toBe(3350000.0);

    $payroll->update(['status' => 'paid']);
    $this->actingAs($this->admin)->put(route('hrd.payrolls.update', $payroll), ['allowances' => 0, 'other_deductions' => 0])
        ->assertSessionHas('error');
    expect((float) $payroll->fresh()->net_salary)->toBe(3350000.0);
});

test('tombol terbitkan dan bayar di satu slip hanya mengubah slip itu', function () {
    $this->actingAs($this->admin)->post(route('hrd.payrolls.generate'), ['month' => '05', 'year' => '2026']);
    $mine = Payroll::where('employee_id', $this->employee->id)->firstOrFail();
    $theirs = Payroll::where('employee_id', $this->colleague->id)->firstOrFail();

    // These used to post the bulk routes and issue/pay every slip of the month.
    $this->actingAs($this->admin)->post(route('hrd.payrolls.approve', $mine))->assertRedirect(route('hrd.payrolls.show', $mine));
    expect($mine->fresh()->status)->toBe('approved')->and($theirs->fresh()->status)->toBe('draft');

    $this->actingAs($this->admin)->post(route('hrd.payrolls.pay', $mine))->assertRedirect();
    expect($mine->fresh()->status)->toBe('paid')->and($mine->fresh()->payment_date)->not->toBeNull()
        ->and($theirs->fresh()->status)->toBe('draft');

    // A draft cannot be paid directly.
    $this->actingAs($this->admin)->post(route('hrd.payrolls.pay', $theirs))->assertSessionHas('error');
    expect($theirs->fresh()->status)->toBe('draft');
});

test('terbitkan dan bayar massal per bulan', function () {
    $this->actingAs($this->admin)->post(route('hrd.payrolls.generate'), ['month' => '05', 'year' => '2026'])->assertRedirect();
    $this->actingAs($this->admin)->post(route('hrd.payrolls.approve-bulk'), ['month' => '05', 'year' => '2026'])->assertRedirect();
    expect(Payroll::where('status', 'approved')->count())->toBe(2);

    $this->actingAs($this->admin)->post(route('hrd.payrolls.pay-bulk'), ['month' => '05', 'year' => '2026'])->assertRedirect();
    expect(Payroll::where('status', 'paid')->count())->toBe(2);
});

test('guru dan staf TU tidak bisa menghitung, menerbitkan, atau mengubah tarif payroll', function (string $role) {
    $this->actingAs(User::factory()->create(['role' => $role]));
    $payroll = Payroll::create(['employee_id' => $this->employee->id, 'month' => '05', 'year' => '2026', 'base_salary' => 3000000, 'net_salary' => 3000000]);

    $this->post(route('hrd.payrolls.generate'), ['month' => '05', 'year' => '2026'])->assertForbidden();
    $this->post(route('hrd.payrolls.rules'), ['late_deduction' => 0])->assertForbidden();
    $this->post(route('hrd.payrolls.approve', $payroll))->assertForbidden();
    $this->post(route('hrd.payrolls.pay', $payroll))->assertForbidden();

    expect($payroll->fresh()->status)->toBe('draft')
        ->and($this->service->rates()['late'])->toBe(25000.0);
})->with(['guru', 'staf_tu', 'siswa']);
