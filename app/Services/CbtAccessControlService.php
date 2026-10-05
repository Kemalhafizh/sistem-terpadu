<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;

class CbtAccessControlService
{
    /**
     * Generate a 6-character alphanumeric UPPERCASE token for exam entry.
     */
    public function generateToken(Exam $exam): string
    {
        $token = strtoupper(Str::random(6));

        // Ensure no ambiguous characters (0/O, 1/I/L)
        // Token CBT berlaku tepat 5 Menit (300 detik) sesuai standar integritas ANBK/Pusmendik
        $expiresAt = now()->addMinutes(5);

        $exam->update([
            'access_token' => $token,
            'token_expires_at' => $expiresAt,
        ]);

        return $token;
    }

    /**
     * Regenerate a new token (e.g., if the old one leaked).
     */
    public function regenerateToken(Exam $exam): string
    {
        return $this->generateToken($exam);
    }

    /**
     * Check if a user is authorized to view the exam token.
     *
     * UTS/UAS: Only the designated operator (exam.operator_id) can see the token.
     * Ulangan Harian/Try Out: Only the exam creator (exam.created_by) can see the token.
     */
    public function canViewToken(User $user, Exam $exam): bool
    {
        // Only superadmin bypasses the rule; admin does not (CLAUDE.md, rule 3).
        if ($user->role === 'superadmin') {
            return true;
        }

        if (in_array($exam->type, ['uts', 'uas'])) {
            // Only the designated operator
            return $exam->operator_id !== null && $user->id === $exam->operator_id;
        }

        // Ulangan harian / try_out: only the creator
        return $user->id === $exam->created_by;
    }

    /**
     * Exams this user personally operates — the same ownership rule as
     * canViewToken() (minus the admin bypass), so every exam returned here
     * can actually be opened in the proctor screen. Only published exams that
     * have not ended yet are relevant for proctoring.
     */
    public function examsOperatedBy(User $user)
    {
        return Exam::query()
            ->where('status', 'published')
            ->where('end_time', '>=', now())
            ->where(function ($q) use ($user) {
                $q->where(function ($q) use ($user) {
                    $q->whereIn('type', ['uts', 'uas'])->where('operator_id', $user->id);
                })->orWhere(function ($q) use ($user) {
                    $q->whereNotIn('type', ['uts', 'uas'])->where('created_by', $user->id);
                });
            })
            ->with(['subject', 'classroom'])
            ->orderBy('start_time');
    }

    /**
     * Check if the given platform is allowed for this exam.
     */
    public function isPlatformAllowed(Exam $exam, string $platform): bool
    {
        if (! $exam->mobile_only) {
            return true; // Any platform allowed
        }

        return $platform === 'android';
    }

    /**
     * Validate a student's token submission.
     *
     * Returns ['valid' => bool, 'message' => string, 'attempt' => ?ExamAttempt]
     */
    public function validateStudentToken(Exam $exam, Student $student, string $token, string $platform): array
    {
        // 1. Check platform
        if (! $this->isPlatformAllowed($exam, $platform)) {
            return [
                'valid' => false,
                'message' => 'Ujian ini hanya dapat dikerjakan melalui Aplikasi SISTA di HP Anda.',
            ];
        }

        // 2. Check token matches
        if (strtoupper($token) !== strtoupper($exam->access_token)) {
            return [
                'valid' => false,
                'message' => 'Token tidak valid. Silakan periksa kembali kode token dari pengawas ujian.',
            ];
        }

        // 2b. Check token expiration (Maksimal 5 Menit / 300 Detik)
        if ($exam->isTokenExpired()) {
            return [
                'valid' => false,
                'message' => 'Token telah kedaluwarsa (masa berlaku 5 menit telah habis). Silakan minta token baru yang di-refresh oleh Pengawas/Operator.',
            ];
        }

        // 3. Check exam is ongoing
        if (! $exam->isOngoing()) {
            return [
                'valid' => false,
                'message' => 'Ujian belum dimulai atau sudah berakhir.',
            ];
        }

        // 4. Check if already submitted/graded
        $existingAttempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->latest()
            ->first();

        if ($existingAttempt && in_array($existingAttempt->status, ['submitted', 'graded'])) {
            return [
                'valid' => false,
                'message' => 'Anda sudah menyelesaikan ujian ini.',
            ];
        }

        // 5. If force_closed, check if it has been reset
        if ($existingAttempt && $existingAttempt->status === 'force_closed') {
            return [
                'valid' => false,
                'message' => 'Ujian Anda sebelumnya ditutup paksa. Minta guru/operator untuk mereset status Anda terlebih dahulu.',
            ];
        }

        // 6. Create or update attempt with token validation info
        $attempt = ExamAttempt::updateOrCreate(
            ['exam_id' => $exam->id, 'student_id' => $student->id],
            [
                'uuid' => $existingAttempt?->uuid ?? (string) Str::uuid(),
                'status' => 'in_progress',
                'started_at' => $existingAttempt?->started_at ?? now(),
                'access_platform' => $platform,
                'used_token' => strtoupper($token),
                'token_validated_at' => now(),
                'force_closed' => false,
                'force_close_reason' => null,
            ]
        );

        return [
            'valid' => true,
            'message' => 'Token valid. Selamat mengerjakan ujian!',
            'attempt' => $attempt,
        ];
    }

    /**
     * Force-close a student's attempt (triggered when student leaves the app).
     */
    public function forceCloseAttempt(Exam $exam, Student $student, string $reason, ?array $answersSnapshot): ExamAttempt
    {
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->latest()
            ->firstOrFail();

        // Merge snapshot with existing answers if any (preserve string keys)
        $mergedAnswers = $attempt->answers ?? [];
        foreach ($answersSnapshot ?? [] as $key => $val) {
            $mergedAnswers[(string) $key] = $val;
        }

        $attempt->update([
            'status' => 'force_closed',
            'force_closed' => true,
            'force_close_reason' => $reason,
            'submitted_at' => now(),
            'answers' => $mergedAnswers,
        ]);

        return $attempt->fresh();
    }

    /**
     * Reset a force_closed attempt so the student can re-enter with a new token.
     * Only the operator (UTS/UAS) or creator (Ulangan Harian) can do this.
     */
    public function resetForceClose(Exam $exam, Student $student, User $operator): ExamAttempt
    {
        // Verify the operator has permission
        if (! $this->canViewToken($operator, $exam)) {
            throw new AuthorizationException(
                __('Anda tidak memiliki hak untuk mereset status ujian siswa ini.')
            );
        }

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'force_closed')
            ->latest()
            ->firstOrFail();

        $attempt->update([
            'status' => 'in_progress',
            'force_closed' => false,
            'force_close_reason' => null,
            'submitted_at' => null,
            // Keep existing answers — student continues from where they left off
        ]);

        return $attempt->fresh();
    }
}
