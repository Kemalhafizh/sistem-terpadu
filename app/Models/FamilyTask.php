<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'assignment_id',
        'parent_guide_notes',
        'estimated_parent_time_minutes',
        'parent_reviewed',
        'parent_feedback',
    ];

    protected $casts = [
        'parent_reviewed' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
