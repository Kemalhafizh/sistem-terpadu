<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlcDiscussionReply extends Model
{
    use HasFactory;

    protected $fillable = ['discussion_id', 'author_id', 'content', 'is_best_answer'];

    protected $casts = ['is_best_answer' => 'boolean'];

    public function discussion()
    {
        return $this->belongsTo(PlcDiscussion::class, 'discussion_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
