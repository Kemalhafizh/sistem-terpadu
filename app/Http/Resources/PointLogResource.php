<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PointLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category->name ?? 'Lainnya',
            'points' => (int) $this->points,
            'type' => $this->type, // penalty or reward
            'description' => $this->description,
            'date' => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}
