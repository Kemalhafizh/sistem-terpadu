<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorCodeNotification extends Notification
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kode Verifikasi Keamanan - Sistem Terpadu')
            ->line('Anda menerima email ini karena ada permintaan login ke akun Anda.')
            ->line('Gunakan kode di bawah ini untuk melanjutkan proses verifikasi:')
            ->greeting('Halo, '.$notifiable->name)
            ->line($notifiable->two_factor_code)
            ->line('Kode ini akan kedaluwarsa dalam 10 menit.')
            ->line('Jika Anda tidak mencoba login, abaikan email ini.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
