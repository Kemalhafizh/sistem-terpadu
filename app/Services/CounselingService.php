<?php

namespace App\Services;

use App\Models\CounselingSession;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CounselingService
{
    protected $whatsappService;

    public function __construct(WhatsappService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Mengajukan permohonan sesi konseling baru (oleh siswa/orang tua/BK)
     */
    public function requestSession(array $data): CounselingSession
    {
        return DB::transaction(function () use ($data) {
            $data['status'] = 'requested';
            $data['requested_by'] = auth()->id();

            // Jika dibuat oleh guru BK, bisa langsung set status scheduled
            if (in_array(auth()->user()->role, ['bk', 'admin', 'superadmin'])) {
                $data['counselor_id'] = auth()->id();
                $data['status'] = 'scheduled';
            }

            $session = CounselingSession::create($data);

            // Kirim notifikasi WA konfirmasi permohonan
            $this->sendWhatsappNotification($session, 'request');

            return $session;
        });
    }

    /**
     * Konfirmasi jadwal & tentukan konselor oleh Guru BK
     */
    public function scheduleSession(CounselingSession $session, int $counselorId, string $scheduledAt): CounselingSession
    {
        return DB::transaction(function () use ($session, $counselorId, $scheduledAt) {
            $session->update([
                'counselor_id' => $counselorId,
                'scheduled_at' => $scheduledAt,
                'status' => 'scheduled',
            ]);

            // Kirim notifikasi konfirmasi jadwal ke siswa & orang tua
            $this->sendWhatsappNotification($session, 'scheduled');

            return $session;
        });
    }

    /**
     * Memulai sesi konseling (Merubah status ke in_progress)
     */
    public function startSession(CounselingSession $session): CounselingSession
    {
        $session->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return $session;
    }

    /**
     * Menyelesaikan sesi konseling dengan rekam medis BK lengkap (terenkripsi)
     */
    public function completeSession(CounselingSession $session, array $records): CounselingSession
    {
        $session->update([
            'status' => 'completed',
            'ended_at' => now(),
            'session_notes' => $records['session_notes'] ?? null,
            'action_plan' => $records['action_plan'] ?? null,
            'follow_up_notes' => $records['follow_up_notes'] ?? null,
            'follow_up_date' => $records['follow_up_date'] ?? null,
        ]);

        // Kirim resume / info ringkasan tidak sensitif ke orang tua
        $this->sendWhatsappNotification($session, 'completed');

        return $session;
    }

    /**
     * Membatalkan sesi konseling
     */
    public function cancelSession(CounselingSession $session, string $reason = ''): CounselingSession
    {
        $session->update([
            'status' => 'cancelled',
            'reason' => 'DIBATALKAN: '.$reason,
        ]);

        $this->sendWhatsappNotification($session, 'cancelled');

        return $session;
    }

    /**
     * Membuat sesi konseling wajib otomatis ketika poin pelanggaran >= 50
     */
    public function createMandatorySession(Student $student): ?CounselingSession
    {
        // Pengecekan apakah sudah ada sesi BK aktif yang berstatus requested/scheduled/in_progress untuk kasus poin ini
        $exists = CounselingSession::where('student_id', $student->id)
            ->where('category', 'pribadi')
            ->whereIn('status', ['requested', 'scheduled', 'in_progress'])
            ->exists();

        if ($exists) {
            return null;
        }

        // Cari guru BK default
        $counselor = User::where('role', 'bk')->first();

        return DB::transaction(function () use ($student, $counselor) {
            $session = CounselingSession::create([
                'student_id' => $student->id,
                'counselor_id' => $counselor?->id,
                'requested_by' => User::where('role', 'admin')->first()?->id ?: 1, // sistem / admin
                'scheduled_at' => now()->addDays(1)->setHour(9)->setMinute(0), // Besok jam 09:00 pagi
                'status' => 'requested',
                'type' => 'individual',
                'category' => 'pribadi',
                'reason' => 'Sesi Konseling Wajib Otomatis dipicu karena akumulasi poin pelanggaran siswa telah mencapai batas kritis ('.$student->total_points.' Poin).',
            ]);

            // Kirim notifikasi darurat ke orang tua & siswa via WhatsApp
            $this->sendWhatsappNotification($session, 'mandatory_triggered');

            return $session;
        });
    }

    /**
     * Dispatcher Notifikasi WhatsApp Dinamis
     */
    protected function sendWhatsappNotification(CounselingSession $session, string $actionType): void
    {
        $student = $session->student;
        $studentUser = $student?->user;
        $parent = $student?->parent;
        $counselor = $session->counselor;

        $studentName = $studentUser?->name ?? 'Siswa';
        $classroomName = $student?->classroom?->name ?? '-';
        $dateFormatted = $session->scheduled_at ? $session->scheduled_at->translatedFormat('d F Y \j\a\m H:i').' WIB' : '-';

        $counselorName = $counselor?->name ?? 'Petugas BK';
        $sessionType = strtoupper($session->type);

        $parentPhone = $parent?->phone_number;
        $studentPhone = $studentUser?->phone_number;

        if ($actionType === 'request') {
            // Beritahu Guru BK ada permohonan baru
            $bkTeachers = User::where('role', 'bk')->get();
            foreach ($bkTeachers as $bk) {
                if ($bk->phone_number) {
                    $msg = " *PERMOHONAN KONSELING BARU (BK ONLINE)*\n\n"
                         ."Halo Bapak/Ibu Guru BK, terdapat permohonan sesi konseling baru dari:\n"
                         ."Nama Siswa: *{$studentName}*\n"
                         ."Kelas: *{$classroomName}*\n"
                         .'Kategori: *'.ucfirst($session->category)."*\n"
                         .'Alasan Pengajuan: '.substr($session->reason, 0, 100)."...\n\n"
                         ."Mohon segera masuk ke sistem terpadu sekolah untuk melakukan verifikasi dan konfirmasi jadwal sesi.\n"
                         .'Terima kasih.';
                    try {
                        $this->whatsappService->sendMessage($bk->phone_number, $msg, 'bk_notification');
                    } catch (\Exception $e) {
                        Log::error('WA BK request failed: '.$e->getMessage());
                    }
                }
            }
        } elseif ($actionType === 'scheduled') {
            // Notifikasi jadwal terkonfirmasi ke Siswa & Orang Tua
            $msg = " *JADWAL KONSELING BK TERKONFIRMASI*\n\n"
                 ."Assalamu'alaikum,\n"
                 ."Menginfokan jadwal sesi bimbingan konseling (BK) untuk:\n"
                 ."Nama: *{$studentName}*\n"
                 ."Kelas: *{$classroomName}*\n\n"
                 ."Telah dijadwalkan pada:\n"
                 ."Hari/Tgl: *{$dateFormatted}*\n"
                 ."Jenis Sesi: *{$sessionType}*\n"
                 ."Konselor/Guru BK: *{$counselorName}*\n\n"
                 .'Kehadiran sangat penting dalam sesi ini demi perkembangan akademik dan pribadi ananda. Terima kasih.';

            if ($parentPhone) {
                try {
                    $this->whatsappService->sendMessage($parentPhone, $msg, 'bk_scheduled');
                } catch (\Exception $e) {
                }
            }
            if ($studentPhone) {
                try {
                    $this->whatsappService->sendMessage($studentPhone, $msg, 'bk_scheduled');
                } catch (\Exception $e) {
                }
            }
        } elseif ($actionType === 'completed') {
            // Beritahu orang tua sesi selesai dilaksanakan
            if ($parentPhone) {
                $msg = " *SESI KONSELING BK SELESAI*\n\n"
                     ."Assalamu'alaikum,\n"
                     ."Menginfokan bahwa sesi bimbingan konseling (BK) ananda:\n"
                     ."Nama: *{$studentName}*\n"
                     ."Telah selesai dilaksanakan pada hari ini bersama Guru BK *{$counselorName}*.\n\n"
                     .'Sistem telah mencatatkan rekam hasil sesi secara aman dan rahasia. Terima kasih atas dukungan Bapak/Ibu.';
                try {
                    $this->whatsappService->sendMessage($parentPhone, $msg, 'bk_completed');
                } catch (\Exception $e) {
                }
            }
        } elseif ($actionType === 'cancelled') {
            // Beritahu pembatalan sesi
            $msg = " *SESI KONSELING BK DIBATALKAN*\n\n"
                 ."Assalamu'alaikum,\n"
                 ."Sesi bimbingan konseling (BK) ananda:\n"
                 ."Nama: *{$studentName}*\n"
                 ."Pada waktu: *{$dateFormatted}*\n"
                 ."Telah dibatalkan dengan alasan:\n"
                 ."*{$session->reason}*\n\n"
                 .'Silakan ajukan kembali jadwal sesi jika masih diperlukan. Terima kasih.';

            if ($parentPhone) {
                try {
                    $this->whatsappService->sendMessage($parentPhone, $msg, 'bk_cancelled');
                } catch (\Exception $e) {
                }
            }
            if ($studentPhone) {
                try {
                    $this->whatsappService->sendMessage($studentPhone, $msg, 'bk_cancelled');
                } catch (\Exception $e) {
                }
            }
        } elseif ($actionType === 'mandatory_triggered') {
            // Notifikasi Peringatan Darurat Konseling Wajib karena akumulasi poin >= 50
            if ($parentPhone) {
                $msg = " *PEMBERITAHUAN DARURAT: KONSELING BK WAJIB*\n\n"
                     ."Assalamu'alaikum Bapak/Ibu Wali Murid dari *{$studentName}*,\n\n"
                     ."Menginfokan dari Unit Bimbingan Konseling (BK) SMA Islam Sultan Agung 1 bahwa ananda saat ini telah mengumpulkan akumulasi poin pelanggaran sebesar *{$student->total_points} Poin* (Batas kritis >= 50).\n\n"
                     ."Sesuai dengan peraturan tata tertib sekolah, sistem secara otomatis menjadwalkan:\n"
                     ." *Sesi Konseling Wajib Individual*\n"
                     ."Hari/Tanggal: *Bilah Jadwal Antrean BK*\n"
                     ."Konselor BK: *{$counselorName}*\n\n"
                     ."Bapak/Ibu diharapkan mendampingi atau memantau kehadiran ananda pada sesi khusus ini guna merumuskan program pembinaan terpadu.\n"
                     .'Terima kasih atas kerja samanya.';
                try {
                    $this->whatsappService->sendMessage($parentPhone, $msg, 'bk_emergency_mandatory');
                } catch (\Exception $e) {
                }
            }
        }
    }
}
