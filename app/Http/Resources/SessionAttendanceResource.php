<?php

namespace App\Http\Resources;

use App\Models\SessionAttendance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * FASE 117 / Android FASE 77 `SessionAttendanceResponse`.
 *
 * @mixin SessionAttendance
 */
class SessionAttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var SessionAttendance $attendance */
        $attendance = $this->resource;

        return [
            'id' => $attendance->id,
            'class_session_id' => $attendance->class_session_id,
            'student_id' => $attendance->student_id,
            'student_name' => $attendance->student?->user?->name,
            'student_nis' => $attendance->student?->nis,
            'status' => $attendance->status,
            'check_in_method' => $attendance->check_in_method,
            'checked_in_at' => $attendance->checked_in_at?->toIso8601String(),
            'notes' => $attendance->notes,
            'is_override' => (bool) $attendance->is_override,
            'override_by' => $attendance->overrider?->name,
            'override_at' => $attendance->override_at?->toIso8601String(),
            'override_reason' => $attendance->override_reason,
        ];
    }
}
