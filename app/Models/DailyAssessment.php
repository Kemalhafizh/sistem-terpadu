<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyAssessment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'subject_id',
        'classroom_id',
        'teacher_id',
        'academic_year_id',
        'title',
        'type',
        'assessment_date',
        'max_score',
        'kkm',
        'description',
        'learning_objective',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'max_score' => 'decimal:2',
        'kkm' => 'decimal:2',
    ];

    /** @return BelongsTo<Subject, $this> */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /** @return BelongsTo<Classroom, $this> */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /** @return BelongsTo<User, $this> */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /** @return BelongsTo<AcademicYear, $this> */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /** @return HasMany<DailyAssessmentScore, $this> */
    public function scores(): HasMany
    {
        return $this->hasMany(DailyAssessmentScore::class);
    }

    /**
     * Get the effective KKM for this assessment.
     */
    public function getEffectiveKkmAttribute(): float
    {
        return (float) ($this->kkm ?? $this->subject->kkm ?? 75.0);
    }
}
