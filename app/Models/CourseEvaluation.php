<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseEvaluation extends Model
{
    use HasFactory;

    protected $table = 'course_evaluations';

    protected $fillable = [
        'subject_id',
        'teacher_id',
        'academic_year',
        'semester',
        'start_date',
        'end_date',
        'is_published',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_published' => 'boolean',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(CourseEvaluationResponse::class, 'evaluation_id');
    }
}
