<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GraduationRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'requirement_type',
        'description',
        'threshold_value',
        'is_mandatory',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
    ];

    public function studentProgresses(): HasMany
    {
        return $this->hasMany(StudentGraduationProgress::class);
    }
}
