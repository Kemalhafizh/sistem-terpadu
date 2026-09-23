<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParentMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sender_id' => $this->sender_id,
            'sender_name' => $this->sender->name ?? 'System',
            'sender_role' => $this->sender->role ?? 'N/A',
            'recipient_id' => $this->recipient_id,
            'recipient_name' => $this->recipient->name ?? 'System',
            'recipient_role' => $this->recipient->role ?? 'N/A',
            'student_id' => $this->student_id,
            'student_uuid' => $this->student->uuid ?? null,
            'student_name' => $this->student->user->name ?? 'N/A',
            'message' => $this->message,
            'attachment_url' => $this->attachment_path ? \Illuminate\Support\Facades\Storage::url($this->attachment_path) : null,
            'attachment_type' => $this->attachment_type,
            'is_read' => (bool) $this->is_read,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'created_at_human' => $this->created_at ? $this->created_at->diffForHumans() : null,
        ];
    }
}
