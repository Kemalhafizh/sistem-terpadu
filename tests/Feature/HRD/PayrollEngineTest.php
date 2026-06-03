<?php

use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\Setting;
use App\Models\User;
use App\Services\PayrollService;
use Illuminate\Support\Carbon;

beforeEach(function () {
    // Seed rates
    Setting::updateOrCreate(['key' => 'payroll_late_deduction'], ['value' => '25000', 'group' => 'system', 'type' => 'text']);
    Setting::updateOrCreate(['key' => 'payroll_absent_deduction'], ['value' => '100000', 'group' => 'system', 'type' => 'text']);

    // Admin user for controller level payroll actions
    $this->admin = User::factory()->create(['role' => 'admin']);

    // Standard employee
    $this->user = User::factory()->create(['role' => 'guru']);
    $this->employee = Employee::create([
        'user_id' => $this->user->id,
        'employee_number' => 'GUR-2026-9999',
        'position' => 'Guru Kimia',
        'employment_status' => 'gty',
        'hire_date' => '2026-01-01',
        'base_salary' => 3000000.00, // Rp 3,000,000 base
    ]);

    $this->payrollService = app(PayrollService::class);
});

test('payroll engine calculates correct working days (weekdays only) in a month', function () {
    // May 2026 has 31 days. Weekends are May 2-3, 9-10, 16-17, 23-24, 30-31 (10 weekend days).
    // Total weekdays = 21.
    $workingDays = $this->payrollService->calculateWorkingDays('05', '2026');
    expect($workingDays)->toBe(21);
});

test('payroll calculation applies correct deductions for late arrivals and absences', function () {
    $month = '05';
    $year = '2026';
    $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();

    // Out of 21 weekdays in May 2026:
    // - 10 days present ('hadir')
    // - 2 days late ('terlambat')
    // - Remaining 9 days are absences (alfa - no record in DB)

    // Seed 10 'hadir' days and 2 'terlambat' days sequentially on weekdays
    $date = $startDate->copy();
    $presentCount = 0;
    $lateCount = 0;

    while ($presentCount < 10 || $lateCount < 2) {
        if (! $date->isWeekend()) {
            if ($presentCount < 10) {
                EmployeeAttendance::create([
                    'employee_id' => $this->employee->id,
                    'date' => $date->format('Y-m-d'),
                    'check_in_time' => '07:00:00',
                    'status' => 'hadir',
                ]);
                $presentCount++;
            } else {
                EmployeeAttendance::create([
                    'employee_id' => $this->employee->id,
                    'date' => $date->format('Y-m-d'),
                    'check_in_time' => '07:20:00',
                    'status' => 'terlambat',
                ]);
                $lateCount++;
            }
        }
        $date->addDay();
    }

    // Calculate payroll using service
    $result = $this->payrollService->calculatePayrollForEmployee($this->employee, $month, $year);

    expect($result['working_days'])->toBe(21);
    expect($result['present_days'])->toBe(12); // 10 hadir + 2 terlambat
    expect($result['late_days'])->toBe(2);
    expect($result['absent_days'])->toBe(9); // 21 working days - 12 present = 9 alfa
    expect($result['leave_days'])->toBe(0);

    // Deductions calculation:
    // Late: 2 * Rp 25,000 = Rp 50,000
    // Absent: 9 * Rp 100,000 = Rp 900,000
    // Total Deductions = Rp 950,000
    expect((int) $result['deductions'])->toBe(950000);

    // Net Salary: 3,000,000 (base) - 950,000 (deductions) = 2,050,000
    expect((int) $result['net_salary'])->toBe(2050000);
});

