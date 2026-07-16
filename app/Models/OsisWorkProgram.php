<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OsisWorkProgram extends Model
{
    protected $fillable = [
        'academic_year_id',
        'title',
        'sekbid',
        'description',
        'budget_plan',
        'budget_actual',
        'planned_date',
        'actual_date',
        'status',
        'report',
        'photo_paths',
    ];

    protected $casts = [
        'planned_date' => 'date',
        'actual_date' => 'date',
        'photo_paths' => 'array',
        'budget_plan' => 'decimal:2',
        'budget_actual' => 'decimal:2',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
