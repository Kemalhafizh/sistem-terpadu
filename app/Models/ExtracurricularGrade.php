<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtracurricularGrade extends Model
{
    protected $fillable = [
        'extracurricular_member_id',
        'academic_year_id',
        'grade',
        'description',
    ];

    public function member()
    {
        return $this->belongsTo(ExtracurricularMember::class, 'extracurricular_member_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
