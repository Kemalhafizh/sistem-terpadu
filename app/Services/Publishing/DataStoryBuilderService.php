<?php

declare(strict_types=1);

namespace App\Services\Publishing;

use App\Models\DataStory;
use App\Models\User;
use Illuminate\Support\Str;

class DataStoryBuilderService
{
    /**
     * Build and save an interactive scrollytelling data story
     */
    public function saveStory(User $author, array $data, ?int $storyId = null): DataStory
    {
        $title = $data['title'] ?? 'Laporan Interaktif Capaian Sekolah';
        $slug = Str::slug($title) . '-' . time();
        $payload = [
            'title' => $title,
            'slug' => $slug,
            'summary' => $data['summary'] ?? 'Visualisasi data mendalam tentang perkembangan SMA Islam Sultan Agung 1.',
            'story_configuration' => $data['story_configuration'] ?? [
                'steps' => [
                    ['title' => 'Pertumbuhan Akademik', 'stat' => '100% Lulus PTN/Kedinasan', 'desc' => 'Peningkatan rata-rata UTBK 3 tahun berturut-turut.'],
                    ['title' => 'Capaian Tahfidz Al-Qur\'an', 'stat' => '320+ Siswa Hafidz 5-30 Juz', 'desc' => 'Program unggulan kepribadian Qur\'ani.'],
                ],
            ],
            'created_by' => $author->id,
            'is_published' => true,
        ];

        if ($storyId) {
            $story = DataStory::find($storyId);
            if ($story) {
                $story->update($payload);
                return $story;
            }
        }

        return DataStory::create($payload);
    }
}
