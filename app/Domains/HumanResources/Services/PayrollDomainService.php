<?php

namespace App\Domains\HumanResources\Services;

class PayrollDomainService
{
    /**
     * Calculate take home pay for SMA ISA 1 employee based on base salary, YBWSA allowance, & attendance deductions.
     */
    public function calculateSalary(float $baseSalary, float $ybwsaAllowance, int $absentDays, float $dailyDeductionRate = 50000.0): array
    {
        $totalDeductions = $absentDays * $dailyDeductionRate;
        $grossSalary = $baseSalary + $ybwsaAllowance;
        $netSalary = max(0, $grossSalary - $totalDeductions);

        return [
            'base_salary' => $baseSalary,
            'ybwsa_allowance' => $ybwsaAllowance,
            'gross_salary' => $grossSalary,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
        ];
    }
}
