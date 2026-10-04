<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
