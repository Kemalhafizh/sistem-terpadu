<?php

namespace App\Models;

use App\Support\Access\StudentDataAccess;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Classroom extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = ['name', 'grade_level', 'major', 'teacher_id', 'academic_year'];

    /**
     * May [user] see this class? School-wide roles always; a teacher when they
     * teach it (schedule) or lead it (homeroom) this academic year. The same
     * rule as Student::visibleTo, for pages about a whole class.
     */
    public function isVisibleTo(User $user): bool
    {
        if (in_array($user->role, Student::SCHOOL_WIDE_ROLES, true)) {
            return true;
        }
        if (! in_array($user->role, StudentDataAccess::TEACHER_ROLES, true) || ! ($year = AcademicYear::current())) {
            return false;
        }

        return Schedule::where('teacher_id', $user->id)->where('academic_year_id', $year->id)->where('classroom_id', $this->id)->exists()
            || ((int) $this->teacher_id === $user->id && $this->academic_year === $year->year);
    }

    /** @return BelongsTo<User, $this> */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /** @return HasMany<Student, $this> */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Get all classrooms, cached forever.
     */
    public static function allCached()
    {
        return Cache::rememberForever('classrooms_all', function () {
            return self::orderBy('name')->get();
        });
    }

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('classrooms_all');
        });
        static::deleted(function () {
            Cache::forget('classrooms_all');
        });
    }
}
