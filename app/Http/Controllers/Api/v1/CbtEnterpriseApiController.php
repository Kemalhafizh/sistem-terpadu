<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\CbtEssaySubmission;
use App\Models\Exam;
use App\Services\CbtEnterpriseService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CbtEnterpriseApiController extends Controller
{
    use ApiResponseTrait;

    protected CbtEnterpriseService $enterpriseService;

    public function __construct(CbtEnterpriseService $enterpriseService)
    {
        $this->enterpriseService = $enterpriseService;
    }

    /**
     * Get encrypted payload for zero-downtime pre-fetching.
     */
    public function encryptedPayload(Request $request, $id)
    {
        $student = $request->user()->student;
        if (! $student) {
            return $this->errorResponse(__('Data siswa tidak ditemukan'), 404);
        }

        $exam = Exam::with(['questions', 'subject'])->findOrFail($id);
        if (Gate::denies('take', $exam)) {
            return $this->errorResponse(__('Ujian ini bukan untuk kelas Anda.'), 403);
        }

        $payload = $this->enterpriseService->generateEncryptedPayload($exam, $student);

        return $this->successResponse($payload, __('Paket soal terenkripsi berhasil dimuat'));
    }

    /**
     * Get decryption key once exam time is reached.
     */
    public function decryptionKey(Request $request, $id)
    {
        $student = $request->user()->student;
        if (! $student) {
            return $this->errorResponse(__('Data siswa tidak ditemukan'), 404);
        }

        $exam = Exam::findOrFail($id);
        if (Gate::denies('take', $exam)) {
            return $this->errorResponse(__('Ujian ini bukan untuk kelas Anda.'), 403);
        }

        $result = $this->enterpriseService->getDecryptionKey($exam, $student);

        if (! $result['accessible']) {
            return $this->errorResponse($result['message'], 403);
        }

        return $this->successResponse($result, __('Kunci dekripsi berhasil ditarik'));
    }

    /**
     * Micro-batch answer sync to reduce DB write pressure.
     */
    public function microSync(Request $request, $id)
    {
        $student = $request->user()->student;
        if (! $student) {
            return $this->errorResponse(__('Data siswa tidak ditemukan'), 404);
        }

        $request->validate([
            'answers' => 'required|array',
        ]);

        $exam = Exam::findOrFail($id);
        if (Gate::denies('take', $exam)) {
            return $this->errorResponse(__('Ujian ini bukan untuk kelas Anda.'), 403);
        }

        $result = $this->enterpriseService->processMicroBatchAnswers($exam, $student, $request->input('answers'));

        return $this->successResponse($result, __('Jawaban berhasil disinkronisasi'));
    }

    /**
     * Upload handwritten essay / math steps photo with auto-compression.
     */
    public function uploadEssay(Request $request, $id)
    {
        $student = $request->user()->student;
        if (! $student) {
            return $this->errorResponse(__('Data siswa tidak ditemukan'), 404);
        }

        $request->validate([
            'question_id' => 'required|integer',
            'file' => 'required|file|mimes:jpeg,jpg,png,webp|max:5120',
            'student_notes' => 'nullable|string',
        ]);

        $exam = Exam::findOrFail($id);
        if (Gate::denies('take', $exam)) {
            return $this->errorResponse(__('Ujian ini bukan untuk kelas Anda.'), 403);
        }

        $submission = $this->enterpriseService->handleEssayUpload(
            $exam,
            $student,
            (int) $request->input('question_id'),
            $request->file('file'),
            $request->input('student_notes')
        );

        return $this->successResponse([
            'submission_id' => $submission->id,
            'file_url' => route('api.v1.cbt.essays.show', $submission),
            'file_size' => $submission->file_size,
            'question_id' => $submission->question_id,
        ], __('Lembar esai berhasil diunggah'));
    }

    /**
     * Download or preview a private handwritten essay submission.
     */
    public function showEssay(Request $request, CbtEssaySubmission $submission)
    {
        $attempt = $submission->examAttempt()->with('exam')->first();
        $exam = $attempt?->exam;
        $user = $request->user();

        if (! $attempt || ! $exam) {
            abort(404);
        }

        $isOwnerStudent = $user->student && (int) $attempt->student_id === (int) $user->student->id;
        if (! $isOwnerStudent && Gate::denies('monitor', $exam)) {
            abort(403, 'Akses ditolak.');
        }

        $path = $submission->file_path;
        if (! $path) {
            abort(404);
        }

        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->response($path);
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->response($path);
        }

        $legacyPublicPath = Str::after($path, 'uploads/');
        if ($legacyPublicPath !== $path && Storage::disk('public')->exists($legacyPublicPath)) {
            return Storage::disk('public')->response($legacyPublicPath);
        }

        abort(404);
    }

    /**
     * Proctor broadcasts a warning modal to student(s).
     */
    public function proctorWarning(Request $request, $id)
    {
        $request->validate([
            'student_id' => 'nullable|integer',
            'message' => 'required|string|max:500',
        ]);

        $exam = Exam::findOrFail($id);
        Gate::authorize('monitor', $exam);

        $log = $this->enterpriseService->proctorBroadcastWarning(
            $exam->id,
            $request->input('student_id'),
            $request->input('message'),
            $request->user()->id
        );

        return $this->successResponse($log, __('Peringatan pengawas berhasil disiarkan'));
    }

    /**
     * Proctor forces submit and locks an attempt.
     */
    public function proctorForceSubmit(Request $request, $id)
    {
        $request->validate([
            'student_id' => 'required|integer',
            'reason' => 'required|string|max:500',
        ]);

        $exam = Exam::findOrFail($id);
        Gate::authorize('monitor', $exam);

        $log = $this->enterpriseService->proctorForceSubmit(
            $exam->id,
            (int) $request->input('student_id'),
            $request->input('reason'),
            $request->user()->id
        );

        return $this->successResponse($log, __('Ujian siswa berhasil disubmit paksa'));
    }

    /**
     * Proctor extends time for a student.
     */
    public function proctorExtendTime(Request $request, $id)
    {
        $request->validate([
            'student_id' => 'required|integer',
            'minutes' => 'required|integer|min:1|max:120',
        ]);

        $exam = Exam::findOrFail($id);
        Gate::authorize('monitor', $exam);

        $log = $this->enterpriseService->proctorExtendTime(
            $exam->id,
            (int) $request->input('student_id'),
            (int) $request->input('minutes'),
            $request->user()->id
        );

        return $this->successResponse($log, __('Tambahan waktu berhasil diberikan'));
    }
}
