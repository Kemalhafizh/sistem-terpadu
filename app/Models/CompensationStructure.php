<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompensationStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'base_salary', 'allowances', 'deductions', 'effective_date'
    ];

    protected $casts = [
        'allowances' => 'array',
        'deductions' => 'array',
        'effective_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}