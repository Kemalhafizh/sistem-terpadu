<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlcDiscussion extends Model
{
    use HasFactory;

    protected $fillable = ['uuid', 'plc_group_id', 'author_id', 'title', 'content', 'is_pinned', 'reply_count'];

    protected $casts = ['is_pinned' => 'boolean'];

    public function group()
    {
        return $this->belongsTo(PlcGroup::class, 'plc_group_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function replies()
    {
        return $this->hasMany(PlcDiscussionReply::class, 'discussion_id');
    }
}
