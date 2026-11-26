<?php

namespace App\Notifications;

use App\Models\GraduationBatch;
use App\Models\Student;
use App\Notifications\Channels\WhatsappChannel;
use App\Support\Access\StudentDataAccess;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GraduationAnnouncedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public GraduationBatch $batch,
        public Student $student,
        public string $decision,
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
        $studentName = $this->student->name ?? 'Siswa';
        $decisionLabel = $this->decision === 'lulus' ? 'LULUS' : 'BELUM LULUS';
        $skInfo = $this->batch->sk_number ? " (SK: {$this->batch->sk_number})" : '';

        return [
            'title' => 'Pengumuman Kelulusan Kelas XII TA '.($this->batch->schoolYear?->name ?? $this->batch->graduation_year),
            'message' => "Berdasarkan keputusan rapat pleno dan pengesahan Kepala Sekolah{$skInfo}, {$studentName} dinyatakan {$decisionLabel}.",
            'graduation_batch_id' => $this->batch->id,
            'student_id' => $this->student->id,
            'decision' => $this->decision,
            'sk_number' => $this->batch->sk_number,
            'graduation_year' => $this->batch->graduation_year,
            'action_url' => $this->decision === 'lulus' ? route('alumni.tracer-study.form') : route('students.ledger', $this->student),
        ];
    }

    public function toWhatsapp(object $notifiable): string
    {
        $studentName = $this->student->name ?? 'Siswa';
        $decisionLabel = $this->decision === 'lulus' ? 'LULUS' : 'BELUM LULUS';
        $schoolName = school_name();
        $skNumber = $this->batch->sk_number ?: '-';

        return "*PENGUMUMAN KELULUSAN KELAS XII*\n\n"
            ."Assalamu'alaikum Bapak/Ibu Wali Murid dari *{$studentName}*,\n\n"
            ."Berdasarkan rapat pleno dewan guru dan Surat Keputusan Kepala Sekolah *{$schoolName}* Nomor *{$skNumber}*, ananda dinyatakan *{$decisionLabel}* pada Tahun Lulus *{$this->batch->graduation_year}*.\n\n"
            .'Informasi lengkap Buku Induk dan pengisian Tracer Study Alumni dapat diakses melalui portal Sulaone dan aplikasi Hanyakra.';
    }
}
