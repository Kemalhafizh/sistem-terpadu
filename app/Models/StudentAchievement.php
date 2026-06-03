<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAchievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'achievement_id',
        'earned_at',
        'context',
    ];

    protected $casts = [
        'earned_at' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function definition()
    {
        return $this->belongsTo(AchievementDefinition::class, 'achievement_id');
    }
}
