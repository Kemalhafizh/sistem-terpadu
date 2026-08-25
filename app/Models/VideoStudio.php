<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoStudio extends Model
{
    use HasFactory;

    protected $table = 'video_studios';

    protected $fillable = [
        'title',
        'description',
        'original_file_path',
        'hls_playlist_paths',
        'thumbnail_path',
        'duration_seconds',
        'transcoding_status',
        'view_count',
        'uploader_id',
    ];

    protected $casts = [
        'hls_playlist_paths' => 'array',
        'duration_seconds' => 'integer',
        'view_count' => 'integer',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }
}
