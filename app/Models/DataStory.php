<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataStory extends Model
{
    use HasFactory;

    protected $table = 'data_stories';

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'story_configuration',
        'created_by',
        'is_published',
    ];

    protected $casts = [
        'story_configuration' => 'array',
        'is_published' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
