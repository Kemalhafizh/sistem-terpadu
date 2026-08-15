<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyLearningPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'academic_term',
        'focus_area',
        'school_action_items',
        'home_action_items',
        'progress_status',
        'next_checkin_date',
    ];

    protected $casts = [
        'school_action_items' => 'array',
        'home_action_items' => 'array',
        'next_checkin_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
