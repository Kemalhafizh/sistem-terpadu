<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticalEmployeePerformance extends Model
{
    use HasFactory;

    protected $table = 'analytical_employee_performances';

    protected $fillable = [
        'employee_id',
        'date',
        'attendance_score',
        'punctuality_minutes',
        'tasks_completed_count',
        'cbt_exams_created',
    ];

    protected $casts = [
        'date' => 'date',
        'attendance_score' => 'decimal:2',
        'punctuality_minutes' => 'integer',
        'tasks_completed_count' => 'integer',
        'cbt_exams_created' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
