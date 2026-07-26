<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentGraduationProgress extends Model
{
    use HasFactory;

    protected $table = 'student_graduation_progress';

    protected $fillable = [
        'student_id',
        'graduation_requirement_id',
        'current_value',
        'is_fulfilled',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'is_fulfilled' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(GraduationRequirement::class, 'graduation_requirement_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
