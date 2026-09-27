<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\EdsAssessment;
use App\Models\EdsPeriod;
use App\Models\EdsStandard;
use App\Services\EdsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EdsController extends Controller
{
    protected EdsService $edsService;

    public function __construct(EdsService $edsService)
    {
        $this->edsService = $edsService;
    }

    /**
     * Display a listing of all EDS periods.
     */
    public function index()
    {
        $this->authorize('viewAny', EdsPeriod::class);

        $periods = EdsPeriod::with('academicYear')->orderBy('created_at', 'desc')->get();
        $activeYear = AcademicYear::current();

        // Calculate progress and average score for each period
        foreach ($periods as $period) {
            $scores = $this->edsService->getStandardScores($period);
            $period->overall_average = $scores['overall_average'];
            $period->overall_progress = $scores['overall_progress'];
            $period->assessed_indicators = $scores['assessed_indicators'];
            $period->total_indicators = $scores['total_indicators'];
        }

        return view('eds.index', compact('periods', 'activeYear'));
    }

    /**
     * Store a newly created EDS period.
     */
    public function store(Request $request)
    {
        $this->authorize('manage', EdsPeriod::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $activeYear = AcademicYear::current();
        if (! $activeYear) {
            return redirect()->route('eds.index')->with('error', 'Tahun akademik aktif tidak ditemukan. Harap aktifkan tahun akademik terlebih dahulu.');
        }

        EdsPeriod::create([
            'name' => $request->name,
            'academic_year_id' => $activeYear->id,
            'status' => 'draft',
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        // Auto-seed the standards on creation just in case
        $this->edsService->seedDefaultStandards();

        return redirect()->route('eds.index')->with('success', 'Periode EDS berhasil diinisiasi dalam status Draft.');
    }

    /**
     * Display the specific EDS period dashboard.
     */
    public function show(EdsPeriod $period)
    {
        $this->authorize('viewAny', $period);

        // Auto-update status to in_progress if viewed and currently draft
        if ($period->status === 'draft') {
            $this->authorize('manage', $period);
            $period->update(['status' => 'in_progress']);
        }

        $period->load('academicYear');

        $scoresData = $this->edsService->getStandardScores($period);
        $chartData = $this->edsService->getSpiderChartData($period);

        return view('eds.show', compact('period', 'scoresData', 'chartData'));
    }

    /**
     * Show the assessment/evaluation form for a specific standard.
     */
    public function assess(EdsPeriod $period, EdsStandard $standard)
    {
        $this->authorize('viewAny', $period);

        $standard->load('indicators');
        $assessments = EdsAssessment::where('period_id', $period->id)
            ->whereIn('indicator_id', $standard->indicators->pluck('id'))
            ->get()
            ->keyBy('indicator_id');

        return view('eds.assess', compact('period', 'standard', 'assessments'));
    }

    /**
     * Store dynamic batch assessment scores and attachments.
     */
    public function storeAssessment(Request $request, EdsPeriod $period)
    {
        $this->authorize('assess', $period);

        $request->validate([
            'assessments' => 'required|array',
            'assessments.*.score' => 'required|integer|between:1,4',
            'assessments.*.evidence' => 'nullable|string',
            'assessments.*.recommendation' => 'nullable|string',
            'assessments.*.files' => 'nullable|array',
            'assessments.*.files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // PDF/Images max 5MB as discussed
        ]);

        $user = auth()->user();

        foreach ($request->assessments as $indicatorId => $data) {
            // Fetch existing assessment or create empty one
            $assessment = EdsAssessment::firstOrNew([
                'period_id' => $period->id,
                'indicator_id' => $indicatorId,
            ]);

            $existingPaths = $assessment->document_paths ?? [];

            // Handle file uploads
            if (isset($data['files'])) {
                foreach ($data['files'] as $file) {
                    if ($file->isValid()) {
                        $path = $file->store('eds/evidence', 'public');
                        $existingPaths[] = Storage::url($path);
                    }
                }
            }

            // Handle file deletion request
            if (isset($data['delete_files'])) {
                foreach ($data['delete_files'] as $delPath) {
                    if (($key = array_search($delPath, $existingPaths)) !== false) {
                        unset($existingPaths[$key]);
                        // Delete actual physical file from storage
                        $storagePath = str_replace('/storage/', '', $delPath);
                        Storage::disk('public')->delete($storagePath);
                    }
                }
                $existingPaths = array_values($existingPaths); // re-index
            }

            $assessment->fill([
                'score' => $data['score'],
                'evidence' => $data['evidence'] ?? null,
                'document_paths' => $existingPaths,
                'recommendation' => $data['recommendation'] ?? null,
                'assessed_by' => $user->id,
            ]);

            $assessment->save();
        }

        // Check if all indicators are assessed, if so, we can optionally prompt to close/complete period.
        // For flexibility, status transition from in_progress to completed is manual via a button,
        // but we'll support manual status updating or simple complete method.
        if ($request->has('complete_period') && $request->complete_period == '1') {
            $this->authorize('manage', $period);
            $period->update(['status' => 'completed']);

            return redirect()->route('eds.show', $period->id)->with('success', 'Penilaian disimpan dan Periode EDS resmi diselesaikan (Completed).');
        }

        return redirect()->route('eds.show', $period->id)->with('success', 'Batch Penilaian Evaluasi Diri Sekolah berhasil disimpan.');
    }

    /**
     * View the Gap Analysis table for a period.
     */
    public function gapAnalysis(EdsPeriod $period)
    {
        $this->authorize('viewAny', $period);

        $gaps = $this->edsService->getGapAnalysis($period);

        return view('eds.gap-analysis', compact('period', 'gaps'));
    }

    /**
     * Compare two EDS periods.
     */
    public function comparison(Request $request)
    {
        $this->authorize('viewAny', EdsPeriod::class);

        $periods = EdsPeriod::with('academicYear')->orderBy('created_at', 'desc')->get();

        $currentPeriod = null;
        $previousPeriod = null;
        $comparisonData = null;

        if ($request->has('current_period_id') && $request->has('previous_period_id')) {
            $currentPeriod = EdsPeriod::with('academicYear')->findOrFail($request->current_period_id);
            $previousPeriod = EdsPeriod::with('academicYear')->findOrFail($request->previous_period_id);
            $comparisonData = $this->edsService->getComparisonData($currentPeriod, $previousPeriod);
        }

        return view('eds.comparison', compact('periods', 'currentPeriod', 'previousPeriod', 'comparisonData'));
    }

    /**
     * Export EDS Report to PDF.
     */
    public function exportPdf(EdsPeriod $period)
    {
        $this->authorize('viewAny', $period);

        $period->load('academicYear');
        $pdf = $this->edsService->generateReportPdf($period);

        return $pdf->download("EDS-Laporan-Mutu-{$period->id}.pdf");
    }

    /**
     * Manually close / complete an EDS period.
     */
    public function complete(EdsPeriod $period)
    {
        $this->authorize('manage', $period);
        $period->update(['status' => 'completed', 'end_date' => now()]);

        return redirect()->route('eds.show', $period->id)->with('success', 'Periode EDS berhasil diselesaikan secara formal.');
    }
}
