<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassFeedPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'target_audience',
        'content',
        'media_urls',
        'post_type',
        'requires_acknowledgment',
    ];

    protected $casts = [
        'target_audience' => 'array',
        'media_urls' => 'array',
        'requires_acknowledgment' => 'boolean',
    ];

    public function teacher()
    {
        return $this->belongsTo(Employee::class, 'teacher_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
