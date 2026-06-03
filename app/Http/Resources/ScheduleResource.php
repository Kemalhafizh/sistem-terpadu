<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'day' => $this->day,
            'session_start' => $this->session_start,
            'session_end' => $this->session_end,
            'subject_name' => $this->subject->name ?? 'N/A',
            'subject_code' => $this->subject->code ?? 'N/A',
            'teacher_name' => $this->teacher->name ?? 'N/A',
            'classroom_name' => $this->classroom->name ?? 'N/A',
        ];
    }
}
