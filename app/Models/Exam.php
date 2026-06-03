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
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'shuffle_questions' => 'boolean',
        'shuffle_options' => 'boolean',
        'is_secure_mode' => 'boolean',
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
}
