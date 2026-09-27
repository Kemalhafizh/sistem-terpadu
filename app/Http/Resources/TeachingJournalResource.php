<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeachingJournalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'teaching_date' => $this->teaching_date ? $this->teaching_date->format('Y-m-d') : null,
            'jam_ke' => $this->jam_ke,
            'classroom_id' => $this->classroom_id,
            'classroom_name' => $this->classroom->name ?? 'N/A',
            'subject_id' => $this->subject_id,
            'subject_name' => $this->subject->name ?? 'N/A',
            'topic' => $this->topic,
            'learning_activity' => $this->learning_activity,
            'learning_method' => $this->learning_method,
            'obstacles' => $this->obstacles,
            'notes' => $this->notes,
            'students_present' => $this->students_present,
            'students_absent' => $this->students_absent,
            'attachment_url' => $this->attachment_path ? asset('storage/'.$this->attachment_path) : null,
            'status' => $this->status,
            'reviewer_notes' => $this->reviewer_notes,
            'reviewer_name' => $this->reviewer->name ?? null,
            'reviewed_at' => $this->reviewed_at ? $this->reviewed_at->toIso8601String() : null,
        ];
    }
}
