<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahfidzTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'target_juz',
        'target_pages',
        'achieved_juz',
        'achieved_pages',
        'status',
        'notes',
    ];

    protected $casts = [
        'target_juz' => 'integer',
        'target_pages' => 'integer',
        'achieved_juz' => 'integer',
        'achieved_pages' => 'integer',
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
