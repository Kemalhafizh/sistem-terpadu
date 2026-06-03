<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->user->name ?? 'N/A',
            'nis' => $this->nis,
            'nisn' => $this->nisn,
            'classroom' => $this->classroom->name ?? 'N/A',
            'grade_level' => $this->classroom->grade_level ?? 'N/A',
            'major' => $this->classroom->major ?? 'N/A',
            'gender' => $this->gender === 'L' ? 'Laki-laki' : 'Perempuan',
            'address' => $this->address,
            'status' => $this->status,
            'total_points' => $this->total_points ?? 0,
        ];
    }
}
