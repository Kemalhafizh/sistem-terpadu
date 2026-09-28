<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElearningAssignment extends Model
{
    use HasUuid;

    protected $fillable = [
        'uuid',
        'elearning_class_id',
        'title',
        'description',
        'due_at',
        'allow_late',
        'is_secure_mode',
        'max_violations',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'allow_late' => 'boolean',
        'is_secure_mode' => 'boolean',
        'max_violations' => 'integer',
    ];

    public function elearningClass(): BelongsTo
    {
        return $this->belongsTo(ElearningClass::class, 'elearning_class_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(ElearningSubmission::class);
    }

    public function isOverdue()
    {
        return $this->due_at->isPast();
    }

    public function studentSubmission(Student $student)
    {
        return $this->submissions()->where('student_id', $student->id)->first();
    }
}
