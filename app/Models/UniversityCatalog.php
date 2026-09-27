<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UniversityCatalog extends Model
{
    use HasFactory;

    protected $fillable = [
        'university_name', 'major', 'passing_grade_index', 'alumni_acceptance_history', 'city', 'accreditation',
    ];

    protected $casts = [
        'passing_grade_index' => 'decimal:2',
        'alumni_acceptance_history' => 'array',
    ];

    public function applications()
    {
        return $this->hasMany(CollegeApplication::class, 'university_catalog_id');
    }
}
