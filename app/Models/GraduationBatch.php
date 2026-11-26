<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class GraduationBatch extends Model
{
    use HasUuid, LogsActivity;

    public const STATUS_DRAFT = 'draf';

    public const STATUS_PENDING = 'pending';

    public const STATUS_REVISION = 'revision';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_EXECUTED = 'dieksekusi';

    protected $fillable = [
        'school_year_id',
        'graduation_year',
        'sk_number',
        'sk_date',
        'graduation_date',
        'meeting_note_id',
        'status',
        'created_by',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'announced_at',
        'executed_by',
        'executed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'graduation_year' => 'integer',
            'sk_date' => 'date',
            'graduation_date' => 'date',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'announced_at' => 'datetime',
            'executed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<SchoolYear, $this> */
    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    /** @return BelongsTo<MeetingNote, $this> */
    public function meetingNote(): BelongsTo
    {
        return $this->belongsTo(MeetingNote::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<User, $this> */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return BelongsTo<User, $this> */
    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    /** @return HasMany<GraduationDecision, $this> */
    public function decisions(): HasMany
    {
        return $this->hasMany(GraduationDecision::class);
    }

    /** @return MorphMany<WorkflowRequest, $this> */
    public function workflowRequests(): MorphMany
    {
        return $this->morphMany(WorkflowRequest::class, 'requestable');
    }
}
