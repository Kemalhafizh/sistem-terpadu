<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Maps a Laravel Notification class to one of Android's 5 registered
     * NotificationChannelType channel IDs (core/common's
     * NotificationChannelManager) — an unmapped type falls back to the
     * general channel rather than a fabricated/guessed one.
     */
    private const CHANNEL_MAP = [
        'StudentAttendanceNotification' => 'attendance_alerts',
        'BillingReminderNotification' => 'financial_reminders',
        'EventReminderNotification' => 'general_info',
        'StudentPointNotification' => 'general_info',
        'TicketCreatedNotification' => 'general_info',
        'TicketStatusUpdatedNotification' => 'general_info',
        'TwoFactorCodeNotification' => 'general_info',
        'UksVisitNotification' => 'general_info',
    ];

    /**
     * Maps a Laravel Notification class to a Screen route the Android app
     * actually has (ui/navigation/Screen.kt) — left null rather than
     * guessed when no matching screen exists, so DeepLinkRouter doesn't
     * get sent somewhere wrong.
     */
    private const DEEP_LINK_MAP = [
        'BillingReminderNotification' => 'billing',
        'StudentPointNotification' => 'discipline',
        'UksVisitNotification' => 'uks_visit',
    ];

    public function toArray(Request $request): array
    {
        $type = class_basename($this->type);

        return [
            'id' => (string) $this->id,
            'title' => $this->data['title'] ?? 'Notifikasi',
            'body' => $this->data['message'] ?? '',
            'channel' => self::CHANNEL_MAP[$type] ?? 'general_info',
            'deep_link_route' => self::DEEP_LINK_MAP[$type] ?? null,
            'timestamp' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'is_read' => $this->read_at !== null,
        ];
    }
}
