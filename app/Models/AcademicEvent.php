<?php

namespace App\Models;

use App\Support\Access\StudentDataAccess;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicEvent extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'event_type',
        'milestone',
        'start_date',
        'end_date',
        'is_all_day',
        'color',
        'target_role',
        'target_classroom_id',
        'academic_year_id',
        'academic_calendar_id',
        'created_by',
        'is_active',
        'send_notification',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_all_day' => 'boolean',
        'is_active' => 'boolean',
        'send_notification' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'target_classroom_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function academicCalendar()
    {
        return $this->belongsTo(AcademicCalendar::class);
    }

    // Scopes untuk filtering dinamis
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeForClassroom(Builder $query, ?int $classroomId): void
    {
        if ($classroomId) {
            $query->where(function ($q) use ($classroomId) {
                $q->whereNull('target_classroom_id')
                    ->orWhere('target_classroom_id', $classroomId);
            });
        }
    }

    public function scopeInDateRange(Builder $query, string $start, string $end): void
    {
        $query->where(function ($q) use ($start, $end) {
            $q->whereBetween('start_date', [$start, $end])
                ->orWhereBetween('end_date', [$start, $end])
                ->orWhere(function ($sub) use ($start, $end) {
                    $sub->where('start_date', '<=', $start)
                        ->where('end_date', '>=', $end);
                });
        });
    }

    /**
     * Active events a user should see, for the web calendar and the app alike:
     * aimed at everyone or at the user's role (under any of its spellings),
     * and for students and parents only those for everyone or for their own
     * (children's) classrooms. Administrators see the whole calendar.
     */
    public function scopeVisibleTo(Builder $query, User $user): void
    {
        $query->where('is_active', true);
        if ($user->isAdmin()) {
            return;
        }

        $roles = match (true) {
            in_array($user->role, StudentDataAccess::STUDENT_ROLES, true) => StudentDataAccess::STUDENT_ROLES,
            in_array($user->role, StudentDataAccess::PARENT_ROLES, true) => StudentDataAccess::PARENT_ROLES,
            in_array($user->role, StudentDataAccess::TEACHER_ROLES, true) => StudentDataAccess::TEACHER_ROLES,
            default => [$user->role],
        };
        $query->where(fn (Builder $q) => $q->whereNull('target_role')->orWhereIn('target_role', $roles));

        $classroomIds = match (true) {
            in_array($user->role, StudentDataAccess::STUDENT_ROLES, true) => [$user->student?->classroom_id],
            in_array($user->role, StudentDataAccess::PARENT_ROLES, true) => $user->children()->pluck('classroom_id')->all(),
            default => null,
        };
        if ($classroomIds !== null) {
            $query->where(fn (Builder $q) => $q->whereNull('target_classroom_id')->orWhereIn('target_classroom_id', array_filter($classroomIds)));
        }
    }

    public function scopeUpcoming(Builder $query): void
    {
        $query->where('start_date', '>=', now());
    }
}
