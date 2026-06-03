<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\Setting;
use Carbon\Carbon;

class PayrollService
{
    /**
     * Calculate effective working days in a month (Monday to Friday).
     */
    public function calculateWorkingDays($month, $year)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        $workingDays = 0;

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if (! $date->isWeekend()) {
                $workingDays++;
            }
        }

        return $workingDays;
    }

    /**
     * Calculate payroll metrics for a single employee in a given month.
     */
    public function calculatePayrollForEmployee(Employee $employee, $month, $year)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $workingDays = $this->calculateWorkingDays($month, $year);

        $presentDays = 0;
        $lateDays = 0;
        $leaveDays = 0;
        $absentDays = 0;

        // Fetch all attendances for this employee in this month
        $attendances = EmployeeAttendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get()
            ->keyBy(function ($item) {
                return $item->date->format('Y-m-d');
            });

        // Fetch all approved leave requests for this employee that overlap with this month
        $approvedLeaves = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->orWhereBetween('end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate->format('Y-m-d'))
                            ->where('end_date', '>=', $endDate->format('Y-m-d'));
                    });
            })
            ->get();

        // Loop through each weekday of the month to count metrics
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if ($date->isWeekend()) {
                continue;
            }

            $dateStr = $date->format('Y-m-d');

            if ($attendances->has($dateStr)) {
                $att = $attendances->get($dateStr);

                if (in_array($att->status, ['hadir', 'terlambat'])) {
                    $presentDays++;
                    if ($att->status === 'terlambat') {
                        $lateDays++;
                    }
                } elseif (in_array($att->status, ['izin', 'sakit', 'cuti'])) {
                    $leaveDays++;
                } elseif ($att->status === 'alfa') {
                    $absentDays++;
                }
            } else {
                // If there's no attendance record, check if there's an approved leave request overlapping this day
                $isOnApprovedLeave = false;
                foreach ($approvedLeaves as $leave) {
                    $leaveStart = Carbon::parse($leave->start_date);
                    $leaveEnd = Carbon::parse($leave->end_date);
                    if ($date->between($leaveStart, $leaveEnd)) {
                        $isOnApprovedLeave = true;
                        break;
                    }
                }

                if ($isOnApprovedLeave) {
                    $leaveDays++;
                } else {
                    $absentDays++;
                }
            }
        }

        // Get deduction rates from settings
        $lateDeductionRate = (float) Setting::get('payroll_late_deduction', 25000);
        $absentDeductionRate = (float) Setting::get('payroll_absent_deduction', 100000);

        $lateDeduction = $lateDays * $lateDeductionRate;
        $absentDeduction = $absentDays * $absentDeductionRate;
        $totalDeductions = $lateDeduction + $absentDeduction;

        $baseSalary = (float) $employee->base_salary;

        // Fetch existing payroll record to retain any manual allowances or additional deductions
        $existingPayroll = Payroll::where('employee_id', $employee->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        $allowances = $existingPayroll ? (float) $existingPayroll->allowances : 0;

        // Net salary calculation
        $netSalary = $baseSalary + $allowances - $totalDeductions;
        if ($netSalary < 0) {
            $netSalary = 0;
        }

        return [
            'employee_id' => $employee->id,
            'month' => $month,
            'year' => $year,
            'base_salary' => $baseSalary,
            'allowances' => $allowances,
            'deductions' => $totalDeductions,
            'net_salary' => $netSalary,
            'working_days' => $workingDays,
            'present_days' => $presentDays,
            'late_days' => $lateDays,
            'absent_days' => $absentDays,
            'leave_days' => $leaveDays,
            'status' => $existingPayroll ? $existingPayroll->status : 'draft',
            'notes' => $existingPayroll ? $existingPayroll->notes : null,
        ];
    }

    /**
     * Generate payroll for all active employees for a given month and year.
     */
    public function generateMonthlyPayroll($month, $year)
    {
        $employees = Employee::all();
        $generatedCount = 0;

        foreach ($employees as $employee) {
            $payrollData = $this->calculatePayrollForEmployee($employee, $month, $year);

            Payroll::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'month' => $month,
                    'year' => $year,
                ],
                $payrollData
            );

            $generatedCount++;
        }

        return $generatedCount;
    }
}
