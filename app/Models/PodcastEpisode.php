<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PodcastEpisode extends Model
{
    use HasFactory;

    protected $table = 'podcast_episodes';

    protected $fillable = [
        'podcast_id',
        'title',
        'audio_file_path',
        'duration_seconds',
        'show_notes',
        'listen_count',
        'published_at',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
        'listen_count' => 'integer',
        'published_at' => 'datetime',
    ];

    public function podcast(): BelongsTo
    {
        return $this->belongsTo(Podcast::class);
    }
}
