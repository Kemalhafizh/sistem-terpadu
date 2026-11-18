<?php

namespace App\Models;

use App\Support\Access\StudentDataAccess;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Classroom extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = ['school_year_id', 'name', 'grade_level', 'major', 'teacher_id', 'academic_year'];

    /**
     * May [user] see this class? School-wide roles always; a teacher when they
     * teach it (schedule) or lead it (homeroom) this academic year. The same
     * rule as Student::visibleTo, for pages about a whole class.
     */
    public function isVisibleTo(User $user): bool
    {
        if (array_intersect($user->effectiveRoles(), Student::SCHOOL_WIDE_ROLES) !== []) {
            return true;
        }
        if (array_intersect($user->effectiveRoles(), StudentDataAccess::TEACHER_ROLES) === [] || ! ($year = AcademicYear::current())) {
            return false;
        }

        return Schedule::where('teacher_id', $user->id)->where('academic_year_id', $year->id)->where('classroom_id', $this->id)->exists()
            || ((int) $this->teacher_id === $user->id && $this->academic_year === $year->year);
    }

    /** @return BelongsTo<SchoolYear, $this> */
    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
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

    /** @return HasMany<ClassEnrollment, $this> */
    public function enrollments(): HasMany
    {
        return $this->hasMany(ClassEnrollment::class);
    }

    /**
     * Kurikulum Merdeka elective subjects configured for this Grade XI/XII classroom.
     *
     * @return BelongsToMany<Subject, $this>
     */
    public function electiveSubjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'classroom_subjects')->withTimestamps();
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
