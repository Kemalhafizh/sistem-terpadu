<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionBank extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subject_id',
        'created_by',
        'question_text',
        'question_image',
        'type',
        'options',
        'correct_answer',
        'explanation',
        'difficulty',
        'competency_code',
        'grade_level',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_questions')
            ->withPivot('order', 'weight')
            ->withTimestamps();
    }
}
