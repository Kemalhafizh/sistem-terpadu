<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'uuid',
        'title',
        'subject_id',
        'classroom_id',
        'academic_year_id',
        'created_by',
        'type',
        'start_time',
        'end_time',
        'duration_minutes',
        'total_questions',
        'shuffle_questions',
        'shuffle_options',
        'is_secure_mode',
        'max_violations',
        'passing_score',
        'status',
        // Fase 87: Token-Gated Mobile-Only (Berlaku 5 Menit)
        'access_token',
        'token_expires_at',
        'mobile_only',
        'operator_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'token_expires_at' => 'datetime',
        'shuffle_questions' => 'boolean',
        'shuffle_options' => 'boolean',
        'is_secure_mode' => 'boolean',
        'mobile_only' => 'boolean',
        'duration_minutes' => 'integer',
        'total_questions' => 'integer',
        'max_violations' => 'integer',
        'passing_score' => 'decimal:2',
    ];


    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Fase 87: Guru Operator Ujian (untuk UTS/UAS).
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }


    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(QuestionBank::class, 'exam_questions', 'exam_id', 'question_bank_id')
            ->withPivot('order', 'weight')
            ->withTimestamps();
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isOngoing(): bool
    {
        $now = now();
        return $this->isPublished() && $now->between($this->start_time, $this->end_time);
    }

    protected static function booted(): void
    {
        static::saving(function ($exam) {
            if (!empty($exam->access_token) && empty($exam->token_expires_at)) {
                $exam->token_expires_at = now()->addMinutes(5);
            }
        });
    }

    /**
     * Cek apakah token ujian sudah kedaluwarsa (> 5 menit).
     */
    public function isTokenExpired(): bool
    {
        if (empty($this->access_token)) {
            return true;
        }
        if (empty($this->token_expires_at)) {
            if ($this->updated_at) {
                return now()->gt($this->updated_at->copy()->addMinutes(5));
            }
            return false;
        }
        return now()->gt($this->token_expires_at);
    }

    /**
     * Hitung sisa detik masa berlaku token (dari total 300 detik / 5 menit).
     */
    public function getTokenRemainingSeconds(): int
    {
        if (empty($this->token_expires_at)) {
            return 300;
        }
        return max(0, (int) now()->diffInSeconds($this->token_expires_at, false));
    }
}
