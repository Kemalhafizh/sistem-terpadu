<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject->name ?? 'N/A',
            'type' => strtoupper($this->type),
            'score' => (float) $this->score,
            'description' => $this->description,
            'date' => $this->created_at->format('Y-m-d'),
        ];
    }
}
