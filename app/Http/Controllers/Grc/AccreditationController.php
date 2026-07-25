<?php

namespace App\Http\Controllers\Grc;

use App\Http\Controllers\Controller;
use App\Models\AccreditationStandard;
use App\Services\Grc\AccreditationService;
use Illuminate\Http\Request;

class AccreditationController extends Controller
{
    /**
     * Display BAN-SM 8 SNP Accreditation Readiness Dashboard.
     */
    public function dashboard(AccreditationService $accreditationService)
    {
        $overall = $accreditationService->calculateOverallScore();
        $standards = AccreditationStandard::withCount('indicators')->get();

        return view('accreditation.dashboard', compact('overall', 'standards'));
    }

    /**
     * Display detail indicators and evidence checklist for a specific 8 SNP standard.
     */
    public function standardDetail(int $id, AccreditationService $accreditationService)
    {
        $standard = AccreditationStandard::with(['indicators.evidences.uploader', 'indicators.evidences.verifier'])->findOrFail($id);
        $scoreCalc = $accreditationService->calculateStandardScore($id);

        return view('accreditation.standard-detail', compact('standard', 'scoreCalc'));
    }

    /**
     * Upload physical / digital evidence file.
     */
    public function uploadEvidence(Request $request, AccreditationService $accreditationService)
    {
        $request->validate([
            'accreditation_indicator_id' => 'required|exists:accreditation_indicators,id',
            'file_path' => 'required|string|max:255',
            'evidence_type' => 'required|in:document,data,photo',
            'description' => 'nullable|string|max:500',
        ]);

        $uploadedBy = auth()->id();
        $evidence = $accreditationService->uploadEvidence($request->all(), $uploadedBy);

        return back()->with('success', "Bukti fisik/digital untuk indikator #{$evidence->accreditation_indicator_id} berhasil diunggah!");
    }

    /**
     * Verify / Approve evidence submission (Assessor/Admin).
     */
    public function verifyEvidence(int $id, AccreditationService $accreditationService)
    {
        $verifiedBy = auth()->id();

        try {
            $evidence = $accreditationService->verifyEvidence($id, $verifiedBy);
            return back()->with('success', "Bukti fisik #{$evidence->id} berhasil DIVERIFIKASI dan dihitung dalam skor SNP!");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display / Generate formal BAN-SM Accreditation Readiness report.
     */
    public function report(AccreditationService $accreditationService)
    {
        $reportData = $accreditationService->generateReport();

        return view('accreditation.report', compact('reportData'));
    }
}
