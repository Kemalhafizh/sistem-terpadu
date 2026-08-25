<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedLessonPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'subject_name',
        'grade_level',
        'topic',
        'curriculum_type',
        'learning_objectives',
        'pedagogical_steps',
        'assessment_rubric',
        'status',
    ];

    protected $casts = [
        'learning_objectives' => 'array',
        'pedagogical_steps' => 'array',
        'assessment_rubric' => 'array',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
