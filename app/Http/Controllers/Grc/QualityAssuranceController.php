<?php

namespace App\Http\Controllers\Grc;

use App\Http\Controllers\Controller;
use App\Models\AccreditationStandard;
use App\Models\SelfEvaluationCycle;
use App\Services\Grc\QualityAssuranceService;
use Illuminate\Http\Request;

class QualityAssuranceController extends Controller
{
    /**
     * Display EDS Evaluation Cycles Dashboard.
     */
    public function cycles()
    {
        $cycles = SelfEvaluationCycle::with(['creator', 'responses'])
            ->orderBy('id', 'desc')
            ->get();

        return view('quality.cycles', compact('cycles'));
    }

    /**
     * Create new EDS evaluation cycle.
     */
    public function createCycle(Request $request, QualityAssuranceService $qaService)
    {
        $request->validate([
            'academic_year' => 'required|string|max:20',
            'semester' => 'required|in:ganjil,genap',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $createdBy = auth()->id();
        $cycle = $qaService->createCycle($request->all(), $createdBy);

        return back()->with('success', "Siklus EDS [TA {$cycle->academic_year} ".ucfirst($cycle->semester).'] berhasil dibuat!');
    }

    /**
     * Form for filling self-evaluation questionnaire per 8 SNP standard.
     */
    public function fillEvaluation(int $id)
    {
        $cycle = SelfEvaluationCycle::findOrFail($id);
        $standards = AccreditationStandard::all();

        return view('quality.evaluation-form', compact('cycle', 'standards'));
    }

    /**
     * Submit evaluation responses for an EDS cycle.
     */
    public function submitEvaluation(Request $request, int $id, QualityAssuranceService $qaService)
    {
        $request->validate([
            'standard_id' => 'required|exists:accreditation_standards,id',
            'score' => 'required|integer|min:1|max:4',
            'evidence_notes' => 'nullable|string|max:500',
        ]);

        $respondentId = auth()->id();
        $data = array_merge($request->all(), ['cycle_id' => $id]);

        $qaService->submitResponse($data, $respondentId);

        return back()->with('success', 'Hasil evaluasi diri untuk standar terpilih berhasil disimpan!');
    }

    /**
     * View EDS results and mean score breakdown for a cycle.
     */
    public function results(int $id, QualityAssuranceService $qaService)
    {
        $results = $qaService->calculateResults($id);
        $cycle = SelfEvaluationCycle::findOrFail($id);

        return view('quality.results', compact('results', 'cycle'));
    }

    /**
     * View EDS trend comparison report between periods.
     */
    public function compareReport(int $id, QualityAssuranceService $qaService)
    {
        $comparison = $qaService->compareWithPrevious($id);
        $cycle = SelfEvaluationCycle::findOrFail($id);

        return view('quality.results', compact('comparison', 'cycle'));
    }
}
