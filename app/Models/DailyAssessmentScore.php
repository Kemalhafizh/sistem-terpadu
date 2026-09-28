<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyAssessmentScore extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'daily_assessment_id',
        'student_id',
        'score',
        'needs_remedial',
        'remedial_score',
        'remedial_status',
        'notes',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'needs_remedial' => 'boolean',
        'remedial_score' => 'decimal:2',
    ];

    /** @return BelongsTo<DailyAssessment, $this> */
    public function dailyAssessment(): BelongsTo
    {
        return $this->belongsTo(DailyAssessment::class);
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the final score attribute, implementing Kurikulum Merdeka capping.
     * If the student completed remedial, their score is updated but capped at the KKM.
     */
    public function getFinalScoreAttribute(): ?float
    {
        if ($this->remedial_status === 'completed' && $this->remedial_score !== null) {
            $kkm = $this->dailyAssessment->effective_kkm;
            $cappedRemedial = min((float) $this->remedial_score, $kkm);

            return max((float) ($this->score ?? 0), $cappedRemedial);
        }

        return $this->score !== null ? (float) $this->score : null;
    }

    /**
     * Helper to check if student passed the assessment (using initial or final score).
     */
    public function getIsPassedAttribute(): bool
    {
        $kkm = $this->dailyAssessment->effective_kkm;
        $finalScore = $this->final_score;

        return $finalScore !== null && $finalScore >= $kkm;
    }
}
