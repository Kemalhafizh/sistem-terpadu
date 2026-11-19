<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentLeaveRequest extends Model
{
    use HasFactory, HasUuid, LogsActivity, SoftDeletes;

    public const TYPE_SAKIT = 'sakit';

    public const TYPE_IZIN = 'izin';

    public const STATUS_PENDING = 'pending';

    public const STATUS_AUTO_APPROVED = 'auto_approved';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    public const APPROVED_STATUSES = [self::STATUS_AUTO_APPROVED, self::STATUS_APPROVED];

    public const ACTIVE_STATUSES = [self::STATUS_PENDING, self::STATUS_AUTO_APPROVED, self::STATUS_APPROVED];

    protected $fillable = [
        'student_id',
        'classroom_id',
        'academic_year_id',
        'submitted_by',
        'type',
        'start_date',
        'end_date',
        'days_count',
        'reason',
        'attachment_path',
        'status',
        'auto_approved',
        'decided_by',
        'decided_at',
        'decision_notes',
        'escalated_at',
        'escalated_to',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'days_count' => 'integer',
            'auto_approved' => 'boolean',
            'decided_at' => 'datetime',
            'escalated_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function escalatedRecipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_to');
    }

    public function isApproved(): bool
    {
        return in_array($this->status, self::APPROVED_STATUSES, true);
    }

    public function dailyAttendanceCode(): string
    {
        return $this->type === self::TYPE_SAKIT ? 'S' : 'I';
    }

    public function sessionAttendanceStatus(): string
    {
        return $this->type === self::TYPE_SAKIT
            ? SessionAttendance::STATUS_SAKIT
            : SessionAttendance::STATUS_IZIN;
    }
}
