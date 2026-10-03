<?php

namespace App\Models;

use App\Support\Access\StudentDataAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    /** @return HasMany<AnnouncementRead, $this> */
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

    /** Roles that manage announcements and see every active one. */
    public const MANAGER_ROLES = ['superadmin', 'admin', 'kepala_sekolah'];

    /**
     * Active, unexpired announcements meant for [user], pinned first. The
     * one place this is decided, for the web dashboard and the mobile API.
     *
     * - target_role "student" / "parent" / "guru" are the web form's three
     *   audiences; every spelling of the role matches, and "guru" means
     *   "Guru & Staf", so TU, BK, wali kelas and waka see it too.
     * - target_classroom_id limits it to that class's students and the
     *   parents of any child in it. Staff see class announcements.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->orderByDesc('is_pinned')
            ->latest();

        if (in_array($user->role, self::MANAGER_ROLES, true)) {
            return $query;
        }

        $audiences = match (true) {
            in_array($user->role, StudentDataAccess::STUDENT_ROLES, true) => StudentDataAccess::STUDENT_ROLES,
            in_array($user->role, StudentDataAccess::PARENT_ROLES, true) => StudentDataAccess::PARENT_ROLES,
            in_array($user->role, StudentDataAccess::STAFF_ROLES, true) => StudentDataAccess::TEACHER_ROLES,
            default => [],
        };
        $audiences[] = $user->role;

        $query->where(fn ($q) => $q->whereNull('target_role')->orWhereIn('target_role', array_unique($audiences)));

        $classroomIds = match (true) {
            in_array($user->role, StudentDataAccess::STUDENT_ROLES, true) => Student::where('user_id', $user->id)->pluck('classroom_id'),
            in_array($user->role, StudentDataAccess::PARENT_ROLES, true) => Student::where('parent_id', $user->id)->pluck('classroom_id'),
            default => null,
        };
        if ($classroomIds !== null) {
            $query->where(fn ($q) => $q->whereNull('target_classroom_id')
                ->orWhereIn('target_classroom_id', $classroomIds->filter()->all()));
        }

        return $query;
    }

    /** Managers may open any announcement, even an expired or inactive one they manage. */
    public function canBeSeenBy(User $user): bool
    {
        return in_array($user->role, self::MANAGER_ROLES, true)
            || static::visibleTo($user)->whereKey($this->getKey())->exists();
    }

    /** Records that [user] opened it, once; the receipts page counts these. */
    public function recordReadBy(User $user): AnnouncementRead
    {
        $read = $this->reads()->firstOrCreate(['user_id' => $user->id], ['read_at' => now()]);
        if ($read->wasRecentlyCreated) {
            $this->increment('read_count');
        }

        return $read;
    }

    /** Records that [user] confirmed reading and understanding it. */
    public function acknowledgeBy(User $user): AnnouncementRead
    {
        $read = $this->recordReadBy($user);
        if ($read->acknowledged_at === null) {
            $read->update(['acknowledged_at' => now()]);
        }

        return $read;
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
