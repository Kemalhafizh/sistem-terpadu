<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticalStudentKpi extends Model
{
    use HasFactory;

    protected $table = 'analytical_student_kpis';

    protected $fillable = [
        'student_id',
        'date',
        'attendance_percentage',
        'average_grade',
        'mutabaah_score',
        'tahfidz_juz_count',
        'penalty_points',
    ];

    protected $casts = [
        'date' => 'date',
        'attendance_percentage' => 'decimal:2',
        'average_grade' => 'decimal:2',
        'mutabaah_score' => 'integer',
        'tahfidz_juz_count' => 'integer',
        'penalty_points' => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
