<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DapodikSyncItem extends Model
{
    use HasFactory, HasUuid, LogsActivity;

    public const ENTITY_STUDENT = 'student';

    public const ENTITY_EMPLOYEE = 'employee';

    public const STATUS_PENDING = 'pending';

    public const STATUS_SYNCED = 'synced';

    public const STATUS_DISMISSED = 'dismissed';

    protected $fillable = [
        'entity_type',
        'entity_id',
        'profile_change_request_id',
        'field_name',
        'dapodik_column_label',
        'old_value',
        'new_value',
        'status',
        'synced_by',
        'synced_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'synced_at' => 'datetime',
        ];
    }

    public function profileChangeRequest(): BelongsTo
    {
        return $this->belongsTo(ProfileChangeRequest::class);
    }

    public function syncer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'synced_by');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'entity_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'entity_id');
    }
}
