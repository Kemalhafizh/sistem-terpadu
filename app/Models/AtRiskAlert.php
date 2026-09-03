<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtRiskAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'risk_level',
        'trigger_reason',
        'unexcused_absences',
        'violation_points',
        'current_gpa',
        'status',
        'bk_counselor_notes',
        'assigned_bk_teacher_id',
        'resolved_at',
    ];

    protected $casts = [
        'current_gpa' => 'decimal:2',
        'resolved_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function assignedBkTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_bk_teacher_id');
    }
}
