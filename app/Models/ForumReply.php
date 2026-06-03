<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForumReply extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'thread_id',
        'user_id',
        'parent_id',
        'body',
        'attachment_path',
        'is_best_answer',
    ];

    protected $casts = [
        'is_best_answer' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($reply) {
            $thread = $reply->thread;
            if ($thread) {
                $thread->increment('reply_count');
                $thread->update(['last_reply_at' => $reply->created_at]);
            }
        });

        static::deleted(function ($reply) {
            $thread = $reply->thread;
            if ($thread) {
                $thread->decrement('reply_count');
            }
        });
    }

    public function thread()
    {
        return $this->belongsTo(ForumThread::class, 'thread_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(ForumReply::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ForumReply::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    public function reactions()
    {
        return $this->morphMany(ForumReaction::class, 'reactable');
    }
}
