<?php

namespace App\Notifications\Channels;

use App\Models\Setting;
use App\Models\User;
use App\Models\WhatsappLog;
use App\Services\WhatsappService;
use Illuminate\Notifications\Notification;

class WhatsappChannel
{
    protected $whatsappService;

    public function __construct(WhatsappService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @return WhatsappLog|null
     */
    public function send($notifiable, Notification $notification)
    {
        // Get the phone number from the notifiable user
        $to = $notifiable->phone_number;

        // If the notifiable has a parent relationship (like a Student who gets notified via their parent user)
        if (! $to && method_exists($notifiable, 'parent')) {
            $to = $notifiable->parent?->phone_number;
        }

        // If still no phone number, try to check if it's a student and we can fetch it directly
        if (! $to && isset($notifiable->parent_id)) {
            $parent = User::find($notifiable->parent_id);
            $to = $parent?->phone_number;
        }

        if (! $to) {
            return null;
        }

        // Call notification custom payload generator for WhatsApp
        if (! method_exists($notification, 'toWhatsapp')) {
            return null;
        }

        $message = $notification->toWhatsapp($notifiable);
        $type = class_basename($notification);

        // Check if enabled in settings
        if ($type === 'StudentAttendanceNotification' && Setting::get('whatsapp_enable_attendance', '1') !== '1') {
            return null;
        }
        if ($type === 'StudentPointNotification' && Setting::get('whatsapp_enable_point', '1') !== '1') {
            return null;
        }
        if ($type === 'BillingReminderNotification' && Setting::get('whatsapp_enable_billing', '1') !== '1') {
            return null;
        }

        return $this->whatsappService->sendMessage($to, $message, $type);
    }
}
