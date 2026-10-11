<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Setting;
use App\Services\HRD\EmployeeAttendanceRecap;

/**
 * Monthly payroll from base salary, the allowances and other deductions TU
 * types in, and the attendance recap.
 *
 * Attendance comes from EmployeeAttendanceRecap: only days the attendance data
 * covers count, so holidays and days before the fingerprint log was imported
 * are never charged as absences, and an approved leave request is never an
 * absence. A month without attendance data deducts nothing for attendance and
 * stores the attendance figures as null.
 */
class PayrollService
{
    public const LATE_RATE = 'payroll_late_deduction';

    public const ABSENT_RATE = 'payroll_absent_deduction';

    public function __construct(private EmployeeAttendanceRecap $recap) {}

    /**
     * The school's deduction per late day and per absent day, as set on the
     * payroll page. Null when not set: nothing is deducted for it.
     *
     * @return array{late: ?float, absent: ?float}
     */
    public function rates(): array
    {
        $rate = fn (string $key) => is_numeric($value = Setting::get($key)) ? (float) $value : null;

        return ['late' => $rate(self::LATE_RATE), 'absent' => $rate(self::ABSENT_RATE)];
    }

    public function saveRates(?float $late, ?float $absent): void
    {
        Setting::set(self::LATE_RATE, $late === null ? null : (string) $late, 'payroll');
        Setting::set(self::ABSENT_RATE, $absent === null ? null : (string) $absent, 'payroll');
    }

    /**
     * Calculate payroll metrics for a single employee in a given month.
     *
     * @return array<string, mixed>
     */
    public function calculatePayrollForEmployee(Employee $employee, string $month, string $year): array
    {
        $attendance = $this->recap->forEmployee($employee, (int) $year, (int) $month);
        $rates = $this->rates();

        $hasData = $attendance['working_days'] !== null;
        $attendanceDeductions = $hasData
            ? $attendance['terlambat'] * ($rates['late'] ?? 0) + $attendance['tidak_hadir'] * ($rates['absent'] ?? 0)
            : 0.0;

        // Keep what TU typed in when the month is generated again.
        $existing = Payroll::where('employee_id', $employee->id)->where('month', $month)->where('year', $year)->first();
        $allowances = (float) ($existing->allowances ?? 0);
        $otherDeductions = (float) ($existing->other_deductions ?? 0);
        $baseSalary = (float) $employee->base_salary;

        return [
            'employee_id' => $employee->id,
            'month' => $month,
            'year' => $year,
            'base_salary' => $baseSalary,
            'allowances' => $allowances,
            'attendance_deductions' => $attendanceDeductions,
            'other_deductions' => $otherDeductions,
            'deductions' => $attendanceDeductions + $otherDeductions,
            'net_salary' => max(0, $baseSalary + $allowances - $attendanceDeductions - $otherDeductions),
            'working_days' => $attendance['working_days'],
            'present_days' => $hasData ? $attendance['hadir'] + $attendance['terlambat'] : null,
            'late_days' => $attendance['terlambat'],
            'absent_days' => $attendance['tidak_hadir'],
            'leave_days' => $hasData ? $attendance['izin'] + $attendance['sakit'] + $attendance['cuti'] : null,
            'status' => $existing->status ?? 'draft',
            'notes' => $existing->notes ?? null,
        ];
    }

    /**
     * Generate (or recalculate) the month's draft slips. Slips already
     * approved or paid are never touched: they were issued to the employee.
     *
     * @return array{generated: int, locked: int, has_attendance: bool}
     */
    public function generateMonthlyPayroll(string $month, string $year): array
    {
        $locked = Payroll::where('month', $month)->where('year', $year)->where('status', '!=', 'draft')->pluck('employee_id');
        $generated = 0;
        $hasAttendance = false;

        foreach (Employee::whereNotIn('id', $locked)->get() as $employee) {
            $data = $this->calculatePayrollForEmployee($employee, $month, $year);
            $hasAttendance = $hasAttendance || $data['working_days'] !== null;

            Payroll::updateOrCreate(['employee_id' => $employee->id, 'month' => $month, 'year' => $year], $data);
            $generated++;
        }

        return ['generated' => $generated, 'locked' => $locked->count(), 'has_attendance' => $hasAttendance];
    }

    /** Recompute a slip's total after TU edits allowances or other deductions. */
    public function applyAdjustments(Payroll $payroll, float $allowances, float $otherDeductions, ?string $notes): void
    {
        $attendance = (float) $payroll->attendance_deductions;

        $payroll->update([
            'allowances' => $allowances,
            'other_deductions' => $otherDeductions,
            'deductions' => $attendance + $otherDeductions,
            'net_salary' => max(0, (float) $payroll->base_salary + $allowances - $attendance - $otherDeductions),
            'notes' => $notes,
        ]);
    }
}
