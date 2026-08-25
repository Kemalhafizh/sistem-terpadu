<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProgressSnapshot extends Model
{
    use HasFactory;

    protected $table = 'student_progress_snapshots';

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'academic_timeline',
        'competency_radar',
        'overall_percentile',
        'at_risk_status',
    ];

    protected $casts = [
        'academic_timeline' => 'array',
        'competency_radar' => 'array',
        'overall_percentile' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
