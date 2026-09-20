<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'category',
        'image_path',
        'attachment_path',
        'target_role',
        'target_classroom_id',
        'priority',
        'is_pinned',
        'require_acknowledgement',
        'read_count',
        'is_active',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'is_pinned' => 'boolean',
        'require_acknowledgement' => 'boolean',
        'read_count' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function targetClassroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'target_classroom_id');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    public function readUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'announcement_reads')
                    ->withPivot('read_at', 'acknowledged_at')
                    ->withTimestamps();
    }

    /**
     * Check if a specific user has read the announcement.
     */
    public function isReadBy(int $userId): bool
    {
        return $this->reads()->where('user_id', $userId)->exists();
    }

    /**
     * Check if a specific user has acknowledged the announcement.
     */
    public function isAcknowledgedBy(int $userId): bool
    {
        return $this->reads()
                    ->where('user_id', $userId)
                    ->whereNotNull('acknowledged_at')
                    ->exists();
    }
}
