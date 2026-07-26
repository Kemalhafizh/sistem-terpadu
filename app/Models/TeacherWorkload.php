<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherWorkload extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'academic_year',
        'semester',
        'teaching_hours_per_week',
        'additional_duties',
        'extracurricular_duties',
        'total_workload_score',
        'status',
    ];

    protected $casts = [
        'additional_duties' => 'array',
        'extracurricular_duties' => 'array',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
