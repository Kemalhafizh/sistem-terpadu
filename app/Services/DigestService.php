<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Billing;
use App\Models\Ticket;
use App\Models\SmartAlert;
use App\Models\Payment;
use App\Models\TeachingJournal;
use App\Models\ComplianceDocument;
use App\Models\ComplianceMilestone;
use App\Models\Grade;
use App\Models\StudentPointLog;
use App\Models\ElearningClass;
use App\Models\ElearningAssignment;
use App\Models\ElearningSubmission;
use App\Models\ScheduledReport;
use App\Mail\ScheduledDigestMail;
use App\Services\WhatsappService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class DigestService
{
    protected $whatsappService;

    public function __construct(WhatsappService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Generate daily admin digest statistics.
     */
    public function generateDailyAdminDigest(): array
    {
        $today = Carbon::today();

        $alpaCount = Attendance::where('date', $today->toDateString())->where('status', 'A')->count();
        $newTickets = Ticket::whereDate('created_at', $today)->count();
        $newBillings = Billing::whereDate('created_at', $today)->where('remaining_balance', '>', 0)->count();
        $activeAlerts = SmartAlert::where('status', 'active')->whereDate('created_at', $today)->count();

        return [
            'alpa_count' => $alpaCount,
            'new_tickets' => $newTickets,
            'new_billings' => $newBillings,
            'active_alerts' => $activeAlerts,
            'date' => $today->translatedFormat('d F Y'),
        ];
    }

    /**
     * Generate weekly headmaster (kepsek) digest statistics.
     */
    public function generateWeeklyKepsekDigest(): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // Attendance stats
        $totalPresences = Attendance::whereBetween('date', [$startOfWeek, $endOfWeek])->count();
        $presentCount = Attendance::whereBetween('date', [$startOfWeek, $endOfWeek])->where('status', 'H')->count();
        $attendanceRate = $totalPresences > 0 ? round(($presentCount / $totalPresences) * 100, 1) : 100.0;

        // Finance stats
        $totalPayments = Payment::whereBetween('created_at', [$startOfWeek, $endOfWeek])->sum('amount_paid');

        // Teaching journals
        $missingJournals = TeachingJournal::whereBetween('date', [$startOfWeek, $endOfWeek])
            ->whereNull('verified_at')
            ->count();

        // Compliance status
        $completedMilestones = ComplianceMilestone::where('is_completed', true)->count();
        $totalMilestones = ComplianceMilestone::count();

        return [
            'attendance_rate' => $attendanceRate,
            'total_payments' => (float)$totalPayments,
            'missing_journals' => $missingJournals,
            'completed_milestones' => $completedMilestones,
            'total_milestones' => $totalMilestones,
            'start_date' => $startOfWeek->translatedFormat('d M'),
            'end_date' => $endOfWeek->translatedFormat('d M Y'),
        ];
    }

    /**
     * Generate monthly finance digest statistics.
     */
    public function generateMonthlyFinanceDigest(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $totalReceived = Payment::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('amount_paid');
        $totalOutstanding = Billing::where('remaining_balance', '>', 0)->sum('remaining_balance');
        $totalOutstandingCount = Billing::where('remaining_balance', '>', 0)->count();

        // Top 5 debtors
        $topDebtors = Student::with(['user', 'classroom'])
            ->leftJoin('billings', 'students.id', '=', 'billings.student_id')
            ->select('students.*', \DB::raw('SUM(billings.remaining_balance) as unpaid_sum'))
            ->where('billings.remaining_balance', '>', 0)
            ->groupBy('students.id')
            ->orderBy('unpaid_sum', 'desc')
            ->take(5)
            ->get();

        return [
            'total_received' => (float)$totalReceived,
            'total_outstanding' => (float)$totalOutstanding,
            'outstanding_count' => $totalOutstandingCount,
            'top_debtors' => $topDebtors,
            'month' => Carbon::now()->translatedFormat('F Y'),
        ];
    }

    /**
     * Generate weekly student progress digest for parents.
     */
    public function generateWeeklyParentDigest(Student $student): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // 1. Attendance recap
        $alpaCount = Attendance::where('student_id', $student->id)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->where('status', 'A')
            ->count();
            
        $lateCount = Attendance::where('student_id', $student->id)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->where('status', 'T')
            ->count();

        // 2. Recent grades
        $recentGrades = Grade::with('subject')
            ->where('student_id', $student->id)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->orderBy('created_at', 'desc')
            ->get();

        // 3. Counseling BK Points
        $bkPoints = StudentPointLog::where('student_id', $student->id)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->sum('points');

        // 4. Pending assignments
        $classIds = ElearningClass::where('classroom_id', $student->classroom_id)->pluck('id');
        $submittedAssignmentIds = ElearningSubmission::where('student_id', $student->id)->pluck('elearning_assignment_id');
        $pendingAssignments = ElearningAssignment::with('elearningClass.subject')
            ->whereIn('elearning_class_id', $classIds)
            ->whereNotIn('id', $submittedAssignmentIds)
            ->get();

        return [
            'student_name' => $student->name,
            'classroom_name' => $student->classroom?->name ?? 'Kelas -',
            'alpa_count' => $alpaCount,
            'late_count' => $lateCount,
            'recent_grades' => $recentGrades,
            'bk_points' => (int)$bkPoints,
            'pending_assignments' => $pendingAssignments,
            'start_date' => $startOfWeek->translatedFormat('d M'),
            'end_date' => $endOfWeek->translatedFormat('d M Y'),
        ];
    }

    /**
     * Compile digest statistics to a beautiful, responsive HTML table structure.
     */
    public function renderDigestHtml(string $type, array $data): string
    {
        $html = '';

        if ($type === 'daily_admin') {
            $html = "
                <p>Berikut adalah ikhtisar operasional harian <strong>SISTA</strong> untuk tanggal <strong>{$data['date']}</strong>:</p>
                <table style='width: 100%; border-collapse: collapse; margin-top: 20px;'>
                    <tr style='background-color: #f1f5f9; text-align: left; font-weight: bold;'>
                        <th style='padding: 12px; border: 1px solid #cbd5e1;'>Indikator Operasional</th>
                        <th style='padding: 12px; border: 1px solid #cbd5e1; text-align: center;'>Jumlah</th>
                    </tr>
                    <tr>
                        <td style='padding: 12px; border: 1px solid #cbd5e1;'>Siswa Alpa Kehadiran (Hari Ini)</td>
                        <td style='padding: 12px; border: 1px solid #cbd5e1; text-align: center; font-weight: bold; color: #ef4444;'>{$data['alpa_count']} Siswa</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; border: 1px solid #cbd5e1;'>Tiket Bantuan Helpdesk Baru</td>
                        <td style='padding: 12px; border: 1px solid #cbd5e1; text-align: center; font-weight: bold; color: #3b82f6;'>{$data['new_tickets']} Tiket</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; border: 1px solid #cbd5e1;'>Tunggakan SPP/Tagihan Keuangan Baru</td>
                        <td style='padding: 12px; border: 1px solid #cbd5e1; text-align: center; font-weight: bold; color: #f59e0b;'>{$data['new_billings']} Tagihan</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; border: 1px solid #cbd5e1;'>Alarm Smart Alerts Aktif Terdeteksi</td>
                        <td style='padding: 12px; border: 1px solid #cbd5e1; text-align: center; font-weight: bold; color: #8b5cf6;'>{$data['active_alerts']} Alarm</td>
                    </tr>
                </table>
                <p style='margin-top: 20px; font-size: 13px; color: #64748b;'>Harap segera login ke dashboard Administrator untuk melakukan peninjauan anomali di lapangan.</p>
            ";

        } elseif ($type === 'weekly_kepsek') {
            $html = "
                <p>Yth. Kepala Sekolah, berikut rekapitulasi strategis sekolah mingguan periode <strong>{$data['start_date']} s.d. {$data['end_date']}</strong>:</p>
                <div style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-top: 20px;'>
                    <h3 style='margin-top: 0; color: #312e81; font-size: 16px;'> Ringkasan Eksekutif</h3>
                    <ul style='padding-left: 20px; margin-bottom: 0; line-height: 1.8;'>
                        <li>Rata-rata Kehadiran Siswa Kelas: <strong>{$data['attendance_rate']}%</strong></li>
                        <li>Total Penerimaan Iuran SPP Baru: <strong>Rp " . number_format($data['total_payments'], 0, ',', '.') . "</strong></li>
                        <li>Jurnal Mengajar Kelas Terhambat (Belum Verifikasi): <strong>{$data['missing_journals']} Jurnal</strong></li>
                        <li>Target Kepatuhan Standar Mutu Tercapai: <strong>{$data['completed_milestones']} / {$data['total_milestones']} Target</strong></li>
                    </ul>
                </div>
            ";

        } elseif ($type === 'monthly_finance') {
            $debtorsHtml = '';
            foreach ($data['top_debtors'] as $idx => $debtor) {
                $debtorsHtml .= "
                    <tr>
                        <td style='padding: 10px; border: 1px solid #cbd5e1; text-align: center;'>" . ($idx + 1) . "</td>
                        <td style='padding: 10px; border: 1px solid #cbd5e1;'>{$debtor->name}</td>
                        <td style='padding: 10px; border: 1px solid #cbd5e1; text-align: center;'>{$debtor->classroom?->name}</td>
                        <td style='padding: 10px; border: 1px solid #cbd5e1; text-align: right; font-weight: bold; color: #ef4444;'>Rp " . number_format($debtor->unpaid_sum, 0, ',', '.') . "</td>
                    </tr>
                ";
            }
            if ($data['top_debtors']->isEmpty()) {
                $debtorsHtml = "<tr><td colspan='4' style='padding: 20px; text-align: center; color: #94a3b8; italic;'>Tidak ada penunggak tagihan bulan ini.</td></tr>";
            }

            $html = "
                <p>Berikut adalah rekapitulasi performa finansial sekolah periode <strong>{$data['month']}</strong>:</p>
                <div style='display: flex; gap: 20px; margin-top: 20px; flex-wrap: wrap;'>
                    <div style='flex: 1; min-width: 200px; background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; padding: 15px; text-align: center;'>
                        <p style='margin: 0; font-size: 11px; font-weight: bold; color: #047857; uppercase; letter-spacing: 0.05em;'>TOTAL DITERIMA BULAN INI</p>
                        <p style='margin: 5px 0 0 0; font-size: 20px; font-weight: 900; color: #065f46;'>Rp " . number_format($data['total_received'], 0, ',', '.') . "</p>
                    </div>
                    <div style='flex: 1; min-width: 200px; background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 15px; text-align: center;'>
                        <p style='margin: 0; font-size: 11px; font-weight: bold; color: #b91c1c; uppercase; letter-spacing: 0.05em;'>TOTAL TUNGGAKAN BERJALAN</p>
                        <p style='margin: 5px 0 0 0; font-size: 20px; font-weight: 900; color: #991b1b;'>Rp " . number_format($data['total_outstanding'], 0, ',', '.') . "</p>
                    </div>
                </div>
                
                <h3 style='margin-top: 30px; color: #312e81; font-size: 15px;'> Klasemen 5 Siswa Penunggak Terbesar</h3>
                <table style='width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px;'>
                    <tr style='background-color: #f1f5f9; text-align: left; font-weight: bold;'>
                        <th style='padding: 10px; border: 1px solid #cbd5e1; text-align: center; width: 50px;'>Pos</th>
                        <th style='padding: 10px; border: 1px solid #cbd5e1;'>Nama Siswa</th>
                        <th style='padding: 10px; border: 1px solid #cbd5e1; text-align: center;'>Kelas</th>
                        <th style='padding: 10px; border: 1px solid #cbd5e1; text-align: right;'>Sisa Tunggakan</th>
                    </tr>
                    {$debtorsHtml}
                </table>
            ";

        } elseif ($type === 'weekly_parent') {
            $gradesHtml = '';
            foreach ($data['recent_grades'] as $grade) {
                $gradesHtml .= "<li>{$grade->subject->name}: <strong>{$grade->score}</strong></li>";
            }
            if ($data['recent_grades']->isEmpty()) {
                $gradesHtml = "<li><span style='color:#94a3b8;'>Belum ada input nilai baru minggu ini.</span></li>";
            }

            $hwHtml = '';
            foreach ($data['pending_assignments'] as $hw) {
                $hwHtml .= "<li>{$hw->elearningClass->subject->name} - {$hw->title} (Batas: " . Carbon::parse($hw->due_at)->translatedFormat('d M Y') . ")</li>";
            }
            if ($data['pending_assignments']->isEmpty()) {
                $hwHtml = "<li><span style='color:#10b981;'> Sempurna! Semua tugas E-Learning telah diselesaikan.</span></li>";
            }

            $html = "
                <p>Bapak/Ibu Orang Tua Wali dari <strong>{$data['student_name']}</strong> ({$data['classroom_name']}), berikut ringkasan tumbuh kembang belajar anak periode <strong>{$data['start_date']} s.d. {$data['end_date']}</strong>:</p>
                
                <h3 style='color: #312e81; font-size: 14px; margin-top: 25px; border-bottom: 2px solid #e2e8f0; padding-bottom: 5px;'> Kedisiplinan & Presensi</h3>
                <ul style='padding-left: 20px; line-height: 1.7;'>
                    <li>Siswa tidak hadir tanpa keterangan (Alpa): <strong>{$data['alpa_count']} Kali</strong></li>
                    <li>Siswa terlambat masuk sekolah: <strong>{$data['late_count']} Kali</strong></li>
                    <li>Akumulasi poin sanksi bimbingan BK baru: <strong style='color: " . ($data['bk_points'] > 0 ? '#ef4444' : '#64748b') . "'>{$data['bk_points']} Poin</strong></li>
                </ul>

                <h3 style='color: #312e81; font-size: 14px; margin-top: 25px; border-bottom: 2px solid #e2e8f0; padding-bottom: 5px;'> Perkembangan Akademik</h3>
                <ul style='padding-left: 20px; line-height: 1.7;'>
                    {$gradesHtml}
                </ul>

                <h3 style='color: #312e81; font-size: 14px; margin-top: 25px; border-bottom: 2px solid #e2e8f0; padding-bottom: 5px;'> Pekerjaan Rumah (PR) E-Learning yang Tertunda</h3>
                <ul style='padding-left: 20px; line-height: 1.7;'>
                    {$hwHtml}
                </ul>
            ";
        }

        return $html;
    }

    /**
     * Compile digest statistics to a beautiful, formal PDF document with kop surat.
     */
    public function renderDigestPdf(string $type, array $data): string
    {
        $htmlContent = $this->renderDigestHtml($type, $data);
        
        $headings = [];
        $title = 'Laporan Rangkuman Terjadwal SISTA';
        
        if ($type === 'daily_admin') {
            $title = 'Laporan Ringkasan Operasional Harian Sekolah';
        } elseif ($type === 'weekly_kepsek') {
            $title = 'Laporan Rekapitulasi Strategis Mingguan Kepala Sekolah';
        } elseif ($type === 'monthly_finance') {
            $title = 'Laporan Kinerja Finansial Sekolah Bulanan';
        } elseif ($type === 'weekly_parent') {
            $title = 'Laporan Perkembangan Tumbuh Kembang Belajar Siswa';
        }

        // We use an elegant PDF view structure
        $pdfHtml = "
            <html>
            <head>
                <meta charset='utf-8'>
                <style>
                    body {
                        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
                        color: #334155;
                        font-size: 12px;
                        line-height: 1.5;
                    }
                    .kop-surat {
                        border-bottom: 3px double #000000;
                        padding-bottom: 10px;
                        margin-bottom: 30px;
                        text-align: center;
                    }
                    .kop-surat h2 {
                        margin: 0;
                        font-size: 18px;
                        color: #1e3a8a;
                    }
                    .kop-surat p {
                        margin: 5px 0 0 0;
                        font-size: 10px;
                        color: #64748b;
                        text-transform: uppercase;
                        letter-spacing: 0.1em;
                    }
                    .pdf-title {
                        font-size: 16px;
                        font-weight: bold;
                        text-align: center;
                        margin-bottom: 20px;
                        color: #1e293b;
                        text-transform: uppercase;
                    }
                    .footer {
                        position: fixed;
                        bottom: 0;
                        left: 0;
                        right: 0;
                        text-align: center;
                        font-size: 9px;
                        color: #94a3b8;
                        border-top: 1px solid #cbd5e1;
                        padding-top: 5px;
                    }
                </style>
            </head>
            <body>
                <!-- Kop Surat SMA Islam Sultan Agung 1 -->
                <div class='kop-surat'>
                    <h2>SMA ISLAM SULTAN AGUNG 1 SEMARANG</h2>
                    <p>Jalan Pahlawan No. 1, Semarang, Jawa Tengah | Telp: (024) 123456</p>
                    <p style='font-size: 9px; font-weight: normal; color: #94a3b8; margin-top: 3px;'>TERAKREDITASI A • STATUS DIGITAL ENTERPRISE INTEGRATED PLATFORM</p>
                </div>
                
                <div class='pdf-title'>{$title}</div>
                
                <div style='margin-top: 20px;'>
                    {$htmlContent}
                </div>

                <div class='footer'>
                    Laporan ini dicetak secara otomatis oleh SISTA Reporting Engine pada " . date('d-m-Y H:i:s') . ". Halaman 1 dari 1.
                </div>
            </body>
            </html>
        ";

        // Generate Barryvdh DomPDF
        $pdf = Pdf::loadHTML($pdfHtml);
        
        $tempPath = storage_path('app/temp');
        if (!file_exists($tempPath)) {
            mkdir($tempPath, 0755, true);
        }
        
        $fileName = 'Digest_' . $type . '_' . uniqid() . '.pdf';
        $fullPath = $tempPath . '/' . $fileName;
        
        $pdf->setPaper('a4', 'portrait')->save($fullPath);
        
        return $fullPath;
    }

    /**
     * Trigger and send scheduled digest.
     */
    public function sendDigest(ScheduledReport $report): void
    {
        $type = $report->report_type;
        $data = [];

        // 1. Gather stats according to report type
        if ($type === 'daily_admin') {
            $data = $this->generateDailyAdminDigest();
        } elseif ($type === 'weekly_kepsek') {
            $data = $this->generateWeeklyKepsekDigest();
        } elseif ($type === 'monthly_finance') {
            $data = $this->generateMonthlyFinanceDigest();
        } elseif ($type === 'weekly_parent') {
            // Parental digest runs dynamically for EACH student whose parents have emails!
            // For testing/mocking or generic execution, we can fetch students
            $students = Student::with(['user', 'classroom'])->whereHas('user', function($q) {
                $q->whereNotNull('email');
            })->take(5)->get(); // Limit for performance safety

            foreach ($students as $st) {
                $stData = $this->generateWeeklyParentDigest($st);
                $this->deliverDigestToRecipient($report, $st->user->email, $st->user->phone_number, $stData);
            }
            
            // Mark as sent
            $report->update(['last_sent_at' => Carbon::now()]);
            return;
        }

        // 2. Deliver digest for general reports
        // Resolve recipients emails
        $emails = [];
        $phones = [];
        foreach ($report->recipients as $rec) {
            if ($rec['type'] === 'email') {
                $emails[] = $rec['value'];
            } elseif ($rec['type'] === 'role') {
                $roleUsers = User::where('role', $rec['value'])->get();
                foreach ($roleUsers as $u) {
                    if ($u->email) $emails[] = $u->email;
                    if ($u->phone_number) $phones[] = $u->phone_number;
                }
            }
        }

        // Deliver
        foreach (array_unique($emails) as $email) {
            // Find phone number for this email if possible
            $user = User::where('email', $email)->first();
            $phone = $user ? $user->phone_number : null;

            $this->deliverDigestToRecipient($report, $email, $phone, $data);
        }

        // Mark as sent
        $report->update(['last_sent_at' => Carbon::now()]);
    }

    /**
     * Internal helper to deliver emails/whatsapp notifications for a single recipient.
     */
    protected function deliverDigestToRecipient(ScheduledReport $report, string $email, ?string $phone, array $data): void
    {
        $type = $report->report_type;
        $htmlContent = $this->renderDigestHtml($type, $data);
        
        $pdfPath = null;
        if ($report->attach_pdf) {
            $pdfPath = $this->renderDigestPdf($type, $data);
        }

        // A. Email Delivery
        if ($report->delivery_method === 'email' || $report->delivery_method === 'both') {
            try {
                Mail::to($email)->send(new ScheduledDigestMail($report->name, $htmlContent, $pdfPath));
            } catch (\Exception $e) {
                \Log::error("Gagal mengirim email digest terjadwal ke {$email}: " . $e->getMessage());
            }
        }

        // B. WhatsApp Delivery
        if ($report->delivery_method === 'whatsapp' || $report->delivery_method === 'both') {
            if (!empty($phone)) {
                $smsText = " SISTA INFO DIGEST: *{$report->name}*\n\n";
                if ($type === 'daily_admin') {
                    $smsText .= "• Alpa Hari Ini: {$data['alpa_count']} Siswa\n• Tiket Baru: {$data['new_tickets']}\n• SPP Baru: {$data['new_billings']}\n• Smart Alerts: {$data['active_alerts']}\n\nHarap login ke dashboard admin SISTA.";
                } elseif ($type === 'weekly_kepsek') {
                    $smsText .= "• Rata-rata Kehadiran: {$data['attendance_rate']}%\n• Penerimaan SPP: Rp " . number_format($data['total_payments'], 0, ',', '.') . "\n• Target Mutu: {$data['completed_milestones']}/{$data['total_milestones']}\n\nHarap periksa email Anda untuk lampiran laporan PDF lengkap.";
                } elseif ($type === 'monthly_finance') {
                    $smsText .= "• Diterima: Rp " . number_format($data['total_received'], 0, ',', '.') . "\n• Sisa Tunggakan: Rp " . number_format($data['total_outstanding'], 0, ',', '.') . "\n\nLaporan keuangan PDF lengkap telah dikirimkan ke email Anda.";
                } elseif ($type === 'weekly_parent') {
                    $smsText .= "Bapak/Ibu Wali dari *{$data['student_name']}*:\n• Absen Alpa: {$data['alpa_count']} kali\n• Terlambat: {$data['late_count']} kali\n• Nilai & PR tertunda dapat diperiksa di email Anda atau portal SISTA.";
                }

                try {
                    $this->whatsappService->sendMessage($phone, $smsText, 'scheduled_digest');
                } catch (\Exception $e) {
                    \Log::error("Gagal mengirim WhatsApp digest terjadwal ke {$phone}: " . $e->getMessage());
                }
            }
        }

        // Clean up temporary PDF file if generated
        if ($pdfPath && file_exists($pdfPath)) {
            @unlink($pdfPath);
        }
    }
}
