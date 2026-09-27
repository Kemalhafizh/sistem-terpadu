<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Notifications\Channels\WhatsappChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TicketCreatedNotification extends Notification
{
  use Queueable;

  public $ticket;
  public $recipientType; // 'reporter' or 'staff'

  public function __construct(Ticket $ticket, string $recipientType = 'reporter')
  {
    $this->ticket = $ticket;
    $this->recipientType = $recipientType;
  }

  public function via(object $notifiable): array
  {
    return ['database', WhatsappChannel::class];
  }

  /**
   * Database notification payload
   */
  public function toArray(object $notifiable): array
  {
    if ($this->recipientType === 'staff') {
      return [
        'title' => 'Tiket Baru Diajukan: ' . $this->ticket->ticket_number,
        'message' => 'Tiket baru "' . $this->ticket->title . '" telah diajukan oleh ' . ($this->ticket->reporter?->name ?? 'Seseorang') . ' dengan prioritas ' . strtoupper($this->ticket->priority) . '.',
        'ticket_uuid' => $this->ticket->uuid,
        'action_url' => '/helpdesk/tickets/' . $this->ticket->uuid,
      ];
    }

    return [
      'title' => 'Tiket Berhasil Dibuat: ' . $this->ticket->ticket_number,
      'message' => 'Tiket Anda "' . $this->ticket->title . '" berhasil dibuat dan sedang ditinjau. Estimasi pengerjaan: ' . ($this->ticket->due_date ? $this->ticket->due_date->format('d M Y H:i') : '-'),
      'ticket_uuid' => $this->ticket->uuid,
      'action_url' => '/helpdesk/tickets/' . $this->ticket->uuid,
    ];
  }

  /**
   * WhatsApp notification message text
   */
  public function toWhatsapp(object $notifiable): string
  {
    $schoolName = school_name();
    $ticketNum = $this->ticket->ticket_number;
    $title = $this->ticket->title;
    $priority = strtoupper($this->ticket->priority);
    $category = strtoupper($this->ticket->category);
    $dueDate = $this->ticket->due_date ? $this->ticket->due_date->format('d M Y H:i') : '-';

    if ($this->recipientType === 'staff') {
      $reporterName = $this->ticket->reporter?->name ?? 'Pengguna';
      return " *TIKET HELPDESK BARU MASUK*\n\n".
          "Menginfokan kepada petugas *{$schoolName}* bahwa terdapat laporan tiket baru:\n\n".
          " *No Tiket:* {$ticketNum}\n".
          " *Judul:* {$title}\n".
          " *Pelapor:* {$reporterName}\n".
          " *Kategori:* {$category}\n".
          " *Prioritas:* *{$priority}*\n".
          " *Batas SLA:* {$dueDate}\n\n".
          "Mohon segera masuk ke dashboard helpdesk untuk menugaskan petugas dan memproses tiket ini.\n\n".
          "Terima kasih.";
    }

    $reporterName = $notifiable->name ?? 'Pengguna';
    return "Assalamu'alaikum *{$reporterName}*,\n\n".
        "Tiket pengaduan Anda berhasil dibuat di sistem *{$schoolName}*:\n\n".
        " *No Tiket:* {$ticketNum}\n".
        " *Judul:* {$title}\n".
        " *Kategori:* {$category}\n".
        " *Prioritas:* {$priority}\n".
        " *Estimasi Selesai (SLA):* *{$dueDate}*\n\n".
        "Petugas kami akan segera meninjau dan merespon laporan Anda. Riwayat pengerjaan dapat Anda pantau langsung melalui dashboard Helpdesk di aplikasi SISTA.\n\n".
        "Terima kasih atas laporan Anda.";
  }
}
