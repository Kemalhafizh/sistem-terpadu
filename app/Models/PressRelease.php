<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PressRelease extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'release_date',
        'distributed_to',
        'status',
        'media_pickup_count',
        'author_id',
    ];

    protected $casts = [
        'release_date' => 'date',
        'distributed_to' => 'array',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
