<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DawahContent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['author_id', 'title', 'content_type', 'content_body', 'references', 'tags', 'status', 'published_at', 'view_count'];

    protected $casts = ['references' => 'array', 'tags' => 'array', 'published_at' => 'datetime'];

    public function author()
    {
        return $this->belongsTo(Employee::class, 'author_id');
    }
}
