<?php

namespace App\Http\Controllers\Inclusive;

use App\Http\Controllers\Controller;
use App\Models\ScreeningResult;
use App\Services\Inclusive\ScreeningEngineService;
use Illuminate\Http\Request;

class DisabilityScreeningController extends Controller
{
    protected $screeningEngine;

    public function __construct(ScreeningEngineService $screeningEngine)
    {
        $this->screeningEngine = $screeningEngine;
    }

    public function dashboard()
    {
        $results = ScreeningResult::with('student', 'evaluator')->latest()->take(10)->get();

        return view('fase58.screening.dashboard', compact('results'));
    }

    public function index()
    {
        return view('fase58.screening.dashboard');
    }

    public function conductScreening()
    {
        return view('fase58.screening.instrument');
    }

    public function storeResult(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'evaluator_id' => 'required|exists:employees,id',
            'screening_type' => 'required|string',
            'responses' => 'required|array',
        ]);

        $totalScore = $this->screeningEngine->evaluateResponses($validated['responses']);
        $riskLevel = $this->screeningEngine->calculateRiskLevel($totalScore);
        $recommendation = $this->screeningEngine->generateActionableRecommendations($riskLevel);

        $result = ScreeningResult::create([
            'student_id' => $validated['student_id'],
            'evaluator_id' => $validated['evaluator_id'],
            'screening_type' => $validated['screening_type'],
            'responses' => $validated['responses'],
            'total_score' => $totalScore,
            'risk_level' => $riskLevel,
            'system_recommendation' => $recommendation,
            'requires_professional_referral' => $riskLevel === 'high',
        ]);

        return redirect()->route('screening.show', $result)->with('success', 'Hasil skrining berhasil disimpan.');
    }

    public function showResult(ScreeningResult $result)
    {
        $result->load('student', 'evaluator');

        return view('fase58.screening.report', compact('result'));
    }
}
