<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * a student's attendance in one ClassSession (one lesson).
 */
class SessionAttendance extends Model
{
    use HasFactory;

    public const STATUS_HADIR = 'hadir';

    public const STATUS_ALPHA = 'alpha';

    public const STATUS_SAKIT = 'sakit';

    public const STATUS_IZIN = 'izin';

    public const STATUS_TELAT = 'telat';

    public const STATUSES = [self::STATUS_HADIR, self::STATUS_ALPHA, self::STATUS_SAKIT, self::STATUS_IZIN, self::STATUS_TELAT];

    /** Statuses that count as physically present in class. */
    public const PRESENT_STATUSES = [self::STATUS_HADIR, self::STATUS_TELAT];

    public const METHOD_QR_SCAN = 'qr_scan';

    public const METHOD_MANUAL_TEACHER = 'manual_teacher';

    public const METHOD_AUTO_ALPHA = 'auto_alpha';

    protected $fillable = [
        'class_session_id',
        'student_id',
        'status',
        'check_in_method',
        'checked_in_at',
        'recorded_by',
        'student_leave_request_id',
        'notes',
        'is_override',
        'override_by',
        'override_at',
        'override_reason',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'override_at' => 'datetime',
        'is_override' => 'boolean',
    ];

    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function overrider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'override_by');
    }

    public function isPresent(): bool
    {
        return in_array($this->status, self::PRESENT_STATUSES, true);
    }
}
