<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeachingJournal extends Model
{
    use HasFactory, HasUuid, LogsActivity, SoftDeletes;

    protected $fillable = [
        'user_id',
        'subject_id',
        'classroom_id',
        'academic_year_id',
        'teaching_date',
        'jam_ke',
        'topic',
        'learning_activity',
        'learning_method',
        'obstacles',
        'notes',
        'students_present',
        'students_absent',
        'attachment_path',
        'status',
        'reviewer_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'teaching_date' => 'date',
        'reviewed_at' => 'datetime',
        'jam_ke' => 'integer',
        'students_present' => 'integer',
        'students_absent' => 'integer',
    ];

    /**
     * Boot helper to auto-set academic_year_id on creation.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->academic_year_id)) {
                $activeYear = AcademicYear::current();
                if ($activeYear) {
                    $model->academic_year_id = $activeYear->id;
                }
            }
        });
    }

    /** @return BelongsTo<User, $this> */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

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

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes untuk filtering dinamis
    public function scopeForTeacher(Builder $query, int $teacherId): void
    {
        $query->where('user_id', $teacherId);
    }

    public function scopeNeedsReview(Builder $query): void
    {
        $query->where('status', 'submitted');
    }

    public function scopeByMonth(Builder $query, int $month, int $year): void
    {
        $query->whereMonth('teaching_date', $month)
            ->whereYear('teaching_date', $year);
    }
}
