<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'student_id',
        'academic_year_id',
        'classroom_id',
        'schedule_id',
        'date',
        'status',
        'notes',
        'recorded_by',
        'student_leave_request_id',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
