<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MicroLessonCard extends Model
{
    use HasFactory;

    protected $fillable = ['micro_lesson_id', 'sequence_order', 'card_type', 'content', 'ai_prompts_used'];

    protected $casts = [
        'ai_prompts_used' => 'array',
    ];

    public function lesson()
    {
        return $this->belongsTo(MicroLesson::class, 'micro_lesson_id');
    }
}
