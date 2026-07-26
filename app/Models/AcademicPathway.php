<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicPathway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'required_subjects',
        'required_credits',
        'is_active',
    ];

    protected $casts = [
        'required_subjects' => 'array',
        'is_active' => 'boolean',
    ];

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentPathwayEnrollment::class);
    }
}
