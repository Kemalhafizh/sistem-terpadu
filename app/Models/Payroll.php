<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'base_salary',
        'allowances',
        'deductions',
        'net_salary',
        'working_days',
        'present_days',
        'late_days',
        'absent_days',
        'leave_days',
        'status',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * Get the employee associated with the payroll.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the month name in Indonesian.
     */
    public function getMonthNameAttribute()
    {
        $months = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];

        return $months[$this->month] ?? $this->month;
    }

    /**
     * Helper to format values to IDR (Rupiah).
     */
    public function formatRupiah($value)
    {
        return 'Rp '.number_format($value, 0, ',', '.');
    }

    public function getBaseSalaryFormattedAttribute()
    {
        return $this->formatRupiah($this->base_salary);
    }

    public function getAllowancesFormattedAttribute()
    {
        return $this->formatRupiah($this->allowances);
    }

    public function getDeductionsFormattedAttribute()
    {
        return $this->formatRupiah($this->deductions);
    }

    public function getNetSalaryFormattedAttribute()
    {
        return $this->formatRupiah($this->net_salary);
    }
}
