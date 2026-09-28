<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grade extends Model
{
    use LogsActivity, SoftDeletes;

    protected $fillable = [
        'student_id',
        'subject_id',
        'academic_year_id',
        'type',
        'score',
        'description',
        'recorded_by',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<Subject, $this> */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Get the letter grade (predikat) based on the subject's KKM.
     */
    public function getPredikatAttribute(): string
    {
        $kkm = $this->subject->kkm ?? 75;

        return match (true) {
            $this->score >= 90 => 'A (Sangat Baik)',
            $this->score >= 80 => 'B (Baik)',
            $this->score >= $kkm => 'C (Cukup)',
            default => 'D (Kurang)',
        };
    }

    /**
     * Check if this grade passes the subject's KKM threshold.
     */
    public function getIsPassedAttribute(): bool
    {
        return $this->score >= ($this->subject->kkm ?? 75);
    }
}
