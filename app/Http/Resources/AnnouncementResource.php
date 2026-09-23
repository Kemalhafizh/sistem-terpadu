<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AnnouncementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            // Android's AnnouncementItem needs a short preview distinct from
            // the full body — there's no separate `summary` column, so this
            // derives one from the real content rather than fabricating text.
            'summary' => Str::limit(strip_tags((string) $this->content), 150),
            'content' => $this->content,
            'category' => $this->category ?: 'Umum',
            'author' => $this->creator->name ?? 'Admin',
            'date' => $this->created_at->diffForHumans(),
            'priority' => $this->priority ?: 'normal',
            'cover_image_url' => $this->image_path ? Storage::url($this->image_path) : null,
            'attachment_url' => $this->attachment_path ? Storage::url($this->attachment_path) : null,
        ];
    }
}
