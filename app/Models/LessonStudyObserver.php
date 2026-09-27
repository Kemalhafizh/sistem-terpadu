<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonStudyObserver extends Model
{
    use HasFactory;

    protected $fillable = ['lesson_study_id', 'observer_id', 'observation_notes', 'student_engagement_score', 'teaching_quality_score', 'material_clarity_score', 'strengths', 'areas_for_improvement', 'suggestions'];

    public function lessonStudy()
    {
        return $this->belongsTo(LessonStudy::class);
    }

    public function observer()
    {
        return $this->belongsTo(User::class, 'observer_id');
    }
}
