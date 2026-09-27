<?php

namespace App\Notifications\Channels;

use App\Models\Setting;
use App\Models\User;
use App\Models\WhatsappAutomation;
use App\Models\WhatsappLog;
use App\Services\WhatsappAutomationService;
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

        $message = null;
        $automationService = app(WhatsappAutomationService::class);

        if ($type === 'StudentAttendanceNotification') {
            $attendance = $notification->attendance;
            $student = $attendance->student;
            $parent = $student?->parent;

            $context = [
                'nama_siswa' => $student?->name ?? '',
                'nama_orang_tua' => $parent?->name ?? '',
                'kelas' => $student?->classroom?->name ?? '',
                'tanggal' => date('d-m-Y', strtotime($attendance->date)),
            ];

            $automation = WhatsappAutomation::where('trigger', 'attendance_absent')
                ->where('is_active', true)
                ->with('template')
                ->first();

            if ($automation && $automation->template) {
                $message = $automationService->parseMessage($automation->template->content, $context);
            }
        } elseif ($type === 'BillingReminderNotification') {
            $billing = $notification->billing;
            $student = $billing->student;
            $parent = $student?->parent;

            $context = [
                'nama_siswa' => $student?->name ?? '',
                'nama_orang_tua' => $parent?->name ?? '',
                'jenis_tagihan' => $billing->category?->name ?? 'SPP',
                'tagihan' => 'Rp '.number_format($billing->remaining_balance, 0, ',', '.'),
                'batas_waktu' => date('d-m-Y', strtotime($billing->due_date)),
            ];

            $automation = WhatsappAutomation::where('trigger', 'billing_overdue')
                ->where('is_active', true)
                ->with('template')
                ->first();

            if ($automation && $automation->template) {
                $message = $automationService->parseMessage($automation->template->content, $context);
            }
        }

        if ($message === null) {
            $message = $notification->toWhatsapp($notifiable);
        }

        return $this->whatsappService->sendMessage($to, $message, $type);
    }
}
