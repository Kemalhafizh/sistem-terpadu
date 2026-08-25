<?php

declare(strict_types=1);

namespace App\Services\Publishing;

use App\Models\DigitalMagazine;
use App\Models\MagazineSubscriber;
use App\Models\User;
use Illuminate\Support\Str;

class DigitalMagazinePublisherService
{
    /**
     * Save digital magazine blocks and publish
     */
    public function saveMagazine(User $author, array $data, ?int $magazineId = null): DigitalMagazine
    {
        $title = $data['title'] ?? 'Edisi Majalah Sultan Agung 1';
        $slug = Str::slug($title) . '-' . time();
        $payload = [
            'title' => $title,
            'slug' => $slug,
            'issue_number' => $data['issue_number'] ?? 1,
            'content_blocks' => $data['content_blocks'] ?? [],
            'cover_image_path' => $data['cover_image_path'] ?? null,
            'status' => $data['status'] ?? 'published',
            'published_at' => now(),
            'author_id' => $author->id,
        ];

        if ($magazineId) {
            $magazine = DigitalMagazine::find($magazineId);
            if ($magazine) {
                $magazine->update($payload);
                return $magazine;
            }
        }

        return DigitalMagazine::create($payload);
    }

    /**
     * Subscribe email to magazine newsletter
     */
    public function subscribe(string $email, ?User $user = null): MagazineSubscriber
    {
        return MagazineSubscriber::updateOrCreate(
            ['email' => $email],
            [
                'user_id' => $user?->id,
                'is_active' => true,
                'subscribed_at' => now(),
            ]
        );
    }
}
