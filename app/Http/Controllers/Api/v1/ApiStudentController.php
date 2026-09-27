<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceResource;
use App\Http\Resources\BillingResource;
use App\Http\Resources\GradeResource;
use App\Http\Resources\MutabaahLogResource;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\PointLogResource;
use App\Http\Resources\ScheduleResource;
use App\Http\Resources\StudentResource;
use App\Http\Resources\TahfidzLogResource;
use App\Models\Billing;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Grade;
use App\Models\QuestionBank;
use App\Models\Schedule;
use App\Services\CbtService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * @group Student Scoped Data v1
 *
 * API endpoint khusus untuk peran Siswa guna melihat data akademik, jadwal, kehadiran, BK, dan keuangan mereka.
 */
class ApiStudentController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get Student Profile
     */
    public function profile(Request $request)
    {
        $student = $request->user()->student;

        if (!$student) {
            return $this->errorResponse('Data siswa tidak ditemukan', 404);
        }

        return $this->successResponse(new StudentResource($student), 'Profil siswa berhasil diambil');
    }

    /**
     * Get Student Schedule
     */
    public function schedule(Request $request)
    {
        $student = $request->user()->student;

        if (!$student || !$student->classroom_id) {
            return $this->successResponse([], 'Belum terdaftar di kelas manapun');
        }

        $schedules = Schedule::where('classroom_id', $student->classroom_id)
            ->with(['subject', 'teacher', 'classroom'])
            ->get();

        return $this->successResponse(ScheduleResource::collection($schedules), 'Jadwal pelajaran berhasil diambil');
    }

    /**
     * Get Student Grades
     */
    public function grades(Request $request)
    {
        $student = $request->user()->student;

        if (!$student) {
            return $this->errorResponse('Data siswa tidak ditemukan', 404);
        }

        $grades = Grade::where('student_id', $student->id)
            ->with('subject')
            ->latest()
            ->get();

        return $this->successResponse(GradeResource::collection($grades), 'Data nilai berhasil diambil');
    }

    /**
     * Get Student Attendance
     */
    public function attendance(Request $request)
    {
        $student = $request->user()->student;

        if (!$student) {
            return $this->errorResponse('Data siswa tidak ditemukan', 404);
        }

        $attendances = $student->attendances()
            ->latest()
            ->take(30)
            ->get();

        return $this->successResponse(AttendanceResource::collection($attendances), 'Data absensi berhasil diambil');
    }

    /**
     * Get Student Billings
     */
    public function billings(Request $request)
    {
        $student = $request->user()->student;

        if (!$student) {
            return $this->errorResponse('Data siswa tidak ditemukan', 404);
        }

        $billings = $student->billings()
            ->with('category')
            ->latest()
            ->get();

        return $this->successResponse(BillingResource::collection($billings), 'Data tagihan berhasil diambil');
    }

    /**
     * Get Student BK Points
     */
    public function points(Request $request)
    {
        $student = $request->user()->student;

        if (!$student) {
            return $this->errorResponse('Data siswa tidak ditemukan', 404);
        }

        $points = $student->pointLogs()
            ->with('category')
            ->latest()
            ->get();

        return $this->successResponse(PointLogResource::collection($points), 'Data poin BK berhasil diambil');
    }

    /**
     * Get Student Tahfidz Records
     */
    public function tahfidz(Request $request)
    {
        $student = $request->user()->student;

        if (!$student) {
            return $this->errorResponse('Data siswa tidak ditemukan', 404);
        }

        $tahfidzLogs = $student->tahfidzLogs()->latest()->get();

        return $this->successResponse(TahfidzLogResource::collection($tahfidzLogs), 'Data Tahfidz Qur\'an berhasil diambil');
    }

    /**
     * Get Student Mutaba'ah Daily Worship Records
     */
    public function mutabaah(Request $request)
    {
        $student = $request->user()->student;

        if (!$student) {
            return $this->errorResponse('Data siswa tidak ditemukan', 404);
        }

        $mutabaahLogs = $student->mutabaahLogs()->with('item')->latest()->take(50)->get();

        return $this->successResponse(MutabaahLogResource::collection($mutabaahLogs), 'Data Mutaba\'ah Yaumiyah berhasil diambil');
    }

    /**
     * Get Active CBT Exams for Student Classroom
     */
    public function cbtExams(Request $request)
    {
        $student = $request->user()->student;

        if (!$student) {
            return $this->errorResponse('Data siswa tidak ditemukan', 404);
        }

        $exams = Exam::where(function ($q) use ($student) {
            $q->where('classroom_id', $student->classroom_id)
                ->orWhereNull('classroom_id');
        })
            ->with('subject')
            ->latest()
            ->get();

        $data = $exams->map(function ($e) use ($student) {
            $attempt = ExamAttempt::where('exam_id', $e->id)->where('student_id', $student->id)->latest()->first();
            return [
                'id' => $e->id,
                'uuid' => $e->uuid,
                'title' => $e->title,
                'subject' => $e->subject->name ?? 'Mata Pelajaran',
                'type' => strtoupper($e->type ?? 'CBT'),
                'duration_minutes' => (int) ($e->duration_minutes ?? 60),
                'total_questions' => (int) ($e->total_questions ?: $e->questions()->count()),
                'passing_score' => (float) ($e->passing_score ?? 75.0),
                'status' => $e->status ?? 'published',
                'is_ongoing' => $e->isOngoing(),
                'has_attempted' => $attempt !== null,
                'score' => $attempt ? (float) $attempt->score : null,
            ];
        });

        return $this->successResponse($data, 'Daftar ujian CBT berhasil diambil');
    }

    /**
     * Get Questions for Specific CBT Exam
     */
    public function cbtExamQuestions(Request $request, $examId, CbtService $cbtService)
    {
        $student = $request->user()->student;
        if (!$student) {
            return $this->errorResponse('Data siswa tidak ditemukan', 404);
        }

        $exam = Exam::with('questions')->findOrFail($examId);

        // 1. Otorisasi kepesertaan siswa (kesesuaian kelas)
        Gate::authorize('take', $exam);

        // 2. Pastikan waktu ujian sedang berlangsung (mencegah kebocoran soal sebelum jadwal)
        if (!$exam->isOngoing()) {
            return $this->errorResponse('Ujian belum dibuka atau waktu ujian telah berakhir.', 403);
        }

        // 3. Pastikan attempt siswa tidak dalam status terkunci atau telah selesai
        $attempt = ExamAttempt::where('exam_id', $exam->id)->where('student_id', $student->id)->first();
        if ($attempt && in_array($attempt->status, ['submitted', 'blocked_violation', 'force_closed'], true)) {
            return $this->errorResponse('Sesi ujian telah selesai atau akses ditutup oleh pengawas.', 403);
        }

        // Honors shuffle_questions / shuffle_options with the same per-student
        // seeded order as the web take view and the encrypted vault payload.
        // Option keys stay the original ones (only display order changes), so
        // submit / micro-sync / force-close answers need no key mapping.
        $questions = collect($cbtService->buildStudentQuestionSet($exam, $student, $attempt))
            ->map(fn (array $item) => [
                'id' => $item['question']->id,
                'number' => $item['number'],
                'question_text' => $item['question']->question_text,
                'image_url' => $item['question']->question_image,
                'question_image' => $item['question']->question_image,
                'options' => $item['options'],
            ]);

        return $this->successResponse([
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'duration_minutes' => (int) ($exam->duration_minutes ?? 60),
                'total_questions' => count($questions),
            ],
            'questions' => $questions,
        ], 'Soal ujian CBT berhasil dimuat');
    }

    /**
     * Submit Answers for CBT Exam and Auto-Grade
     */
    public function cbtSubmitExam(Request $request, $examId, CbtService $cbtService)
    {
        $student = $request->user()->student;
        if (!$student) {
            return $this->errorResponse('Data siswa tidak ditemukan', 404);
        }

        $exam = Exam::with('questions')->findOrFail($examId);

        Gate::authorize('take', $exam);

        // Cegah manipulasi nilai / pengulangan submit jika status sudah diserahkan atau diblokir
        $existingAttempt = ExamAttempt::where('exam_id', $exam->id)->where('student_id', $student->id)->first();
        if ($existingAttempt && in_array($existingAttempt->status, ['submitted', 'blocked_violation', 'force_closed'], true)) {
            return $this->errorResponse('Ujian sudah pernah diselesaikan atau status sesi Anda terkunci.', 403);
        }
        $answers = $request->input('answers', []); // [question_id => selected_option_index]
        $totalQuestions = $exam->questions->count();
        $correctCount = 0;

        // Graded by original option key, so the per-student shuffled display
        // order (see cbtExamQuestions) never affects scoring. A numeric answer
        // is treated as an index into the *authored* option order.
        foreach ($exam->questions as $q) {
            $userAns = $answers[$q->id] ?? null;
            if ($userAns === null || $userAns === '') {
                continue;
            }
            $authored = $cbtService->formatOptions(is_array($q->options) ? $q->options : []);
            $userKey = $this->resolveOptionKey($userAns, $authored);
            $correctKey = $this->resolveOptionKey($q->correct_answer, $authored);
            if (($userKey !== null && $userKey === $correctKey) || (string) $userAns === (string) $q->correct_answer) {
                $correctCount++;
            }
        }

        $score = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100, 1) : 0;
        $passed = $score >= (float) ($exam->passing_score ?? 75.0);

        ExamAttempt::updateOrCreate(
            ['exam_id' => $exam->id, 'student_id' => $student->id],
            [
                'score' => $score,
                'correct_count' => $correctCount,
                'wrong_count' => max(0, $totalQuestions - $correctCount),
                'unanswered_count' => max(0, $totalQuestions - count($answers)),
                'answers' => $answers,
                'status' => 'submitted',
                'submitted_at' => now(),
            ]
        );

        return $this->successResponse([
            'exam_id' => $exam->id,
            'title' => $exam->title,
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctCount,
            'wrong_answers' => max(0, $totalQuestions - $correctCount),
            'score' => $score,
            'passed' => $passed,
            'submitted_at' => now()->format('Y-m-d H:i:s'),
        ], 'Hasil ujian CBT berhasil dinilai secara otomatis');
    }

    /**
     * Map an answer / answer key (option key, authored index, or option text)
     * to the original option key, or null if it matches none.
     */
    private function resolveOptionKey($value, array $authoredOptions): ?string
    {
        $value = trim((string) $value);
        foreach ($authoredOptions as $opt) {
            if (strcasecmp($opt['key'], $value) === 0) {
                return $opt['key'];
            }
        }
        if (ctype_digit($value) && isset($authoredOptions[(int) $value])) {
            return $authoredOptions[(int) $value]['key'];
        }
        foreach ($authoredOptions as $opt) {
            if ($opt['text'] !== '' && $opt['text'] === $value) {
                return $opt['key'];
            }
        }

        return null;
    }

    /**
     * Generate Payment VA Simulator for Billing
     */
    public function payBilling(Request $request, $billingId)
    {
        $student = $request->user()->student;
        if (!$student) {
            return $this->errorResponse('Data siswa tidak ditemukan', 404);
        }

        $billing = $student->billings()->findOrFail($billingId);
        $bank = $request->input('bank', 'BCA');

        $vaPrefix = match (strtoupper($bank)) {
            'MANDIRI' => '88908',
            'BNI' => '98888',
            'BRI' => '12888',
            default => '77888',
        };
        $vaNumber = $vaPrefix . str_pad((string) ($student->nis ?? '001'), 11, '0', STR_PAD_LEFT);

        return $this->successResponse([
            'billing_id' => $billing->id,
            'category' => $billing->category->name ?? 'SPP',
            'amount' => (float) $billing->amount,
            'amount_formatted' => 'Rp ' . number_format($billing->amount, 0, ',', '.'),
            'bank' => strtoupper($bank),
            'va_number' => $vaNumber,
            'status' => 'pending',
            'expiry_time' => now()->addHours(24)->format('Y-m-d H:i:s'),
            'qr_content' => "00020101021226590014ID.LINKAJA.WWW011893600000000000000002150000000000000005204000053033605802ID5924SMA ISLAM SULTAN AGUNG 16008SEMARANG61055012362140710SPP-" . $billing->id . "6304",
        ], 'Simulasi transaksi pembayaran berhasil dibuat');
    }

    /**
     * Get Student Notifications
     */
    public function notifications(Request $request)
    {
        $user = $request->user();
        $notifications = $user->notifications()->latest()->take(50)->get();

        return $this->successResponse(NotificationResource::collection($notifications), 'Notifikasi berhasil diambil');
    }

    /**
     * Mark Notification as Read
     */
    public function markNotificationRead(Request $request, $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        return $this->successResponse(null, 'Notifikasi ditandai telah dibaca');
    }
}

