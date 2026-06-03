<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MutabaahLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'item_name' => $this->item->name ?? 'N/A',
            'category' => $this->item->category ?? 'N/A',
            'is_completed' => (bool) $this->is_completed,
            'date' => $this->date,
        ];
    }
}
