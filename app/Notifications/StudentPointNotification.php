<?php

namespace App\Notifications;

use App\Models\Setting;
use App\Models\StudentPointLog;
use App\Notifications\Channels\WhatsappChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StudentPointNotification extends Notification
{
    use Queueable;

    public $pointLog;

    public function __construct(StudentPointLog $pointLog)
    {
        $this->pointLog = $pointLog;
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
        $typeLabel = $this->pointLog->type === 'reward' ? 'Prestasi (Reward)' : ($this->pointLog->type === 'penalty' ? 'Pelanggaran (Penalty)' : 'Penyesuaian');
        $pointsSign = $this->pointLog->type === 'reward' ? '+' : ($this->pointLog->type === 'penalty' ? '-' : '');

        return [
            'title' => 'Pencatatan Poin Kedisiplinan',
            'message' => 'Ananda '.$this->pointLog->student?->name.' mendapatkan poin kedisiplinan: '.$pointsSign.$this->pointLog->points.' ('.$typeLabel.') karena: '.$this->pointLog->description.'.',
            'point_log_id' => $this->pointLog->id,
            'points' => $this->pointLog->points,
            'type' => $this->pointLog->type,
            'action_url' => '/academic/points',
        ];
    }

    /**
     * WhatsApp notification message text
     */
    public function toWhatsapp(object $notifiable): string
    {
        $studentName = $this->pointLog->student?->name ?? 'Siswa';
        $typeLabel = $this->pointLog->type === 'reward' ? '🎉 *PRESTASI / PENGHARGASI*' : '⚠️ *PELANGGARAN TATA TERTIB*';
        $pointsLabel = $this->pointLog->type === 'reward' ? '+'.$this->pointLog->points : '-'.$this->pointLog->points;
        $categoryName = $this->pointLog->category?->name ?? 'Poin Kedisiplinan';
        $description = $this->pointLog->description;
        $schoolName = Setting::get('school_name', 'SMA Islam Sultan Agung 1');

        return "Assalamu'alaikum Bapak/Ibu Wali Murid dari *{$studentName}*,\n\n".
               "Menginfokan dari bagian bimbingan konseling (BK) *{$schoolName}* bahwa ananda baru saja mencatat data poin kedisiplinan:\n\n".
               "🔹 *Kategori:* {$typeLabel}\n".
               "🔹 *Jenis Poin:* {$categoryName}\n".
               "🔹 *Nilai Poin:* *{$pointsLabel} Poin*\n".
               "🔹 *Keterangan:* \"{$description}\"\n\n".
               "Mohon dukungan Bapak/Ibu untuk terus memotivasi prestasi ananda atau menertibkan perilaku ananda sesuai tata tertib sekolah.\n\n".
               'Terima kasih atas kepedulian dan kerja samanya.';
    }
}
