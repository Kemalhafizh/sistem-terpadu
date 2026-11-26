<?php

namespace App\Notifications;

use App\Models\WarningLetter;
use App\Notifications\Channels\WhatsappChannel;
use App\Support\Access\StudentDataAccess;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WarningLetterIssuedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public WarningLetter $warningLetter,
        public int $currentPoints,
    ) {}

    public function via(object $notifiable): array
    {
        if (isset($notifiable->role) && in_array($notifiable->role, StudentDataAccess::PARENT_ROLES, true)) {
            return ['database', WhatsappChannel::class];
        }

        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        $student = $this->warningLetter->student;
        $studentName = $student?->name ?? 'Siswa';
        $classroomName = $student?->classroom?->name ?? '-';
        $level = $this->warningLetter->level;
        $threshold = $this->warningLetter->point_threshold;

        return [
            'title' => "Surat Peringatan {$level} Diterbitkan",
            'message' => "Surat Peringatan {$level} diterbitkan untuk {$studentName} ({$classroomName}) karena akumulasi poin mencapai {$this->currentPoints} poin (ambang {$threshold}).",
            'warning_letter_id' => $this->warningLetter->id,
            'student_id' => $this->warningLetter->student_id,
            'level' => $level,
            'point_threshold' => $threshold,
            'current_points' => $this->currentPoints,
            'counseling_session_id' => $this->warningLetter->counseling_session_id,
            'action_url' => $student ? route('points.show', $student) : route('points.index'),
        ];
    }

    public function toWhatsapp(object $notifiable): string
    {
        $student = $this->warningLetter->student;
        $studentName = $student?->name ?? 'Siswa';
        $classroomName = $student?->classroom?->name ?? '-';
        $level = $this->warningLetter->level;
        $threshold = $this->warningLetter->point_threshold;
        $schoolName = school_name();

        return "*PEMBERITAHUAN SURAT PERINGATAN ({$level})*\n\n"
            ."Assalamu'alaikum Bapak/Ibu Wali Murid dari *{$studentName}* ({$classroomName}),\n\n"
            ."Menginfokan dari Bidang Kesiswaan *{$schoolName}* bahwa akumulasi poin kedisiplinan ananda telah mencapai *{$this->currentPoints} Poin* (melewati ambang {$level}: {$threshold} poin).\n\n"
            ."Sehubungan dengan hal tersebut, sekolah menerbitkan *Surat Peringatan {$level}* serta menjadwalkan pendampingan konseling bersama Guru BK. Bapak/Ibu dimohon meninjau dan menandatangani Surat Peringatan secara digital melalui aplikasi Hanyakra / portal Sulaone.\n\n"
            .'Terima kasih atas perhatian dan kerja sama Bapak/Ibu.';
    }
}
