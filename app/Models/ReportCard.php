<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportCard extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'classroom_id',
        'total_score',
        'average_score',
        'rank_in_class',
        'homeroom_notes',
        'extracurricular_notes',
        'recorded_by',
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
}
