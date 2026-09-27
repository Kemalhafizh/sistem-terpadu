<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Services\CbtEnterpriseService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
            return $this->errorResponse('Data siswa tidak ditemukan', 404);
        }

        $exam = Exam::with(['questions', 'subject'])->findOrFail($id);
        Gate::authorize('take', $exam);

        $payload = $this->enterpriseService->generateEncryptedPayload($exam, $student);

        return $this->successResponse($payload, 'Paket soal terenkripsi berhasil dimuat');
    }

    /**
     * Get decryption key once exam time is reached.
     */
    public function decryptionKey(Request $request, $id)
    {
        $student = $request->user()->student;
        if (! $student) {
            return $this->errorResponse('Data siswa tidak ditemukan', 404);
        }

        $exam = Exam::findOrFail($id);
        Gate::authorize('take', $exam);

        $result = $this->enterpriseService->getDecryptionKey($exam, $student);

        if (! $result['accessible']) {
            return $this->errorResponse($result['message'], 403);
        }

        return $this->successResponse($result, 'Kunci dekripsi berhasil ditarik');
    }

    /**
     * Micro-batch answer sync to reduce DB write pressure.
     */
    public function microSync(Request $request, $id)
    {
        $student = $request->user()->student;
        if (! $student) {
            return $this->errorResponse('Data siswa tidak ditemukan', 404);
        }

        $request->validate([
            'answers' => 'required|array',
        ]);

        $exam = Exam::findOrFail($id);
        Gate::authorize('take', $exam);

        $result = $this->enterpriseService->processMicroBatchAnswers($exam, $student, $request->input('answers'));

        return $this->successResponse($result, 'Jawaban berhasil disinkronisasi');
    }

    /**
     * Upload handwritten essay / math steps photo with auto-compression.
     */
    public function uploadEssay(Request $request, $id)
    {
        $student = $request->user()->student;
        if (! $student) {
            return $this->errorResponse('Data siswa tidak ditemukan', 404);
        }

        $request->validate([
            'question_id' => 'required|integer',
            'file' => 'required|file|mimes:jpeg,jpg,png,webp|max:5120',
            'student_notes' => 'nullable|string',
        ]);

        $exam = Exam::findOrFail($id);
        Gate::authorize('take', $exam);

        $submission = $this->enterpriseService->handleEssayUpload(
            $exam,
            $student,
            (int) $request->input('question_id'),
            $request->file('file'),
            $request->input('student_notes')
        );

        return $this->successResponse([
            'submission_id' => $submission->id,
            'file_url' => url($submission->file_path),
            'file_size' => $submission->file_size,
            'question_id' => $submission->question_id,
        ], 'Lembar esai berhasil diunggah');
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

        return $this->successResponse($log, 'Peringatan pengawas berhasil disiarkan');
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

        return $this->successResponse($log, 'Ujian siswa berhasil disubmit paksa');
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

        return $this->successResponse($log, 'Tambahan waktu berhasil diberikan');
    }
}
