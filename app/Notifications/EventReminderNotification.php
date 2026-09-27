<?php

namespace App\Notifications;

use App\Models\AcademicEvent;
use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class EventReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $event;

    public function __construct(AcademicEvent $event)
    {
        $this->event = $event;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event_uuid' => $this->event->uuid,
            'title' => 'Pengingat Agenda: '.$this->event->title,
            'message' => 'Agenda "'.$this->event->title.'" dijadwalkan dimulai pada '.$this->event->start_date->format('d M Y H:i').'. Harap persiapkan diri Anda.',
            'event_type' => $this->event->event_type,
        ];
    }

    /**
     * Dispatch WhatsApp message immediately.
     */
    public function sendWhatsapp(string $recipientPhone): void
    {
        $message = " *PENGINGAT AGENDA AKADEMIK — SMA ISLAM SULTAN AGUNG 1*\n\n".
          "Yth. Bapak/Ibu/Siswa,\n".
          "Menginfokan agenda sekolah berikut yang akan segera berlangsung:\n\n".
          ' *Nama Kegiatan:* '.$this->event->title."\n".
          ' *Keterangan:* '.($this->event->description ?: '-')."\n".
          ' *Waktu:* '.$this->event->start_date->format('d F Y, H:i')." WIB\n\n".
          'Mohon menjadi perhatian dan persiapan bersama. Terima kasih.';

        // Menggunakan WhatsappService terpusat
        app(WhatsappService::class)->sendMessage($recipientPhone, $message, 'event_reminder');
    }
}
