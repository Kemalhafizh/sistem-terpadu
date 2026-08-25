<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalMagazine extends Model
{
    use HasFactory;

    protected $table = 'digital_magazines';

    protected $fillable = [
        'title',
        'slug',
        'issue_number',
        'content_blocks',
        'cover_image_path',
        'status',
        'published_at',
        'author_id',
    ];

    protected $casts = [
        'issue_number' => 'integer',
        'content_blocks' => 'array',
        'published_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
