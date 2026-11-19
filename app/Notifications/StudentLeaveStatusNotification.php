<?php

namespace App\Notifications;

use App\Models\StudentLeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StudentLeaveStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public StudentLeaveRequest $leaveRequest,
        public string $event = 'submitted',
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $this->leaveRequest->loadMissing('student.user');
        $studentName = $this->leaveRequest->student?->user?->name ?? __('Siswa');
        $typeLabel = $this->leaveRequest->type === StudentLeaveRequest::TYPE_SAKIT ? __('Sakit') : __('Izin');
        $range = $this->leaveRequest->start_date?->format('d/m/Y')
            .(($this->leaveRequest->days_count ?? 1) > 1
                ? ' - '.$this->leaveRequest->end_date?->format('d/m/Y')
                : '');

        [$title, $message, $url] = match ($this->event) {
            'auto_approved' => [
                __("Izin {$typeLabel} Disetujui Otomatis"),
                __("Pengajuan {$typeLabel} untuk {$studentName} ({$range}) telah disetujui otomatis."),
                '/parent-portal/leave-requests',
            ],
            'approved' => [
                __("Izin {$typeLabel} Disetujui"),
                __("Pengajuan {$typeLabel} untuk {$studentName} ({$range}) disetujui wali kelas/kesiswaan."),
                '/parent-portal/leave-requests',
            ],
            'rejected' => [
                __("Izin {$typeLabel} Ditolak"),
                __("Pengajuan {$typeLabel} untuk {$studentName} ({$range}) ditolak: ".($this->leaveRequest->decision_notes ?? '-')),
                '/parent-portal/leave-requests',
            ],
            'revoked' => [
                __("Persetujuan Izin {$typeLabel} Dicabut"),
                __("Persetujuan izin {$typeLabel} untuk {$studentName} ({$range}) dicabut: ".($this->leaveRequest->decision_notes ?? '-')),
                '/parent-portal/leave-requests',
            ],
            'cancelled' => [
                __("Pengajuan Izin {$typeLabel} Dibatalkan Orang Tua"),
                __("Orang tua membatalkan pengajuan {$typeLabel} untuk {$studentName} ({$range})."),
                '/teacher/leave-requests',
            ],
            'escalated' => [
                __("Eskalasi Persetujuan Izin {$typeLabel}"),
                __("Pengajuan {$typeLabel} untuk {$studentName} ({$range}) melewati batas waktu tindak lanjut wali kelas."),
                '/teacher/leave-requests',
            ],
            default => [
                __("Pengajuan Izin {$typeLabel} Baru"),
                __("Wali murid mengajukan {$typeLabel} untuk {$studentName} ({$range})."),
                '/teacher/leave-requests',
            ],
        };

        return [
            'title' => $title,
            'message' => $message,
            'leave_request_id' => $this->leaveRequest->id,
            'leave_request_uuid' => $this->leaveRequest->uuid,
            'student_id' => $this->leaveRequest->student_id,
            'student_name' => $studentName,
            'type' => $this->leaveRequest->type,
            'status' => $this->leaveRequest->status,
            'start_date' => $this->leaveRequest->start_date?->toDateString(),
            'end_date' => $this->leaveRequest->end_date?->toDateString(),
            'event' => $this->event,
            'action_url' => $url,
        ];
    }
}
