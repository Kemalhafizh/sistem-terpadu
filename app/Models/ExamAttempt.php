<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAttempt extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'uuid',
        'exam_id',
        'student_id',
        'answers',
        'question_order',
        'score',
        'correct_count',
        'wrong_count',
        'unanswered_count',
        'started_at',
        'submitted_at',
        'status',
        'violation_count',
        'violation_logs',
        'is_auto_submitted',
        // Fase 87: Token-Gated Mobile-Only
        'access_platform',
        'used_token',
        'token_validated_at',
        'force_closed',
        'force_close_reason',
        // Fase 72.2: Live Proctoring
        'last_heartbeat_at',
    ];

    protected $casts = [
        'answers' => 'array',
        'question_order' => 'array',
        'violation_logs' => 'array',
        'score' => 'decimal:2',
        'correct_count' => 'integer',
        'wrong_count' => 'integer',
        'unanswered_count' => 'integer',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'token_validated_at' => 'datetime',
        'is_auto_submitted' => 'boolean',
        'force_closed' => 'boolean',
        'violation_count' => 'integer',
        'last_heartbeat_at' => 'datetime',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * The student's attempt they may work on right now: in progress and, when
     * the exam is token-gated (has an access_token), entered through
     * validate-token with the proctor's token. Null means the student has not
     * entered the exam (or is finished/locked), so the question, key, sync,
     * essay and submit endpoints must refuse instead of creating an attempt.
     */
    public static function openFor(Exam $exam, Student $student): ?self
    {
        return static::query()
            ->where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->when(filled($exam->access_token), fn ($query) => $query->whereNotNull('token_validated_at'))
            ->latest()
            ->first();
    }

    /**
     * Compute remaining seconds based on exam duration limit and exam end time.
     */
    public function getRemainingSeconds(): int
    {
        if ($this->status !== 'in_progress') {
            return 0;
        }

        $exam = $this->exam;
        if (! $exam) {
            return 0;
        }

        $startTime = $this->started_at ?? $this->created_at ?? now();
        $durationLimit = $startTime->copy()->addMinutes($exam->duration_minutes);
        $endTimeLimit = $exam->end_time;

        // Take the earliest limit
        $limit = $durationLimit->min($endTimeLimit);
        $now = now();

        if ($now->greaterThanOrEqualTo($limit)) {
            return 0;
        }

        return $now->diffInSeconds($limit);
    }

    /**
     * Check if attempt time limit is expired.
     */
    public function isExpired(): bool
    {
        if ($this->status !== 'in_progress') {
            return true;
        }

        return $this->getRemainingSeconds() <= 0;
    }
}
