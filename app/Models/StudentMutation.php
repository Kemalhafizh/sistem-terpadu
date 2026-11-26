<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class StudentMutation extends Model
{
    use HasUuid, LogsActivity;

    public const TYPE_IN = 'in';

    public const TYPE_OUT = 'out';

    public const STATUS_PENDING = 'pending';

    public const STATUS_REVISION = 'revision';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const BILLING_CLEARED = 'cleared';

    public const BILLING_NEEDS_SETTLEMENT = 'needs_settlement';

    public const BILLING_SETTLED = 'settled';

    /** @var array<string, mixed> */
    protected $attributes = [
        'status' => self::STATUS_APPROVED,
    ];

    protected $fillable = [
        'student_id',
        'classroom_id',
        'school_year_id',
        'type',
        'mutation_date',
        'reason',
        'target_school',
        'letter_number',
        'document_path',
        'status',
        'candidate_data',
        'billing_review_status',
        'billing_review_summary',
        'requested_by',
        'approved_by',
        'approved_at',
        'executed_at',
        'rejection_notes',
    ];

    protected function casts(): array
    {
        return [
            'mutation_date' => 'date:Y-m-d',
            'candidate_data' => 'array',
            'billing_review_summary' => 'array',
            'approved_at' => 'datetime',
            'executed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<Classroom, $this> */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /** @return BelongsTo<SchoolYear, $this> */
    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    /** @return BelongsTo<User, $this> */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return MorphMany<WorkflowRequest, $this> */
    public function workflowRequests(): MorphMany
    {
        return $this->morphMany(WorkflowRequest::class, 'requestable');
    }

    public function getStudentDisplayNameAttribute(): string
    {
        if ($this->student?->user?->name) {
            return $this->student->user->name;
        }

        $candidateName = is_array($this->candidate_data) ? ($this->candidate_data['name'] ?? null) : null;

        return filled($candidateName) ? (string) $candidateName : 'Calon Siswa Pindahan';
    }
}
