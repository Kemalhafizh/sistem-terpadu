<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonStudy extends Model
{
    use HasFactory;

    protected $fillable = ['uuid', 'title', 'model_teacher_id', 'subject_id', 'classroom_id', 'phase', 'session_date', 'start_time', 'end_time', 'learning_objectives', 'plan_notes', 'observation_focus', 'reflection_summary', 'improvement_action', 'rpp_file_path', 'video_recording_path', 'status'];

    protected $casts = ['session_date' => 'date', 'start_time' => 'datetime', 'end_time' => 'datetime'];

    public function modelTeacher()
    {
        return $this->belongsTo(User::class, 'model_teacher_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function observers()
    {
        return $this->hasMany(LessonStudyObserver::class);
    }
}
