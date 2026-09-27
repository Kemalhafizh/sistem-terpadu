<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Notifications\Channels\WhatsappChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TicketStatusUpdatedNotification extends Notification
{
  use Queueable;

  public $ticket;
  public $updateType; // 'status_changed', 'assigned', 'comment_added'
  public $extraData;

  public function __construct(Ticket $ticket, string $updateType, array $extraData = [])
  {
    $this->ticket = $ticket;
    $this->updateType = $updateType;
    $this->extraData = $extraData;
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
    $message = '';
    $title = '';

    if ($this->updateType === 'status_changed') {
      $title = 'Status Tiket Diperbarui: ' . $this->ticket->ticket_number;
      $message = 'Status tiket Anda "' . $this->ticket->title . '" telah diperbarui menjadi ' . strtoupper($this->ticket->status) . '.';
    } elseif ($this->updateType === 'assigned') {
      $title = 'Tiket Ditugaskan: ' . $this->ticket->ticket_number;
      $message = 'Tiket Anda "' . $this->ticket->title . '" telah ditugaskan kepada ' . ($this->ticket->assignee?->name ?? 'petugas') . '.';
    } elseif ($this->updateType === 'comment_added') {
      $title = 'Komentar Baru: ' . $this->ticket->ticket_number;
      $message = ($this->extraData['commenter_name'] ?? 'Petugas') . ' menambahkan komentar baru pada tiket Anda.';
    }

    return [
      'title' => $title,
      'message' => $message,
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
    $reporterName = $notifiable->name ?? 'Pengguna';
    
    $body = "Assalamu'alaikum *{$reporterName}*,\n\n".
        "Menginfokan perkembangan tiket Anda *#{$ticketNum}* di *{$schoolName}*:\n\n".
        " *Judul:* {$title}\n";

    if ($this->updateType === 'status_changed') {
      $statusLabel = match ($this->ticket->status) {
        'in_progress' => ' *SEDANG DIKERJAKAN (IN PROGRESS)*',
        'waiting' => ' *MENUNGGU INFORMASI (WAITING)*',
        'resolved' => ' *SELESAI (RESOLVED)*',
        'closed' => ' *DITUTUP (CLOSED)*',
        default => strtoupper($this->ticket->status),
      };
      $body .= " *Status Terbaru:* {$statusLabel}\n";
      if ($this->ticket->status === 'resolved' && $this->ticket->resolution) {
        $body .= " *Solusi/Resolusi:* \"{$this->ticket->resolution}\"\n";
      }
    } elseif ($this->updateType === 'assigned') {
      $assigneeName = $this->ticket->assignee?->name ?? 'Petugas';
      $body .= " *Ditugaskan Kepada:* *{$assigneeName}*\n".
           " *Status:* *SEDANG DIKERJAKAN*\n";
    } elseif ($this->updateType === 'comment_added') {
      $commenter = $this->extraData['commenter_name'] ?? 'Petugas';
      $commentBody = $this->extraData['comment_body'] ?? '';
      $body .= " *Pemberi Respon:* *{$commenter}*\n".
           " *Pesan:* \"{$commentBody}\"\n";
    }

    $body .= "\nSilakan pantau terus perkembangan tiket Anda melalui dashboard Helpdesk aplikasi SISTA.\n\n".
         "Terima kasih.";

    return $body;
  }
}
