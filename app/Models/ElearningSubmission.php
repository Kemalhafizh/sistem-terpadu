<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ElearningSubmission extends Model
{
    protected $fillable = [
        'elearning_assignment_id',
        'student_id',
        'submitted_at',
        'file_path',
        'score',
        'feedback',
        'status',
        'violation_count',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'score' => 'decimal:2',
        'violation_count' => 'integer',
    ];

    public function assignment()
    {
        return $this->belongsTo(ElearningAssignment::class, 'elearning_assignment_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
