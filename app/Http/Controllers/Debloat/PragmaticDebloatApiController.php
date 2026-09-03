<?php

namespace App\Http\Controllers\Debloat;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\Debloat\DigitalCertificateVerificationService;
use App\Services\Debloat\PragmaticAtRiskStudentService;
use App\Services\Debloat\PragmaticModuleCatalogService;
use App\Services\Debloat\PragmaticSyncService;
use App\Services\Debloat\SingleVpsInfrastructureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PragmaticDebloatApiController extends Controller
{
    public function __construct(
        protected DigitalCertificateVerificationService $certificateService,
        protected PragmaticAtRiskStudentService $atRiskService,
        protected SingleVpsInfrastructureService $vpsService,
        protected PragmaticSyncService $syncService,
        protected PragmaticModuleCatalogService $catalogService
    ) {}

    /**
     * Public static verification for diploma / certificate.
     */
    public function verifyCertificate(string $docNumber): JsonResponse
    {
        $result = $this->certificateService->verifyCertificate($docNumber);

        return response()->json([
            'success' => $result['is_valid'] ?? false,
            'data' => $result,
        ], ($result['is_valid'] ?? false) ? 200 : 404);
    }

    /**
     * Issue digital verification record.
     */
    public function issueCertificate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'document_type' => 'required|string',
            'document_number' => 'required|string',
        ]);

        $student = Student::with('user')->findOrFail($validated['student_id']);
        $doc = $this->certificateService->issueCertificate(
            $student,
            $validated['document_type'],
            $validated['document_number']
        );

        return response()->json([
            'success' => true,
            'message' => 'Sertifikat digital terverifikasi berhasil diterbitkan.',
            'data' => $doc,
        ], 201);
    }

    /**
     * Evaluate at-risk student status via deterministic rules.
     */
    public function evaluateAtRisk(int $studentId): JsonResponse
    {
        $alert = $this->atRiskService->evaluateStudentRisk($studentId);

        return response()->json([
            'success' => true,
            'has_alert' => $alert !== null,
            'data' => $alert,
        ]);
    }

    /**
     * Single VPS server health monitoring.
     */
    public function getVpsHealth(): JsonResponse
    {
        $summary = $this->vpsService->getServerHealthSummary();

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Pragmatic FIFO Offline Sync Queue.
     */
    public function syncQueue(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'actions' => 'required|array',
        ]);

        $user = $request->user();
        $res = $this->syncService->processQueue($user, $validated['actions']);

        return response()->json([
            'success' => true,
            'data' => $res,
        ]);
    }

    /**
     * Pragmatic Quick Pull endpoint.
     */
    public function syncPull(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $this->syncService->getQuickPullData($user);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Static Monolithic Module Catalog.
     */
    public function getModuleCatalog(): JsonResponse
    {
        $catalog = $this->catalogService->getActiveCatalog();

        return response()->json([
            'success' => true,
            'data' => $catalog,
        ]);
    }
}
