<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'priority' => $this->priority,
            'image_url' => $this->image_path ? asset('storage/'.$this->image_path) : null,
            'date' => $this->created_at->diffForHumans(),
            'author' => $this->creator->name ?? 'Admin',
        ];
    }
}
