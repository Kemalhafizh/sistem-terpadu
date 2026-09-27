<?php

namespace App\Services;

use App\Models\Billing;
use App\Models\Exam;
use App\Models\MutabaahWeeklySummary;
use App\Models\Student;
use App\Models\TahfidzTarget;
use App\Models\User;
use App\Models\WhatsappAutomation;
use App\Models\WhatsappLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class WhatsappAutomationService
{
    protected $whatsappService;

    public function __construct(WhatsappService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Parse message placeholders
     */
    public function parseMessage(string $content, array $data): string
    {
        $placeholders = [
            '{nama_siswa}' => $data['nama_siswa'] ?? '',
            '{nama_orang_tua}' => $data['nama_orang_tua'] ?? '',
            '{kelas}' => $data['kelas'] ?? '',
            '{tagihan}' => $data['tagihan'] ?? '',
            '{jenis_tagihan}' => $data['jenis_tagihan'] ?? '',
            '{batas_waktu}' => $data['batas_waktu'] ?? '',
            '{tanggal}' => $data['tanggal'] ?? '',
            '{mata_pelajaran}' => $data['mata_pelajaran'] ?? '',
            '{tanggal_ujian}' => $data['tanggal_ujian'] ?? '',
            '{jam_ujian}' => $data['jam_ujian'] ?? '',
            '{keluhan}' => $data['keluhan'] ?? '',
            '{diagnosa}' => $data['diagnosa'] ?? '',
            '{penanganan}' => $data['penanganan'] ?? '',
            '{gejala}' => $data['gejala'] ?? '',
            '{disposisi}' => $data['disposisi'] ?? '',
            '{nilai}' => $data['nilai'] ?? '',
            '{kkm}' => $data['kkm'] ?? '',
            '{judul_penilaian}' => $data['judul_penilaian'] ?? '',
            '{skor_total}' => $data['skor_total'] ?? '',
            '{grade}' => $data['grade'] ?? '',
            '{target_juz}' => $data['target_juz'] ?? '',
            '{capaian_juz}' => $data['capaian_juz'] ?? '',
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $content);
    }

    /**
     * Dispatch an instant trigger notification
     */
    public function triggerInstant(string $triggerType, $notifiable, array $contextData): ?WhatsappLog
    {
        // 1. Find active automation rule for trigger
        $automation = WhatsappAutomation::where('trigger', $triggerType)
            ->where('is_active', true)
            ->with('template')
            ->first();

        if (! $automation || ! $automation->template || ! $automation->template->is_active) {
            return null;
        }

        // 2. Resolve recipient phone number
        $to = null;
        if ($notifiable instanceof User) {
            $to = $notifiable->phone_number;
        } elseif ($notifiable instanceof Student) {
            $to = $notifiable->parent?->phone_number;
        }

        if (! $to) {
            Log::warning("WhatsApp instant trigger {$triggerType} cancelled: No phone number found for notifiable.");

            return null;
        }

        // 3. Compile message
        $parsedMessage = $this->parseMessage($automation->template->content, $contextData);

        // 4. Send message
        return $this->whatsappService->sendMessage($to, $parsedMessage, 'automation_'.$triggerType);
    }

    /**
     * Run all scheduled triggers (called daily or periodically)
     */
    public function runScheduledTriggers(): void
    {
        $this->processBillingDue3Days();
        $this->processBillingOverdue();
        $this->processExamUpcoming1Day();
        $this->processMutabaahGradeD();
        $this->processTahfidzBehindTarget();
    }

    protected function processBillingDue3Days(): void
    {
        $automation = WhatsappAutomation::where('trigger', 'billing_due_3_days')
            ->where('is_active', true)
            ->with('template')
            ->first();

        if (! $automation) {
            return;
        }

        $targetDate = Carbon::today()->addDays(3)->toDateString();
        $billings = Billing::whereIn('status', ['unpaid', 'partial'])
            ->whereDate('due_date', $targetDate)
            ->with(['student.parent', 'category'])
            ->get();

        foreach ($billings as $billing) {
            $student = $billing->student;
            $parent = $student?->parent;

            if ($parent && $parent->phone_number) {
                $context = [
                    'nama_siswa' => $student->name,
                    'nama_orang_tua' => $parent->name,
                    'jenis_tagihan' => $billing->category?->name ?? 'SPP',
                    'tagihan' => 'Rp '.number_format($billing->remaining_balance, 0, ',', '.'),
                    'batas_waktu' => $billing->due_date->format('d-m-Y'),
                ];

                $message = $this->parseMessage($automation->template->content, $context);
                $this->whatsappService->sendMessage($parent->phone_number, $message, 'automation_billing_due_3_days');
            }
        }
    }

    protected function processBillingOverdue(): void
    {
        $automation = WhatsappAutomation::where('trigger', 'billing_overdue')
            ->where('is_active', true)
            ->with('template')
            ->first();

        if (! $automation) {
            return;
        }

        $billings = Billing::whereIn('status', ['unpaid', 'partial'])
            ->whereDate('due_date', '<', Carbon::today())
            ->with(['student.parent', 'category'])
            ->get();

        foreach ($billings as $billing) {
            $student = $billing->student;
            $parent = $student?->parent;

            if ($parent && $parent->phone_number) {
                $context = [
                    'nama_siswa' => $student->name,
                    'nama_orang_tua' => $parent->name,
                    'jenis_tagihan' => $billing->category?->name ?? 'SPP',
                    'tagihan' => 'Rp '.number_format($billing->remaining_balance, 0, ',', '.'),
                    'batas_waktu' => $billing->due_date->format('d-m-Y'),
                ];

                $message = $this->parseMessage($automation->template->content, $context);
                $this->whatsappService->sendMessage($parent->phone_number, $message, 'automation_billing_overdue');
            }
        }
    }

    protected function processExamUpcoming1Day(): void
    {
        $automation = WhatsappAutomation::where('trigger', 'exam_upcoming_1_day')
            ->where('is_active', true)
            ->with('template')
            ->first();

        if (! $automation) {
            return;
        }

        $tomorrow = Carbon::tomorrow();
        $exams = Exam::whereDate('start_time', $tomorrow->toDateString())
            ->with(['classroom.students.parent', 'subject'])
            ->get();

        foreach ($exams as $exam) {
            $students = $exam->classroom?->students ?? [];
            foreach ($students as $student) {
                $parent = $student->parent;
                if ($parent && $parent->phone_number) {
                    $context = [
                        'nama_siswa' => $student->name,
                        'kelas' => $exam->classroom?->name ?? '',
                        'mata_pelajaran' => $exam->subject?->name ?? '',
                        'tanggal_ujian' => $exam->start_time->format('d-m-Y'),
                        'jam_ujian' => $exam->start_time->format('H:i'),
                    ];

                    $message = $this->parseMessage($automation->template->content, $context);
                    $this->whatsappService->sendMessage($parent->phone_number, $message, 'automation_exam_upcoming_1_day');
                }
            }
        }
    }

    protected function processMutabaahGradeD(): void
    {
        $automation = WhatsappAutomation::where('trigger', 'mutabaah_grade_d')
            ->where('is_active', true)
            ->with('template')
            ->first();

        if (! $automation) {
            return;
        }

        // Get summaries for previous week or current incomplete week that have grade D
        $summaries = MutabaahWeeklySummary::where('grade', 'D')
            ->where('parent_acknowledged', false)
            ->whereDate('week_end', '>=', Carbon::today()->subDays(7))
            ->with('student.parent')
            ->get();

        foreach ($summaries as $summary) {
            $student = $summary->student;
            $parent = $student?->parent;

            if ($parent && $parent->phone_number) {
                $context = [
                    'nama_siswa' => $student->name,
                    'nama_orang_tua' => $parent->name,
                    'kelas' => $student->classroom?->name ?? '',
                    'skor_total' => $summary->total_score,
                    'grade' => $summary->grade,
                ];

                $message = $this->parseMessage($automation->template->content, $context);
                $this->whatsappService->sendMessage($parent->phone_number, $message, 'automation_mutabaah_grade_d');
            }
        }
    }

    protected function processTahfidzBehindTarget(): void
    {
        $automation = WhatsappAutomation::where('trigger', 'tahfidz_behind_target')
            ->where('is_active', true)
            ->with('template')
            ->first();

        if (! $automation) {
            return;
        }

        $targets = TahfidzTarget::where('status', 'behind')
            ->with('student.parent')
            ->get();

        foreach ($targets as $target) {
            $student = $target->student;
            $parent = $student?->parent;

            if ($parent && $parent->phone_number) {
                $context = [
                    'nama_siswa' => $student->name,
                    'nama_orang_tua' => $parent->name,
                    'kelas' => $student->classroom?->name ?? '',
                    'target_juz' => $target->target_juz,
                    'capaian_juz' => $target->achieved_juz,
                ];

                $message = $this->parseMessage($automation->template->content, $context);
                $this->whatsappService->sendMessage($parent->phone_number, $message, 'automation_tahfidz_behind_target');
            }
        }
    }
}
