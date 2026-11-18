<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * one live lesson: a Schedule entry held on a given date.
 *
 * session_date is kept as a plain 'Y-m-d' string (no date cast): a date cast
 * is stored as 'Y-m-d 00:00:00' on SQLite, which silently breaks equality
 * lookups and the (schedule_id, session_date) unique key.
 *
 * @property string $session_date
 * @property string $scheduled_start
 * @property string $scheduled_end
 * @property string $status
 */
class ClassSession extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_AUTO_CLOSED = 'auto_closed';

    /** Statuses in which the lesson actually took place. */
    public const HELD_STATUSES = [self::STATUS_ACTIVE, self::STATUS_COMPLETED, self::STATUS_AUTO_CLOSED];

    protected $fillable = [
        'schedule_id',
        'teacher_id',
        'classroom_id',
        'subject_id',
        'academic_year_id',
        'session_date',
        'jam_ke',
        'scheduled_start',
        'scheduled_end',
        'actual_start',
        'actual_end',
        'status',
        'qr_secret',
        'topic',
        'notes',
        'is_auto_closed',
        'teaching_journal_id',
        'start_reminder_sent_at',
        'end_reminder_sent_at',
    ];

    protected $hidden = ['qr_secret'];

    protected $casts = [
        'jam_ke' => 'integer',
        'actual_start' => 'datetime',
        'actual_end' => 'datetime',
        'is_auto_closed' => 'boolean',
        'start_reminder_sent_at' => 'datetime',
        'end_reminder_sent_at' => 'datetime',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function teachingJournal(): BelongsTo
    {
        return $this->belongsTo(TeachingJournal::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(SessionAttendance::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeForDate(Builder $query, string $date): void
    {
        $query->where('session_date', $date);
    }

    public function scopeForToday(Builder $query): void
    {
        $query->where('session_date', now()->toDateString());
    }

    public function scopeForTeacher(Builder $query, int $teacherId): void
    {
        $query->where('teacher_id', $teacherId);
    }

    public function scheduledStartAt(): Carbon
    {
        return Carbon::parse($this->session_date.' '.$this->scheduled_start);
    }

    public function scheduledEndAt(): Carbon
    {
        return Carbon::parse($this->session_date.' '.$this->scheduled_end);
    }

    /** Earliest moment the teacher may start the lesson. */
    public function startableFrom(): Carbon
    {
        return $this->scheduledStartAt()->subMinutes((int) config('class_sessions.start_early_minutes', 10));
    }

    /** Moment an active session is closed by the scheduler. */
    public function autoCloseAt(): Carbon
    {
        return $this->scheduledEndAt()->addMinutes((int) config('class_sessions.auto_close_grace_minutes', 5));
    }

    /**
     * Blueprint 117.3 helper: is "now" inside the window in which the
     * lesson may be started (start − early tolerance … scheduled end)?
     */
    public function isWithinScheduledTime(?Carbon $now = null): bool
    {
        $now ??= now();

        return $now->between($this->startableFrom(), $this->scheduledEndAt());
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function hasEnded(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_AUTO_CLOSED, self::STATUS_CANCELLED], true);
    }
}
