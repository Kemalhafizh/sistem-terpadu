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
            'subject_id' => $this->subject_id,
            // A missing relation (deleted subject, removed teacher) is null, not
            // "N/A": the app shows "–" for it instead of a fake name.
            'subject_name' => $this->subject?->name,
            'subject_code' => $this->subject?->code,
            'teacher_name' => $this->teacher?->name,
            'classroom_id' => $this->classroom_id,
            'classroom_name' => $this->classroom?->name,
            'room_name' => $this->room?->name,
        ];
    }
}
