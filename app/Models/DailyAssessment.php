<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'max_score' => 'decimal:2',
        'kkm' => 'decimal:2',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function scores()
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
