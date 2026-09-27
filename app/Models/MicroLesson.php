<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MicroLesson extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['syllabus_id', 'title', 'ai_generated_summary', 'estimated_minutes', 'is_published', 'metadata'];

    protected $casts = [
        'is_published' => 'boolean',
        'metadata' => 'array',
    ];

    public function cards()
    {
        return $this->hasMany(MicroLessonCard::class);
    }
}
