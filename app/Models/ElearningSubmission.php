<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElearningSubmission extends Model
{
    protected $fillable = [
        'elearning_assignment_id',
        'student_id',
        'content',
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

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ElearningAssignment::class, 'elearning_assignment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
