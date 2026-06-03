<?php

namespace App\Services;

use App\Models\ChatbotFaq;
use App\Models\ChatbotConversation;
use App\Models\User;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Billing;
use App\Models\Ticket;
use App\Models\TeachingJournal;
use Illuminate\Support\Str;

class ChatbotService
{
    /**
     * Process user message, classify intent, and log the conversation.
     */
    public function processMessage(User $user, string $message): array
    {
        $normalizedMessage = Str::lower(trim($message));
        
        // 1. Try to match Data Query Intent
        $dataResponse = $this->handleDataQueryIntent($user, $normalizedMessage);
        if ($dataResponse) {
            $conversation = $this->logConversation($user, $message, $dataResponse['reply'], 'query_data', $dataResponse['context'], 1.0);
            return [
                'reply' => $dataResponse['reply'],
                'intent' => 'query_data',
                'conversation_id' => $conversation->id,
            ];
        }

        // 2. Try to match Navigation Intent
        $navResponse = $this->handleNavigateIntent($normalizedMessage);
        if ($navResponse) {
            $reply = "Tentu! Saya dapat membantu Anda menavigasi ke sana. Klik tautan berikut untuk membuka: <a href=\"{$navResponse['url']}\" class=\"text-blue-400 hover:text-blue-300 font-semibold underline\">{$navResponse['label']}</a>.";
            $conversation = $this->logConversation($user, $message, $reply, 'navigate', $navResponse, 1.0);
            return [
                'reply' => $reply,
                'intent' => 'navigate',
                'conversation_id' => $conversation->id,
            ];
        }

        // 3. Try to match FAQ Intent
        $faqReply = $this->handleFaqIntent($normalizedMessage);
        if ($faqReply) {
            $conversation = $this->logConversation($user, $message, $faqReply, 'faq', null, 0.9);
            return [
                'reply' => $faqReply,
                'intent' => 'faq',
                'conversation_id' => $conversation->id,
            ];
        }

        // 4. Fallback (Unknown Intent)
        $fallbackReply = "Maaf, saya belum memahami pertanyaan Anda secara detail. Cobalah menanyakan hal lain seperti:<br/>" .
            "- 📊 *\"berapa siswa alpa hari ini\"* atau *\"total tunggakan spp\"*<br/>" .
            "- 🎯 *\"buka halaman nilai\"* atau *\"buka absensi\"*<br/>" .
            "- 💡 Pertanyaan umum tentang sistem SISTA.";
            
        $conversation = $this->logConversation($user, $message, $fallbackReply, 'unknown', null, 0.0);
        return [
            'reply' => $fallbackReply,
            'intent' => 'unknown',
            'conversation_id' => $conversation->id,
        ];
    }

    /**
     * Handle FAQ Intent via local database full-text-like keyword matching.
     */
    public function handleFaqIntent(string $message): ?string
    {
        $faqs = ChatbotFaq::active()->get();
        $bestMatch = null;
        $maxMatches = 0;

        foreach ($faqs as $faq) {
            $matches = 0;
            
            // Check in question title
            if (Str::contains($message, Str::lower($faq->question))) {
                $matches += 5;
            }

            // Check individual keywords
            if (is_array($faq->keywords)) {
                foreach ($faq->keywords as $kw) {
                    if (Str::contains($message, Str::lower($kw))) {
                        $matches += 2;
                    }
                }
            }

            // Record best match
            if ($matches > $maxMatches && $matches >= 2) {
                $maxMatches = $matches;
                $bestMatch = $faq;
            }
        }

        if ($bestMatch) {
            $bestMatch->increment('usage_count');
            return $bestMatch->answer;
        }

        return null;
    }

