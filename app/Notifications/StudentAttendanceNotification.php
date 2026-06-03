<?php

namespace App\Notifications;

use App\Models\Attendance;
use App\Models\Setting;
use App\Notifications\Channels\WhatsappChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StudentAttendanceNotification extends Notification
{
    use Queueable;

    public $attendance;

    public function __construct(Attendance $attendance)
    {
        $this->attendance = $attendance;
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
        $statusLabels = [
            'H' => 'Hadir',
            'S' => 'Sakit',
            'I' => 'Izin',
            'A' => 'Alpa (Tanpa Keterangan)',
        ];
        $statusLabel = $statusLabels[$this->attendance->status] ?? 'Absen';

        return [
            'title' => 'Laporan Absensi Siswa',
            'message' => 'Ananda '.$this->attendance->student?->name.' tercatat memiliki status kehadiran: '.$statusLabel.' pada tanggal '.date('d-m-Y', strtotime($this->attendance->date)).'.',
            'attendance_id' => $this->attendance->id,
            'status' => $this->attendance->status,
            'date' => $this->attendance->date,
            'action_url' => '/academic/attendance',
        ];
    }

    /**
     * WhatsApp notification message text
     */
    public function toWhatsapp(object $notifiable): string
    {
        $studentName = $this->attendance->student?->name ?? 'Siswa';
        $dateFormatted = date('d-m-Y', strtotime($this->attendance->date));
        $schoolName = Setting::get('school_name', 'SMA Islam Sultan Agung 1');
        $notes = $this->attendance->notes ? "\n🔹 *Keterangan:* ".$this->attendance->notes : '';

        return "Assalamu'alaikum Bapak/Ibu Wali Murid dari *{$studentName}*,\n\n".
               "Menginfokan dari bagian kesiswaan *{$schoolName}* bahwa pada hari ini:\n".
               "📅 *Tanggal:* {$dateFormatted}\n".
               "📌 *Status Presensi:* *ALPA (Mangkir / Tanpa Keterangan)*{$notes}\n\n".
               "Mohon bimbingan bersama untuk kedisiplinan ananda di sekolah. Bila terdapat kekeliruan atau keperluan mendesak, silakan hubungi wali kelas terkait.\n\n".
               'Terima kasih atas kerja samanya.';
    }
}
