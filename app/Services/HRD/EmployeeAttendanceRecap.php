<?php

namespace App\Services\HRD;

use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\LeaveRequest;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Monthly staff attendance per employee, for the HRD report and payroll
 * (FASE 119.6.1).
 *
 * A working day is a day the school works (config hrd.work_days) that the
 * attendance data covers: someone scanned in or was recorded on leave. So a
 * holiday, where nobody scans, is not a day anyone missed, and neither is a
 * day before the log was imported. A month with no data at all has no
 * working days, and every figure is null, not 0.
 *
 * For each employee, a working day without a scan is leave when an approved
 * leave request covers it, and absent otherwise. Days before the employee's
 * hire date do not count.
 */
class EmployeeAttendanceRecap
{
    private const EMPTY = ['working_days' => null, 'hadir' => null, 'terlambat' => null, 'izin' => null, 'sakit' => null, 'cuti' => null, 'tidak_hadir' => null, 'rate' => null];

    /**
     * @param  Collection<int, Employee>|null  $employees
     * @return array{working_days: ?int, rows: list<array{employee: Employee, working_days: ?int, hadir: ?int, terlambat: ?int, izin: ?int, sakit: ?int, cuti: ?int, tidak_hadir: ?int, rate: ?float}>}
     */
    public function month(int $year, int $month, ?Collection $employees = null): array
    {
        $employees ??= Employee::with('user')->orderBy('id')->get();
        $workDays = config('hrd.work_days', [1, 2, 3, 4, 5]);

        $records = EmployeeAttendance::whereYear('date', $year)->whereMonth('date', $month)->get()
            ->filter(fn (EmployeeAttendance $record) => in_array($record->date->dayOfWeekIso, $workDays, true));
        $workingDates = $records->map(fn (EmployeeAttendance $record) => $record->date->toDateString())->unique()->sort()->values();

        if ($workingDates->isEmpty()) {
            return [
                'working_days' => null,
                'rows' => $employees->map(fn (Employee $employee) => ['employee' => $employee, ...self::EMPTY])->values()->all(),
            ];
        }

        $first = CarbonImmutable::create($year, $month, 1);
        $leaves = LeaveRequest::approvedBetween($first->toDateString(), $first->endOfMonth()->toDateString())
            ->whereIn('employee_id', $employees->pluck('id'))
            ->get()
            ->groupBy('employee_id');
        $byEmployee = $records->groupBy('employee_id');

        $rows = $employees->map(function (Employee $employee) use ($workingDates, $byEmployee, $leaves) {
            $hired = $employee->hire_date ? CarbonImmutable::parse($employee->hire_date)->toDateString() : null;
            $dates = $workingDates->filter(fn (string $date) => $hired === null || $date >= $hired);
            $own = $byEmployee->get($employee->id, collect())->keyBy(fn (EmployeeAttendance $record) => $record->date->toDateString());
            $ownLeaves = $leaves->get($employee->id, collect());

            $counts = ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'sakit' => 0, 'cuti' => 0, 'tidak_hadir' => 0];
            foreach ($dates as $date) {
                $status = $own->get($date)?->status;

                if (in_array($status, ['hadir', 'terlambat', 'izin', 'sakit', 'cuti'], true)) {
                    $counts[$status]++;
                } elseif ($status === null && ($leave = $ownLeaves->first(fn (LeaveRequest $leave) => $leave->start_date->toDateString() <= $date && $leave->end_date->toDateString() >= $date))) {
                    $counts[LeaveRequest::RECAP_BUCKET[$leave->type] ?? 'izin']++;
                } else {
                    // No scan and no approved leave, or recorded as alfa.
                    $counts['tidak_hadir']++;
                }
            }

            $expected = $dates->count();

            return [
                'employee' => $employee,
                'working_days' => $expected,
                ...$counts,
                'rate' => $expected > 0 ? round(($counts['hadir'] + $counts['terlambat']) / $expected * 100, 1) : null,
            ];
        })->values()->all();

        return ['working_days' => $workingDates->count(), 'rows' => $rows];
    }

    /** @return array{working_days: ?int, hadir: ?int, terlambat: ?int, izin: ?int, sakit: ?int, cuti: ?int, tidak_hadir: ?int, rate: ?float} */
    public function forEmployee(Employee $employee, int $year, int $month): array
    {
        $row = $this->month($year, $month, collect([$employee]))['rows'][0];
        unset($row['employee']);

        return $row;
    }
}
