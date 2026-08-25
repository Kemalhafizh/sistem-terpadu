<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Teacher360Metric extends Model
{
    use HasFactory;

    protected $table = 'teacher_360_metrics';

    protected $fillable = [
        'teacher_id',
        'academic_year_id',
        'student_satisfaction_score',
        'peer_review_score',
        'avg_class_grade',
        'attendance_percentage',
        'skill_radar_data',
    ];

    protected $casts = [
        'student_satisfaction_score' => 'decimal:2',
        'peer_review_score' => 'decimal:2',
        'avg_class_grade' => 'decimal:2',
        'attendance_percentage' => 'integer',
        'skill_radar_data' => 'array',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
