<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\ReportCardTemplate;
use App\Services\ERaporPdfService;
use App\Support\Access\StudentDataAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ERaporApiController extends Controller
{
    protected ERaporPdfService $service;

    public function __construct(ERaporPdfService $service)
    {
        $this->service = $service;
    }

    public function getCurrentRapor(Request $request): JsonResponse
    {
        $studentId = $request->user()->id;
        $reportData = $this->service->getStudentReport($studentId);

        return response()->json([
            'success' => true,
            'data' => $reportData,
        ]);
    }

    public function getChildRapor(Request $request, int $childId): JsonResponse
    {
        StudentDataAccess::authorizeStudentUser($request->user(), $childId);

        $reportData = $this->service->getStudentReport($childId);

        return response()->json([
            'success' => true,
            'data' => $reportData,
        ]);
    }

    public function generatePdf(Request $request): JsonResponse
    {
        $request->validate([
            'report_card_id' => 'nullable|integer',
            'student_id' => 'nullable|integer',
        ]);

        $studentId = (int) ($request->input('student_id') ?: $request->user()->id);
        StudentDataAccess::authorizeStudentUser($request->user(), $studentId);

        // No template means rapor has not been set up yet: say so. This used to
        // invent an academic year ("2025/2026") and a template on the spot.
        $reportCardId = $request->input('report_card_id') ?: ReportCardTemplate::latest()->value('id');
        if (! $reportCardId) {
            return response()->json([
                'success' => false,
                'message' => 'Template rapor belum dibuat oleh sekolah.',
            ], 422);
        }

        $pdf = $this->service->generateSinglePdf(
            (int) $reportCardId,
            (int) $studentId,
            (int) $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'PDF Rapor berhasil digenerate',
            'data' => $pdf,
        ]);
    }

    public function verify(string $hash): JsonResponse
    {
        $pdf = $this->service->verifyPdfHash($hash);

        if (! $pdf) {
            return response()->json([
                'success' => false,
                'message' => 'Sertifikat rapor digital tidak valid atau tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sertifikat rapor digital resmi SMA Islam Sultan Agung 1 terverifikasi.',
            'data' => [
                'student_name' => $pdf->student->name ?? 'Siswa',
                'nisn' => $pdf->student->nisn ?? '-',
                'semester' => $pdf->template->semester ?? 'Ganjil',
                'phase' => $pdf->template->phase ?? 'F',
                'sha256_hash' => $pdf->sha256_hash,
                'generated_at' => $pdf->generated_at ? $pdf->generated_at->toIso8601String() : null,
            ],
        ]);
    }
}
