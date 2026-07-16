<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OsisMember extends Model
{
    protected $fillable = [
        'student_id',
        'position',
        'academic_year_id',
        'status',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
