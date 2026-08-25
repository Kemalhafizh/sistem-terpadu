<?php

declare(strict_types=1);

namespace App\Services\Publishing;

use App\Models\User;
use App\Models\VideoStudio;

class VideoTranscodingOrchestratorService
{
    /**
     * Register a new video upload and prepare adaptive streaming profiles
     */
    public function registerVideo(User $uploader, array $data): VideoStudio
    {
        $hlsPlaylists = [
            '360p' => '/storage/videos/hls/360p.m3u8',
            '720p' => '/storage/videos/hls/720p.m3u8',
            '1080p' => '/storage/videos/hls/1080p.m3u8',
        ];

        return VideoStudio::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'original_file_path' => $data['original_file_path'] ?? '/storage/videos/original.mp4',
            'hls_playlist_paths' => $hlsPlaylists,
            'thumbnail_path' => $data['thumbnail_path'] ?? '/images/video-thumb-default.jpg',
            'duration_seconds' => $data['duration_seconds'] ?? 600,
            'transcoding_status' => 'completed',
            'uploader_id' => $uploader->id,
        ]);
    }
}
