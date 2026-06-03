<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'status' => $this->status,
            'status_label' => $this->getLabel($this->status),
            'notes' => $this->notes,
            'recorded_by' => $this->recordedBy->name ?? 'System',
        ];
    }

    private function getLabel($status)
    {
        return [
            'H' => 'Hadir',
            'S' => 'Sakit',
            'I' => 'Izin',
            'A' => 'Alpa',
        ][$status] ?? $status;
    }
}
