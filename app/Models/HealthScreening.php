<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HealthScreening extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'screening_date',
        'academic_year_id',
        'target_classrooms',
        'status',
        'notes',
    ];

    protected $casts = [
        'screening_date' => 'date',
        'target_classrooms' => 'array',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(HealthScreeningResult::class, 'screening_id');
    }
}
