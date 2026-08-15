<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlumniProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'graduation_year',
        'current_company',
        'job_title',
        'industry',
        'linkedin_url',
        'is_willing_to_mentor',
        'contact_preferences',
    ];

    protected $casts = [
        'is_willing_to_mentor' => 'boolean',
        'contact_preferences' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function achievements()
    {
        return $this->hasMany(AlumniAchievement::class);
    }
}