test('approved leave requests are exempted from absence deductions', function () {
    $month = '05';
    $year = '2026';

    // Out of 21 weekdays:
    // - 10 days present ('hadir')
    // - 2 days late ('terlambat')
    // - 4 days on approved leave (seeded overlapping weekday range)
    // - Remaining 5 days are absences (alfa)

    // Seed 10 'hadir' days and 2 'terlambat' days sequentially on weekdays, avoiding the leave period (May 25-28)
    $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
    $date = $startDate->copy();
    $presentCount = 0;
    $lateCount = 0;

    while ($presentCount < 10 || $lateCount < 2) {
        if (! $date->isWeekend() && ! $date->between(Carbon::parse('2026-05-25'), Carbon::parse('2026-05-28'))) {
            if ($presentCount < 10) {
                EmployeeAttendance::create([
                    'employee_id' => $this->employee->id,
                    'date' => $date->format('Y-m-d'),
                    'check_in_time' => '07:00:00',
                    'status' => 'hadir',
                ]);
                $presentCount++;
            } else {
                EmployeeAttendance::create([
                    'employee_id' => $this->employee->id,
                    'date' => $date->format('Y-m-d'),
                    'check_in_time' => '07:20:00',
                    'status' => 'terlambat',
                ]);
                $lateCount++;
            }
        }
        $date->addDay();
    }

    // Seed approved LeaveRequest for 4 weekdays (e.g., May 25, 2026 to May 28, 2026)
    // Let's verify May 25, 26, 27, 28 are weekdays (Mon, Tue, Wed, Thu)
    LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'start_date' => '2026-05-25',
        'end_date' => '2026-05-28',
        'type' => 'izin',
        'reason' => 'Keperluan keluarga penting',
        'status' => 'approved',
    ]);

    // Calculate payroll using service
    $result = $this->payrollService->calculatePayrollForEmployee($this->employee, $month, $year);

    expect($result['working_days'])->toBe(21);
    expect($result['present_days'])->toBe(12); // 10 hadir + 2 terlambat
    expect($result['late_days'])->toBe(2);
    expect($result['leave_days'])->toBe(4); // 4 days of approved leave
    expect($result['absent_days'])->toBe(5); // 21 - 12 - 4 = 5 absent days

    // Deductions calculation:
    // Late: 2 * Rp 25,000 = Rp 50,000
    // Absent: 5 * Rp 100,000 = Rp 500,000
    // Total Deductions = Rp 550,000
    expect((int) $result['deductions'])->toBe(550000);

    // Net Salary: 3,000,000 - 550,000 = 2,450,000
    expect((int) $result['net_salary'])->toBe(2450000);
});

test('admin can manually adjust allowances and deductions for employee payroll', function () {
    // Generate initial payroll draft in DB
    $payroll = Payroll::create([
        'employee_id' => $this->employee->id,
        'month' => '05',
        'year' => '2026',
        'base_salary' => 3000000.00,
        'allowances' => 0.00,
        'deductions' => 50000.00, // automatic late deduction
        'net_salary' => 2950000.00,
        'working_days' => 21,
        'present_days' => 20,
        'late_days' => 2,
        'status' => 'draft',
    ]);

    // Admin updates allowances, deductions, and notes
    $response = $this->actingAs($this->admin)->put(route('hrd.payrolls.update', $payroll->id), [
        'allowances' => 500000, // Rp 500,000 allowance
        'deductions' => 150000, // Rp 150,000 (automatic + manual kasbon)
        'notes' => 'Tunjangan kinerja + Potongan kasbon Rp 100rb',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHas('success');

    $payroll->refresh();
    expect((int) $payroll->allowances)->toBe(500000);
    expect((int) $payroll->deductions)->toBe(150000);

    // Net salary = 3,000,000 + 500,000 - 150,000 = 3,350,000
    expect((int) $payroll->net_salary)->toBe(3350000);
    expect($payroll->notes)->toBe('Tunjangan kinerja + Potongan kasbon Rp 100rb');
});

test('admin can mass generate, mass approve, and mass pay payrolls', function () {
    $month = '05';
    $year = '2026';

    // 1. Mass Generate Payrolls via POST route
    $response = $this->actingAs($this->admin)->post(route('hrd.payrolls.generate'), [
        'month' => $month,
        'year' => $year,
    ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('payrolls', [
        'employee_id' => $this->employee->id,
        'month' => $month,
        'year' => $year,
        'status' => 'draft',
    ]);

    // 2. Bulk Approve Payrolls
    $response = $this->actingAs($this->admin)->post(route('hrd.payrolls.approve-bulk'), [
        'month' => $month,
        'year' => $year,
    ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('payrolls', [
        'employee_id' => $this->employee->id,
        'month' => $month,
        'year' => $year,
        'status' => 'approved',
    ]);

    // 3. Bulk Pay Payrolls
    $response = $this->actingAs($this->admin)->post(route('hrd.payrolls.pay-bulk'), [
        'month' => $month,
        'year' => $year,
    ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('payrolls', [
        'employee_id' => $this->employee->id,
        'month' => $month,
        'year' => $year,
        'status' => 'paid',
    ]);
});
