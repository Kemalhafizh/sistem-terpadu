<?php

namespace App\Notifications;

use App\Models\Billing;
use App\Notifications\Channels\WhatsappChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BillingReminderNotification extends Notification
{
  use Queueable;

  public $billing;

  public function __construct(Billing $billing)
  {
    $this->billing = $billing;
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
    return [
      'title' => 'Tagihan Pembayaran Baru',
      'message' => 'Tagihan '.$this->billing->category?->name.' sebesar Rp '.number_format($this->billing->amount, 0, ',', '.').' telah diterbitkan.',
      'billing_id' => $this->billing->id,
      'amount' => $this->billing->amount,
      'due_date' => $this->billing->due_date,
      'action_url' => '/finance',
    ];
  }

  /**
   * WhatsApp notification message text
   */
  public function toWhatsapp(object $notifiable): string
  {
    $studentName = $this->billing->student?->name ?? 'Siswa';
    $categoryName = $this->billing->category?->name ?? 'SPP/Uang Gedung';
    $amountFormatted = 'Rp '.number_format($this->billing->remaining_balance, 0, ',', '.');
    $dueDateFormatted = date('d-m-Y', strtotime($this->billing->due_date));
    $schoolName = school_name();

    return "Assalamu'alaikum Bapak/Ibu Wali Murid dari *{$studentName}*,\n\n".
        "Menginfokan tagihan pendidikan ananda yang belum terselesaikan di *{$schoolName}*:\n".
        " *Jenis Tagihan:* {$categoryName}\n".
        " *Jumlah Tunggakan:* {$amountFormatted}\n".
        " *Batas Jatuh Tempo:* {$dueDateFormatted}\n\n".
        "Mohon segera melakukan penyelesaian pembayaran melalui portal keuangan Sistem Terpadu berikut ini:\n".
        url('/finance/checkout/'.$this->billing->id)."\n\n".
        'Terima kasih atas kerja sama Bapak/Ibu sekalian. Semoga ananda selalu diberikan kelancaran belajar.';
  }
}
