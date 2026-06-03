<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TahfidzLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'surah_name' => $this->surah_name,
            'surah_number' => $this->surah_number,
            'from_ayat' => $this->from_ayat,
            'to_ayat' => $this->to_ayat,
            'type' => $this->type, // ziyadah or murajaah
            'grade' => $this->grade,
            'notes' => $this->notes,
            'date' => $this->date,
            'teacher' => $this->teacher->name ?? 'Ustaz/Ustazah',
        ];
    }
}
