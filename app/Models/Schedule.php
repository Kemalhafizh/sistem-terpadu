<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'classroom_id',
        'subject_id',
        'teacher_id',
        'room_id',
        'day',
        'session_start',
        'session_end',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    /**
     * Does [teacher] teach [subject] in [classroom] in [academic year]?
     * A teaching journal (web or app) may only be filed for such a lesson.
     * Only superadmin passes without a schedule.
     */
    public static function teaches(User $teacher, int $classroomId, int $subjectId, int $academicYearId): bool
    {
        if ($teacher->role === 'superadmin') {
            return true;
        }

        return self::where('teacher_id', $teacher->id)
            ->where('classroom_id', $classroomId)
            ->where('subject_id', $subjectId)
            ->where('academic_year_id', $academicYearId)
            ->exists();
    }

    /**
     * The classrooms [teacher] may take attendance for and see the roster of:
     * the ones they have a lesson in this academic year, and the one they are
     * homeroom teacher of this year. Without an active year there is none.
     *
     * @return Collection<int, int>
     */
    public static function classroomIdsFor(User $teacher, ?AcademicYear $year): Collection
    {
        if (! $year) {
            return collect();
        }

        return self::where('teacher_id', $teacher->id)
            ->where('academic_year_id', $year->id)
            ->pluck('classroom_id')
            ->concat(Classroom::where('teacher_id', $teacher->id)->where('academic_year', $year->year)->pluck('id'))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }

    /** May [teacher] see the roster of and record attendance for [classroomId]? Superadmin always may. */
    public static function coversClassroom(User $teacher, int $classroomId, ?AcademicYear $year): bool
    {
        return $teacher->role === 'superadmin'
            || self::classroomIdsFor($teacher, $year)->contains($classroomId);
    }
}
