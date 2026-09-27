<?php

namespace App\Http\Resources;

use App\Models\ClassSession;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * FASE 117 / Android FASE 77 `ClassSessionResponse` (snake_case, Gson @SerializedName).
 *
 * Expects the session loaded through ClassSessionService::withListData()
 * (subject/classroom/teacher + total_students/present_count/absent_count).
 *
 * @mixin ClassSession
 */
class ClassSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ClassSession $session */
        $session = $this->resource;
        $now = now();

        return [
            'id' => $session->id,
            'uuid' => $session->uuid,
            'schedule_id' => $session->schedule_id,
            'subject_id' => $session->subject_id,
            'subject_name' => $session->subject?->name,
            'classroom_id' => $session->classroom_id,
            'classroom_name' => $session->classroom?->name,
            'teacher_id' => $session->teacher_id,
            'teacher_name' => $session->teacher?->name,
            'session_date' => $session->session_date,
            'jam_ke' => $session->jam_ke,
            'scheduled_start' => substr((string) $session->scheduled_start, 0, 5),
            'scheduled_end' => substr((string) $session->scheduled_end, 0, 5),
            'startable_from' => $session->startableFrom()->format('H:i'),
            'auto_close_at' => $session->autoCloseAt()->toIso8601String(),
            'actual_start' => $session->actual_start?->toIso8601String(),
            'actual_end' => $session->actual_end?->toIso8601String(),
            'status' => $session->status,
            'can_start' => $session->status === ClassSession::STATUS_SCHEDULED && $session->isWithinScheduledTime($now),
            'topic' => $session->topic,
            'notes' => $session->notes,
            'total_students' => (int) ($session->total_students ?? 0),
            'present_count' => (int) ($session->present_count ?? 0),
            'absent_count' => (int) ($session->absent_count ?? 0),
            'is_auto_closed' => (bool) $session->is_auto_closed,
            'teaching_journal_id' => $session->teaching_journal_id,
        ];
    }
}
