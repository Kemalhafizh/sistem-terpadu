<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DifferentiatedPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_plan_id',
        'differentiation_type',
        'tier_1_strategy',
        'tier_2_strategy',
        'tier_3_strategy',
        'modified_assessments',
        'teacher_reflection',
    ];

    protected $casts = [
        'tier_1_strategy' => 'array',
        'tier_2_strategy' => 'array',
        'tier_3_strategy' => 'array',
        'modified_assessments' => 'array',
    ];

    public function lessonPlan(): BelongsTo
    {
        return $this->belongsTo(LessonPlan::class);
    }
}
