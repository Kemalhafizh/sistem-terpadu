<?php

namespace App\Http\Controllers\Api\v1;

use App\Events\StudentCheatedEvent;
use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Student;
use App\Services\CbtAccessControlService;
use App\Services\CbtService;
use App\Traits\ApiResponseTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class CbtAccessControlApiController extends Controller
{
    use ApiResponseTrait;

    protected CbtAccessControlService $accessControlService;

    protected CbtService $cbtService;

    public function __construct(CbtAccessControlService $accessControlService, CbtService $cbtService)
    {
        $this->accessControlService = $accessControlService;
        $this->cbtService = $cbtService;
    }

    /**
     * Validate token submitted by student from Android app.
     */
    public function validateToken(Request $request, $id)
    {
        $request->validate([
            'token' => 'required|string|size:6',
        ]);

        $student = $request->user()->student;
        if (! $student) {
            return $this->errorResponse('Data siswa tidak ditemukan', 404);
        }

        $exam = Exam::findOrFail($id);
        $platform = strtolower($request->header('X-SISTA-Platform', $request->input('platform', 'web')));

        $result = $this->accessControlService->validateStudentToken(
            $exam,
            $student,
            $request->input('token'),
            $platform
        );

        if (! $result['valid']) {
            return $this->errorResponse($result['message'], 403);
        }

        return $this->successResponse([
            'valid' => true,
            'message' => $result['message'],
            'exam_id' => $exam->id,
            'attempt_status' => $result['attempt']->status,
            // FASE 72.2: lets the app subscribe to its own
            // private-cbt.exam.{examId}.student.{studentId} channel for live
            // proctor interventions — nothing else surfaces this numeric id.
            'student_id' => $student->id,
            // The client's local anti-cheat lockout threshold must match the
            // one the backend actually enforces (log-violation's auto-block,
            // above) — previously hardcoded to 3 in CbtAntiCheatEngine
            // regardless of this per-exam setting.
            'max_violations' => (int) $exam->max_violations,
        ], 'Token berhasil divalidasi');
    }

    /**
     * Force-close an exam attempt when student minimizes/closes app or uses split screen.
     */
    public function forceClose(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|in:app_minimized,app_closed,split_screen,back_button_pressed',
            'answers_snapshot' => 'nullable|array',
        ]);

        $student = $request->user()->student;
        if (! $student) {
            return $this->errorResponse('Data siswa tidak ditemukan', 404);
        }

        $exam = Exam::findOrFail($id);

        try {
            $attempt = $this->accessControlService->forceCloseAttempt(
                $exam,
                $student,
                $request->input('reason'),
                $request->input('answers_snapshot', [])
            );

            return $this->successResponse([
                'status' => 'force_closed',
                'answers_saved' => true,
                'force_close_reason' => $attempt->force_close_reason,
            ], 'Sesi ujian telah ditutup paksa demi integritas ujian');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Sesi ujian aktif tidak ditemukan', 404);
        } catch (\Exception $e) {
            report($e);

            return $this->errorResponse(__('Sesi ujian belum bisa ditutup. Coba lagi.'), 500);
        }
    }

    /**
     * Student app pings this every ~15s while an exam is active so the
     * proctor dashboard can tell a live-but-quiet student apart from one
     * who silently dropped connection (app killed, network loss) —
     * distinct from forceClose(), which is an explicit lifecycle violation.
     */
    public function heartbeat(Request $request, $id)
    {
        $student = $request->user()->student;
        if (! $student) {
            return $this->errorResponse('Data siswa tidak ditemukan', 404);
        }

        $exam = Exam::findOrFail($id);

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        if (! $attempt || in_array($attempt->status, ['submitted', 'graded', 'blocked_violation', 'timeout', 'force_closed'])) {
            return $this->errorResponse('Sesi ujian aktif tidak ditemukan', 404);
        }

        $attempt->update(['last_heartbeat_at' => now()]);

        return $this->successResponse([
            'exam_id' => $exam->id,
            'last_heartbeat_at' => $attempt->last_heartbeat_at->toIso8601String(),
        ], 'Heartbeat diterima');
    }

    /**
     * Non-terminal cheating violation reporting (screenshot attempt, app
     * switch detected without triggering the ON_PAUSE force-close, etc.) —
     * mobile mirror of the web-only ExamController::logViolation(). Distinct
     * from forceClose(): this doesn't end the attempt unless max_violations
     * is reached, it just counts and broadcasts to the proctor in real time.
     */
    public function logViolation(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|string|max:100',
        ]);

        $student = $request->user()->student;
        if (! $student) {
            return $this->errorResponse('Data siswa tidak ditemukan', 404);
        }

        $exam = Exam::findOrFail($id);

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->first();

        if (! $attempt) {
            return $this->errorResponse('Sesi ujian aktif tidak ditemukan', 404);
        }

        $violationType = $request->input('type');
        $logs = $attempt->violation_logs ?? [];
        $logs[] = [
            'type' => $violationType,
            'time' => now()->toDateTimeString(),
            'user_agent' => $request->userAgent(),
        ];

        $attempt->increment('violation_count');
        $attempt->update(['violation_logs' => $logs]);

        broadcast(new StudentCheatedEvent($attempt->fresh(), $violationType));

        $isBlocked = false;
        if ($attempt->fresh()->violation_count >= $exam->max_violations) {
            $attempt->update([
                'status' => 'blocked_violation',
                'submitted_at' => now(),
                'is_auto_submitted' => true,
            ]);
            $this->cbtService->autoGrade($attempt->fresh());
            $isBlocked = true;
        }

        return $this->successResponse([
            'violation_count' => $attempt->fresh()->violation_count,
            'is_blocked' => $isBlocked,
        ], 'Pelanggaran tercatat');
    }

    /**
     * List the exams the authenticated teacher operates (UTS/UAS as operator,
     * other types as creator) that are published and not yet ended — the
     * exams they can open in the proctor screen.
     */
    public function teacherExams(Request $request)
    {
        $exams = $this->accessControlService->examsOperatedBy($request->user())->get();

        $data = $exams->map(fn (Exam $exam) => [
            'id' => $exam->id,
            'title' => $exam->title,
            'type' => $exam->type,
            'subject' => $exam->subject?->name,
            'classroom' => $exam->classroom?->name,
            'start_time' => $exam->start_time?->toIso8601String(),
            'end_time' => $exam->end_time?->toIso8601String(),
            'duration_minutes' => $exam->duration_minutes,
            'total_questions' => $exam->total_questions,
            'mobile_only' => (bool) $exam->mobile_only,
            'is_ongoing' => $exam->isOngoing(),
        ])->values();

        return $this->successResponse($data, __('Daftar ujian yang Anda awasi berhasil diambil'));
    }

    /**
     * Get active exam entry token (Teacher/Operator only).
     */
    public function getToken(Request $request, $id)
    {
        $user = $request->user();
        $exam = Exam::findOrFail($id);

        if (! $this->accessControlService->canViewToken($user, $exam)) {
            return $this->errorResponse(
                $exam->type === 'uts' || $exam->type === 'uas'
                    ? __('Anda tidak berwenang melihat token ujian ini. Token UTS/UAS hanya dipegang oleh Operator Ujian yang ditunjuk.')
                    : __('Anda tidak berwenang melihat token ujian ini. Token Ulangan Harian dipegang oleh guru pembuat ujian.'),
                403
            );
        }

        // Auto-generate token if not yet set or already expired (> 5 minutes)
        if (empty($exam->access_token) || $exam->isTokenExpired()) {
            $token = $this->accessControlService->generateToken($exam);
            $exam->refresh();
        } else {
            $token = $exam->access_token;
        }

        return $this->successResponse([
            'exam_id' => $exam->id,
            'title' => $exam->title,
            'type' => $exam->type,
            'access_token' => $token,
            'token_expires_at' => $exam->token_expires_at?->toIso8601String(),
            'remaining_seconds' => $exam->getTokenRemainingSeconds(),
            'is_expired' => $exam->isTokenExpired(),
            'duration_minutes' => 5,
            'mobile_only' => (bool) $exam->mobile_only,
            'is_ongoing' => $exam->isOngoing(),
        ], __('Token ujian berhasil diambil (berlaku 5 menit)'));
    }

    /**
     * Regenerate new exam entry token with 5-minute lifespan (Teacher/Operator only).
     */
    public function regenerateToken(Request $request, $id)
    {
        $user = $request->user();
        $exam = Exam::findOrFail($id);

        if (! $this->accessControlService->canViewToken($user, $exam)) {
            return $this->errorResponse(__('Anda tidak berwenang memperbarui token ujian ini'), 403);
        }

        $newToken = $this->accessControlService->regenerateToken($exam);
        $exam->refresh();

        return $this->successResponse([
            'exam_id' => $exam->id,
            'title' => $exam->title,
            'access_token' => $newToken,
            'token_expires_at' => $exam->token_expires_at?->toIso8601String(),
            'remaining_seconds' => $exam->getTokenRemainingSeconds(),
            'is_expired' => false,
            'duration_minutes' => 5,
        ], __('Token ujian berhasil diperbarui (berlaku 5 menit)'));
    }

    /**
     * Reset force-closed status for a student so they can re-enter (Teacher/Operator only).
     */
    public function resetStudent(Request $request, $id)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $user = $request->user();
        $exam = Exam::findOrFail($id);
        $student = Student::findOrFail($request->input('student_id'));

        try {
            $attempt = $this->accessControlService->resetForceClose($exam, $student, $user);

            return $this->successResponse([
                'exam_id' => $exam->id,
                'student_id' => $student->id,
                'attempt_status' => $attempt->status,
                'force_closed' => $attempt->force_closed,
            ], __('Status ujian siswa berhasil direset. Siswa dapat memasukkan token untuk masuk kembali.'));
        } catch (AuthorizationException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(__('Siswa ini tidak sedang terkunci di ujian ini.'), 404);
        } catch (\Exception $e) {
            // The exception's text is for the log, not for the teacher's screen.
            report($e);

            return $this->errorResponse(__('Status ujian siswa belum bisa direset. Coba lagi.'), 500);
        }
    }

    /**
     * Students locked out of [id] by the anti-cheat (force_closed attempts), so
     * the operator picks who to let back in instead of typing a students.id.
     * `reason_code` is what the app reported; `reason` is its label.
     */
    public function lockedStudents(Request $request, $id)
    {
        $exam = Exam::findOrFail($id);
        if (! $this->accessControlService->canViewToken($request->user(), $exam)) {
            return $this->errorResponse(__('Anda tidak berwenang mengawasi ujian ini.'), 403);
        }

        $reasons = [
            'app_minimized' => __('Aplikasi diperkecil'),
            'app_closed' => __('Aplikasi ditutup'),
            'split_screen' => __('Layar terbagi'),
            'back_button_pressed' => __('Tombol kembali ditekan'),
        ];

        $locked = ExamAttempt::where('exam_id', $exam->id)
            ->where('status', 'force_closed')
            ->with(['student.user:id,name', 'student.classroom:id,name'])
            ->latest('updated_at')
            ->get()
            ->unique('student_id')
            ->values()
            ->map(fn (ExamAttempt $attempt) => [
                'student_id' => $attempt->student_id,
                'name' => $attempt->student?->name,
                'nis' => $attempt->student?->nis,
                'classroom' => $attempt->student?->classroom?->name,
                'reason_code' => $attempt->force_close_reason,
                'reason' => $reasons[$attempt->force_close_reason] ?? null,
                'violation_count' => (int) $attempt->violation_count,
                'locked_at' => $attempt->updated_at?->toIso8601String(),
            ]);

        return $this->successResponse($locked, __('Daftar siswa terkunci berhasil diambil'));
    }
}
