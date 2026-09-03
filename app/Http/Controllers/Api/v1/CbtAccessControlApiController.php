<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Student;
use App\Services\CbtAccessControlService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class CbtAccessControlApiController extends Controller
{
    use ApiResponseTrait;

    protected CbtAccessControlService $accessControlService;

    public function __construct(CbtAccessControlService $accessControlService)
    {
        $this->accessControlService = $accessControlService;
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
        if (!$student) {
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

        if (!$result['valid']) {
            return $this->errorResponse($result['message'], 403);
        }

        return $this->successResponse([
            'valid' => true,
            'message' => $result['message'],
            'exam_id' => $exam->id,
            'attempt_status' => $result['attempt']->status,
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
        if (!$student) {
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
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Sesi ujian aktif tidak ditemukan', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal menutup sesi ujian: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get active exam entry token (Teacher/Operator only).
     */
    public function getToken(Request $request, $id)
    {
        $user = $request->user();
        $exam = Exam::findOrFail($id);

        if (!$this->accessControlService->canViewToken($user, $exam)) {
            return $this->errorResponse(
                'Anda tidak berwenang melihat token ujian ini. ' .
                ($exam->type === 'uts' || $exam->type === 'uas'
                    ? 'Token UTS/UAS hanya dipegang oleh Operator Ujian yang ditunjuk.'
                    : 'Token Ulangan Harian dipegang oleh guru pembuat ujian.'),
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
        ], 'Token ujian berhasil diambil (berlaku 5 menit)');
    }

    /**
     * Regenerate new exam entry token with 5-minute lifespan (Teacher/Operator only).
     */
    public function regenerateToken(Request $request, $id)
    {
        $user = $request->user();
        $exam = Exam::findOrFail($id);

        if (!$this->accessControlService->canViewToken($user, $exam)) {
            return $this->errorResponse('Anda tidak berwenang memperbarui token ujian ini', 403);
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
        ], 'Token ujian berhasil diperbarui (berlaku 5 menit)');
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
            ], 'Status ujian siswa berhasil direset. Siswa dapat memasukkan token untuk masuk kembali.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Data attempt force_closed untuk siswa ini tidak ditemukan', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mereset status siswa: ' . $e->getMessage(), 500);
        }
    }
}
