<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfileChangeRequest extends Model
{
    use HasFactory, HasUuid, LogsActivity;

    public const TARGET_STUDENT = 'student';

    public const TARGET_EMPLOYEE = 'employee';

    public const TARGET_USER = 'user';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'requester_user_id',
        'target_user_id',
        'target_type',
        'target_id',
        'field_name',
        'field_label',
        'old_value',
        'new_value',
        'reason',
        'evidence_path',
        'status',
        'reviewed_by',
        'review_notes',
        'reviewed_at',
        'escalated_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'escalated_at' => 'datetime',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_user_id');
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function dapodikSyncItems(): HasMany
    {
        return $this->hasMany(DapodikSyncItem::class);
    }
}
