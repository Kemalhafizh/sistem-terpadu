<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceEvaluation extends Model
{
    protected $fillable = [
        'employee_id',
        'evaluator_id',
        'period',
        'criteria_scores',
        'total_score',
        'grade',
        'strengths',
        'improvements',
        'goals',
        'status',
    ];

    protected $casts = [
        'criteria_scores' => 'array',
        'total_score' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }
}
