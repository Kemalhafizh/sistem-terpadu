<?php

namespace App\Notifications;

use App\Models\ProfileChangeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProfileChangeRequestNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ProfileChangeRequest $changeRequest,
        public string $event = 'submitted',
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $this->changeRequest->loadMissing(['requester', 'targetUser']);
        $targetName = $this->changeRequest->targetUser?->name
            ?? $this->changeRequest->requester?->name
            ?? __('Pengguna');
        $fieldLabel = $this->changeRequest->field_label ?: $this->changeRequest->field_name;

        [$title, $message, $url] = match ($this->event) {
            'approved' => [
                __("Perubahan {$fieldLabel} Disetujui"),
                __("Pengajuan perubahan {$fieldLabel} untuk {$targetName} telah disetujui Tata Usaha."),
                '/profile/change-requests',
            ],
            'rejected' => [
                __("Perubahan {$fieldLabel} Ditolak"),
                __("Pengajuan perubahan {$fieldLabel} untuk {$targetName} ditolak: ".($this->changeRequest->review_notes ?? '-')),
                '/profile/change-requests',
            ],
            'cancelled' => [
                __("Pengajuan Perubahan {$fieldLabel} Dibatalkan"),
                __("Pengaju membatalkan permintaan perubahan {$fieldLabel} untuk {$targetName}."),
                '/admin/profile-change-requests',
            ],
            'escalated' => [
                __("Eskalasi Verifikasi Perubahan Data ({$fieldLabel})"),
                __("Pengajuan perubahan {$fieldLabel} untuk {$targetName} menunggu verifikasi TU melebihi tenggat waktu."),
                '/admin/profile-change-requests',
            ],
            default => [
                __("Pengajuan Perubahan Data: {$fieldLabel}"),
                __("{$targetName} mengajukan perubahan {$fieldLabel} menjadi \"{$this->changeRequest->new_value}\"."),
                '/admin/profile-change-requests',
            ],
        };

        return [
            'title' => $title,
            'message' => $message,
            'profile_change_request_id' => $this->changeRequest->id,
            'profile_change_request_uuid' => $this->changeRequest->uuid,
            'target_type' => $this->changeRequest->target_type,
            'target_id' => $this->changeRequest->target_id,
            'field_name' => $this->changeRequest->field_name,
            'status' => $this->changeRequest->status,
            'event' => $this->event,
            'action_url' => $url,
        ];
    }
}
