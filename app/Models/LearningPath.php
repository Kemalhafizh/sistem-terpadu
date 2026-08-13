<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningPath extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'title',
        'diagnosis',
        'weak_competencies',
        'recommended_resources',
        'status',
        'progress_percentage',
        'pre_score',
        'post_score',
    ];

    protected $casts = [
        'weak_competencies' => 'array',
        'recommended_resources' => 'array',
        'progress_percentage' => 'integer',
        'pre_score' => 'float',
        'post_score' => 'float',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function items()
    {
        return $this->hasMany(LearningPathItem::class);
    }
}
