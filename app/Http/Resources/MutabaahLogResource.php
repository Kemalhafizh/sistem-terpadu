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
            'item_name' => $this->item->name ?? 'Amalan Harian',
            'category' => $this->item->category ?? 'Harian',
            'is_completed' => (bool) ($this->mutabaah_item_id ? $this->is_completed : ($this->score >= 80)),
            'date' => $this->date,
            'score' => $this->score,
            'sholat_subuh' => $this->sholat_subuh,
            'sholat_dzuhur' => $this->sholat_dzuhur,
            'sholat_ashar' => $this->sholat_ashar,
            'sholat_maghrib' => $this->sholat_maghrib,
            'sholat_isya' => $this->sholat_isya,
            'sholat_dhuha' => $this->sholat_dhuha,
            'sholat_tahajud' => $this->sholat_tahajud,
            'tadarus_pages' => $this->tadarus_pages,
            'puasa_sunnah' => (bool) $this->puasa_sunnah,
            'sedekah' => (bool) $this->sedekah,
            'dzikir_pagi' => (bool) $this->dzikir_pagi,
            'dzikir_sore' => (bool) $this->dzikir_sore,
            'catatan_harian' => $this->catatan_harian,
        ];
    }
}
