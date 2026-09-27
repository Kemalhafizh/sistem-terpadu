<?php

declare(strict_types=1);

namespace App\Services\Publishing;

use App\Models\Podcast;
use App\Models\PodcastEpisode;
use App\Models\User;
use Illuminate\Support\Str;

class PodcastDistributionService
{
    /**
     * Create a new podcast channel
     */
    public function createChannel(User $host, array $data): Podcast
    {
        return Podcast::create([
            'title' => $data['title'],
            'slug' => Str::slug($data['title']).'-'.time(),
            'description' => $data['description'] ?? '',
            'cover_image_path' => $data['cover_image_path'] ?? '/images/podcast-cover.jpg',
            'category' => $data['category'] ?? 'islamic_studies',
            'host_id' => $host->id,
        ]);
    }

    /**
     * Publish a new podcast episode
     */
    public function publishEpisode(Podcast $podcast, array $data): PodcastEpisode
    {
        return PodcastEpisode::create([
            'podcast_id' => $podcast->id,
            'title' => $data['title'],
            'audio_file_path' => $data['audio_file_path'] ?? '/storage/audio/episode.mp3',
            'duration_seconds' => $data['duration_seconds'] ?? 1200,
            'show_notes' => $data['show_notes'] ?? null,
            'published_at' => now(),
        ]);
    }
}
