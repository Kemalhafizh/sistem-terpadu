<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForumThread extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'title',
        'body',
        'user_id',
        'classroom_id',
        'subject_id',
        'elearning_class_id',
        'scope',
        'is_pinned',
        'is_locked',
        'is_announcement',
        'attachment_path',
        'reply_count',
        'last_reply_at',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_locked' => 'boolean',
        'is_announcement' => 'boolean',
        'last_reply_at' => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function elearningClass()
    {
        return $this->belongsTo(ElearningClass::class, 'elearning_class_id');
    }

    public function replies()
    {
        return $this->hasMany(ForumReply::class, 'thread_id')->orderBy('created_at', 'asc');
    }

    public function reactions()
    {
        return $this->morphMany(ForumReaction::class, 'reactable');
    }

    public function scopeForClassroom($query, $classroomId)
    {
        return $query->where('classroom_id', $classroomId);
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopePopular($query)
    {
        return $query->orderBy('reply_count', 'desc');
    }
}
