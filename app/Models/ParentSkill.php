<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentSkill extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'parent_name',
        'profession',
        'skill_tags',
        'available_for_guest_lecture',
        'available_for_mentorship',
        'past_contributions',
    ];

    protected $casts = [
        'skill_tags' => 'array',
        'available_for_guest_lecture' => 'boolean',
        'available_for_mentorship' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
