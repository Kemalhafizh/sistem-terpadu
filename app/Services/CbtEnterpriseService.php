<?php

namespace App\Services;

use App\Events\ProctorInterventionEvent;
use App\Models\CbtEssaySubmission;
use App\Models\CbtProctorLog;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;

class CbtEnterpriseService
{
    protected CbtService $cbtService;

    public function __construct(CbtService $cbtService)
    {
        $this->cbtService = $cbtService;
    }

    /**
     * Generate an AES-256-CBC encrypted payload of exam questions for zero-downtime pre-fetching.
     */
    public function generateEncryptedPayload(Exam $exam, Student $student): array
    {
        // Pre-fetched before the student enters the exam, so it must not create
        // the attempt (only validate-token does). Without one, the seeded order
        // is computed without persisting; it is the same order the attempt
        // stores later.
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->latest()
            ->first();

        // Same per-student order as GET student/cbt/exams/{id}/questions and the web take view.
        $payload = [];
        foreach ($this->cbtService->buildStudentQuestionSet($exam, $student, $attempt) as $item) {
            $q = $item['question'];
            $payload[] = [
                'id' => $q->id,
                'number' => $item['number'],
                'type' => $q->type,
                'question_text' => $q->question_text,
                'image_url' => $q->question_image ? url($q->question_image) : null,
                'options' => $item['options'],
            ];
        }

        $jsonPayload = json_encode([
            'exam_id' => $exam->id,
            'title' => $exam->title,
            'subject' => $exam->subject->name ?? 'Mata Pelajaran',
            'duration_minutes' => (int) $exam->duration_minutes,
            'total_questions' => count($payload),
            'questions' => $payload,
        ]);

        $key = $this->deriveExamKey($exam);
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($jsonPayload, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

        return [
            'exam_id' => $exam->id,
            'encrypted_data' => base64_encode($encrypted),
            'iv' => base64_encode($iv),
            'format' => 'aes-256-cbc',
            'fetched_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Get the decryption key for an exam if the scheduled time has arrived.
     */
    public function getDecryptionKey(Exam $exam, Student $student): array
    {
        // Allow decryption starting 10 minutes before exam start up until exam end
        $graceStart = $exam->start_time ? $exam->start_time->copy()->subMinutes(10) : now()->subMinutes(10);
        $isAccessible = now()->gte($graceStart);

        if (! $isAccessible) {
            return [
                'accessible' => false,
                'message' => 'Ujian belum dimulai. Kunci dekripsi akan dibagikan saat waktu ujian tiba.',
                'starts_at' => $exam->start_time ? $exam->start_time->toIso8601String() : null,
            ];
        }

        // The payload is useless without the key, so the key is what the token gates.
        if (! ExamAttempt::openFor($exam, $student)) {
            return [
                'accessible' => false,
                'message' => __('Masukkan token ujian dari pengawas terlebih dahulu.'),
            ];
        }

        $key = $this->deriveExamKey($exam);

        return [
            'accessible' => true,
            'exam_id' => $exam->id,
            'key' => base64_encode($key),
            'valid_until' => $exam->end_time ? $exam->end_time->toIso8601String() : now()->addHours(3)->toIso8601String(),
        ];
    }

    /**
     * Process micro-batched student answers using fast Cache/Redis and update ExamAttempt.
     */
    public function processMicroBatchAnswers(Exam $exam, Student $student, array $answers): array
    {
        // Zero Trust: Enforce state machine integrity
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        if ($attempt && in_array($attempt->status, ['submitted', 'completed', 'blocked_violation'])) {
            abort(403, 'Akses ditolak: Ujian telah selesai atau dikunci oleh pengawas.');
        }

        // Only an attempt opened through validate-token accepts answers; never create one here.
        $attempt = ExamAttempt::openFor($exam, $student);
        if (! $attempt) {
            abort(403, __('Masukkan token ujian dari pengawas terlebih dahulu.'));
        }

        $cacheKey = "cbt_answers_{$exam->id}_{$student->id}";
        $existing = Cache::get($cacheKey, []);
        foreach ($answers as $qId => $ans) {
            $existing[(string) $qId] = (string) $ans;
        }
        $merged = $existing;

        // Store in Redis/Cache for 6 hours
        Cache::put($cacheKey, $merged, now()->addHours(6));

        $attempt->update([
            'answers' => $merged,
            'status' => 'in_progress',
        ]);

        return [
            'synced_count' => count($merged),
            'synced_at' => now()->toIso8601String(),
            'status' => 'saved',
        ];
    }

    /**
     * Handle student essay handwritten sheet photo upload with compression.
     */
    public function handleEssayUpload(Exam $exam, Student $student, int $questionId, UploadedFile $file, ?string $notes = null): CbtEssaySubmission
    {
        // Zero Trust: Enforce attempt state & question ownership
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        if ($attempt && in_array($attempt->status, ['submitted', 'completed', 'blocked_violation'])) {
            abort(403, 'Akses ditolak: Ujian telah selesai atau dikunci oleh pengawas.');
        }

        // Only an attempt opened through validate-token accepts answers; never create one here.
        $attempt = ExamAttempt::openFor($exam, $student);
        if (! $attempt) {
            abort(403, __('Masukkan token ujian dari pengawas terlebih dahulu.'));
        }

        $questionBelongsToExam = $exam->questions()->where('question_banks.id', $questionId)->exists();
        if (! $questionBelongsToExam) {
            abort(422, 'Soal tidak terdaftar dalam paket ujian ini.');
        }

        $filename = 'essay_'.$exam->id.'_'.$student->id.'_'.$questionId.'_'.time().'.jpg';
        $destinationPath = storage_path('app/public/cbt/essays/'.$exam->id);
        if (! file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $targetFile = $destinationPath.'/'.$filename;
        $fileSize = $file->getSize();

        // Compress image using GD if available
        if (function_exists('imagecreatefromstring') && function_exists('imagejpeg')) {
            $sourceData = file_get_contents($file->getRealPath());
            $image = @imagecreatefromstring($sourceData);
            if ($image !== false) {
                // Resize if width > 1200
                $origWidth = imagesx($image);
                $origHeight = imagesy($image);
                if ($origWidth > 1200) {
                    $newWidth = 1200;
                    $newHeight = (int) ($origHeight * (1200 / $origWidth));
                    $resized = imagecreatetruecolor($newWidth, $newHeight);
                    imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
                    imagedestroy($image);
                    $image = $resized;
                }
                // Save compressed with quality 75
                imagejpeg($image, $targetFile, 75);
                imagedestroy($image);
                $fileSize = file_exists($targetFile) ? filesize($targetFile) : $fileSize;
            } else {
                $file->move($destinationPath, $filename);
            }
        } else {
            $file->move($destinationPath, $filename);
        }

        $relativePath = 'uploads/cbt/essays/'.$exam->id.'/'.$filename;

        return CbtEssaySubmission::updateOrCreate(
            [
                'exam_attempt_id' => $attempt->id,
                'question_id' => $questionId,
            ],
            [
                'file_path' => $relativePath,
                'file_size' => $fileSize,
                'mime_type' => 'image/jpeg',
                'student_notes' => $notes,
            ]
        );
    }

    /**
     * Proctor broadcasts a warning modal to student(s).
     */
    public function proctorBroadcastWarning(int $examId, ?int $studentId, string $message, int $proctorId): CbtProctorLog
    {
        $log = CbtProctorLog::create([
            'exam_id' => $examId,
            'student_id' => $studentId,
            'proctor_id' => $proctorId,
            'action_type' => 'warning_modal',
            'reason' => $message,
            'payload' => ['message' => $message],
        ]);

        // Exam-wide channel (cbt.exam.{examId}) is proctor-only — a student
        // taking the exam is never authorized on it (see routes/channels.php),
        // since it also carries StudentCheatedEvent, which must stay private
        // to proctors. So a "broadcast to everyone" (student_id null) still
        // needs one event per student on their own private per-student
        // channel to actually reach any exam-taker's screen — dispatching
        // once with studentId=null would otherwise silently go nowhere.
        broadcast(new ProctorInterventionEvent($examId, $studentId, 'WARNING_MODAL', $message));

        if ($studentId === null) {
            ExamAttempt::where('exam_id', $examId)
                ->where('status', 'in_progress')
                ->pluck('student_id')
                ->each(function (int $activeStudentId) use ($examId, $message) {
                    broadcast(new ProctorInterventionEvent($examId, $activeStudentId, 'WARNING_MODAL', $message));
                });
        }

        return $log;
    }

    /**
     * Proctor forces submit and locks an attempt for severe cheating.
     */
    public function proctorForceSubmit(int $examId, int $studentId, string $reason, int $proctorId): CbtProctorLog
    {
        $attempt = ExamAttempt::where('exam_id', $examId)
            ->where('student_id', $studentId)
            ->first();

        if ($attempt) {
            $attempt->update([
                'status' => 'blocked_violation',
                'submitted_at' => now(),
                'is_auto_submitted' => true,
            ]);
        }

        $log = CbtProctorLog::create([
            'exam_id' => $examId,
            'student_id' => $studentId,
            'proctor_id' => $proctorId,
            'action_type' => 'force_submit',
            'reason' => $reason,
            'payload' => ['reason' => $reason],
        ]);

        broadcast(new ProctorInterventionEvent($examId, $studentId, 'FORCE_SUBMIT', $reason));

        return $log;
    }

    /**
     * Proctor extends time for a student.
     */
    public function proctorExtendTime(int $examId, int $studentId, int $minutes, int $proctorId): CbtProctorLog
    {
        $log = CbtProctorLog::create([
            'exam_id' => $examId,
            'student_id' => $studentId,
            'proctor_id' => $proctorId,
            'action_type' => 'extend_time',
            'reason' => "Tambahan waktu {$minutes} menit.",
            'payload' => ['extra_minutes' => $minutes],
        ]);

        broadcast(new ProctorInterventionEvent($examId, $studentId, 'EXTEND_TIME', "Waktu ujian diperpanjang {$minutes} menit.", $minutes));

        return $log;
    }

    /**
     * Derive a cryptographic 32-byte key for an exam incorporating application secret.
     */
    protected function deriveExamKey(Exam $exam): string
    {
        $appKey = (string) config('app.key', 'cbt_default_salt_super_web_2026');

        return hash_hmac('sha256', "cbt_exam_{$exam->id}_{$exam->uuid}_{$exam->created_at}", $appKey, true);
    }
}
