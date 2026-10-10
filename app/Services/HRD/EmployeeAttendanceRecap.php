<?php

namespace App\Services\HRD;

use App\Models\Employee;
use App\Models\EmployeeAttendance;
use Illuminate\Support\Collection;

/**
 * Monthly staff attendance per employee, for the HRD report and payroll
 * (FASE 119.6.1).
 *
 * The working days of a month are the days the attendance data covers (a day
 * someone scanned in or was recorded on leave), not an assumed 25. A month
 * with no data at all has no working days, and every figure is null, not 0.
 */
class EmployeeAttendanceRecap
{
    /**
     * @param  Collection<int, Employee>|null  $employees
     * @return array{working_days: ?int, rows: list<array{employee: Employee, hadir: ?int, terlambat: ?int, izin: ?int, sakit: ?int, cuti: ?int, tidak_hadir: ?int, rate: ?float}>}
     */
    public function month(int $year, int $month, ?Collection $employees = null): array
    {
        $employees ??= Employee::with('user')->orderBy('id')->get();
        $records = EmployeeAttendance::whereYear('date', $year)->whereMonth('date', $month)->get();
        $workingDays = $records->map(fn (EmployeeAttendance $record) => $record->date->toDateString())->unique()->count();
        $byEmployee = $records->groupBy('employee_id');

        $rows = $employees->map(function (Employee $employee) use ($byEmployee, $workingDays) {
            if ($workingDays === 0) {
                return ['employee' => $employee, 'hadir' => null, 'terlambat' => null, 'izin' => null, 'sakit' => null, 'cuti' => null, 'tidak_hadir' => null, 'rate' => null];
            }

            $own = $byEmployee->get($employee->id, collect());
            $count = fn (string $status) => $own->where('status', $status)->count();
            $recordedDays = $own->map(fn (EmployeeAttendance $record) => $record->date->toDateString())->unique()->count();
            $present = $count('hadir') + $count('terlambat');

            return [
                'employee' => $employee,
                'hadir' => $count('hadir'),
                'terlambat' => $count('terlambat'),
                'izin' => $count('izin'),
                'sakit' => $count('sakit'),
                'cuti' => $count('cuti'),
                // Days the data covers with nothing for this employee, plus days marked alfa.
                'tidak_hadir' => ($workingDays - $recordedDays) + $count('alfa'),
                'rate' => round($present / $workingDays * 100, 1),
            ];
        })->values()->all();

        return ['working_days' => $workingDays ?: null, 'rows' => $rows];
    }

    /** @return array{working_days: ?int, hadir: ?int, terlambat: ?int, izin: ?int, sakit: ?int, cuti: ?int, tidak_hadir: ?int, rate: ?float} */
    public function forEmployee(Employee $employee, int $year, int $month): array
    {
        $recap = $this->month($year, $month, collect([$employee]));
        $row = $recap['rows'][0];
        unset($row['employee']);

        return ['working_days' => $recap['working_days'], ...$row];
    }
}