    /**
     * Match navigation intent and return target SISTA route URLs.
     */
    public function handleNavigateIntent(string $message): ?array
    {
        $navMap = [
            'absen' => ['url' => '/attendances', 'label' => 'Presensi Harian Siswa'],
            'kehadiran' => ['url' => '/attendances', 'label' => 'Presensi Harian Siswa'],
            'presensi' => ['url' => '/attendances', 'label' => 'Presensi Harian Siswa'],
            'jadwal' => ['url' => '/schedules', 'label' => 'Jadwal Pelajaran'],
            'schedule' => ['url' => '/schedules', 'label' => 'Jadwal Pelajaran'],
            'nilai' => ['url' => '/grades', 'label' => 'Input Nilai Siswa'],
            'grade' => ['url' => '/grades', 'label' => 'Input Nilai Siswa'],
            'rapor' => ['url' => '/report-cards', 'label' => 'Laporan Hasil Belajar (E-Rapor)'],
            'raport' => ['url' => '/report-cards', 'label' => 'Laporan Hasil Belajar (E-Rapor)'],
            'keuangan' => ['url' => '/finance', 'label' => 'Manajemen Keuangan & SPP'],
            'spp' => ['url' => '/finance', 'label' => 'Manajemen Keuangan & SPP'],
            'bayar' => ['url' => '/finance', 'label' => 'Manajemen Keuangan & SPP'],
            'billing' => ['url' => '/finance', 'label' => 'Manajemen Keuangan & SPP'],
            'survei' => ['url' => '/surveys', 'label' => 'Survei Digital Sekolah'],
            'survey' => ['url' => '/surveys', 'label' => 'Survei Digital Sekolah'],
            'forum' => ['url' => '/forum', 'label' => 'Forum Diskusi Akademik'],
            'diskusi' => ['url' => '/forum', 'label' => 'Forum Diskusi Akademik'],
            'belajar' => ['url' => '/elearning', 'label' => 'E-Learning SISTA Classroom'],
            'cbt' => ['url' => '/exams', 'label' => 'Ujian Online CBT'],
            'ujian' => ['url' => '/exams', 'label' => 'Ujian Online CBT'],
            'portofolio' => ['url' => '/portfolios', 'label' => 'E-Portfolio Siswa'],
            'prestasi' => ['url' => '/portfolios', 'label' => 'E-Portfolio Siswa'],
            'bantuan' => ['url' => '/tickets', 'label' => 'Helpdesk & Pusat Bantuan'],
            'tiket' => ['url' => '/tickets', 'label' => 'Helpdesk & Pusat Bantuan'],
            'siem' => ['url' => '/admin/security/dashboard', 'label' => 'Security Monitor SIEM'],
            'keamanan' => ['url' => '/admin/security/dashboard', 'label' => 'Security Monitor SIEM'],
        ];

        foreach ($navMap as $keyword => $data) {
            if (Str::contains($message, $keyword)) {
                return $data;
            }
        }

        return null;
    }

    /**
     * Map natural language data queries to secure Laravel Eloquent count results.
     */
    public function handleDataQueryIntent(User $user, string $message): ?array
    {
        // 1. "berapa siswa alpa hari ini"
        if (Str::contains($message, 'alpa') && Str::contains($message, 'hari ini')) {
            $count = Attendance::where('date', today()->toDateString())->where('status', 'A')->count();
            return [
                'reply' => "Jumlah siswa yang alpa (**tidak hadir tanpa keterangan**) hari ini adalah: **{$count}** siswa.",
                'context' => ['query' => 'absensi_alpa_today', 'result' => $count],
            ];
        }

        // 2. "total tunggakan spp"
        if (Str::contains($message, 'tunggakan') || (Str::contains($message, 'spp') && Str::contains($message, 'belum lunas'))) {
            $count = Billing::where('remaining_balance', '>', 0)->count();
            return [
                'reply' => "Total tagihan SPP yang **belum lunas** di sekolah saat ini berjumlah: **{$count}** tagihan.",
                'context' => ['query' => 'spp_tunggakan_count', 'result' => $count],
            ];
        }

        // 3. "berapa total siswa aktif"
        if (Str::contains($message, 'siswa aktif')) {
            $count = Student::where('status', 'active')->count();
            return [
                'reply' => "Total siswa yang **berstatus aktif** terdaftar saat ini: **{$count}** siswa.",
                'context' => ['query' => 'siswa_aktif_count', 'result' => $count],
            ];
        }

        // 4. "tiket helpdesk yang open" / "tiket open"
        if (Str::contains($message, 'tiket') && (Str::contains($message, 'open') || Str::contains($message, 'aktif'))) {
            $count = Ticket::where('status', 'open')->count();
            return [
                'reply' => "Jumlah tiket bantuan/helpdesk yang **berstatus Open** saat ini: **{$count}** tiket.",
                'context' => ['query' => 'tickets_open_count', 'result' => $count],
            ];
        }

        // 5. "guru yang belum isi jurnal hari ini"
        if (Str::contains($message, 'belum') && Str::contains($message, 'jurnal')) {
            $totalTeachers = User::where('role', 'guru')->count();
            $filledToday = TeachingJournal::whereDate('created_at', today())->distinct('user_id')->count();
            $count = max(0, $totalTeachers - $filledToday);
            
            return [
                'reply' => "Jumlah guru yang **belum mengisi jurnal mengajar** hari ini berjumlah: **{$count}** guru.",
                'context' => ['query' => 'teachers_no_journal_today', 'result' => $count],
            ];
        }

        return null;
    }

    /**
     * Log conversation in chatbot_conversations table.
     */
    private function logConversation(User $user, string $message, string $reply, string $intent, ?array $context, float $confidence): ChatbotConversation
    {
        return ChatbotConversation::create([
            'user_id' => $user->id,
            'user_message' => $message,
            'bot_response' => $reply,
            'intent' => $intent,
            'context_data' => $context,
            'confidence' => $confidence,
        ]);
    }
}
