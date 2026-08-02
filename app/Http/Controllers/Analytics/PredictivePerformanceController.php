<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\MlPerformancePrediction;
use App\Models\User;
use App\Services\Analytics\PredictivePerformanceService;
use Illuminate\Http\Request;

class PredictivePerformanceController extends Controller
{
    public function dashboard()
    {
        $predictions = MlPerformancePrediction::with('student')->latest()->get();
        return view('analytics.predictive.dashboard', compact('predictions'));
    }

    public function showStudentRisk(int $id)
    {
        $prediction = MlPerformancePrediction::with(['student', 'interventions.counselor'])->findOrFail($id);
        return view('analytics.predictive.student-risk', compact('prediction'));
    }

    public function storeIntervention(Request $request, PredictivePerformanceService $service)
    {
        $validated = $request->validate([
            'prediction_id' => 'required|exists:ml_performance_predictions,id',
            'action_taken' => 'required|string',
        ]);

        $service->logIntervention($validated['prediction_id'], auth()->id(), $validated['action_taken']);

        return back()->with('success', 'Rencana tindakan intervensi konselor BK berhasil dicatat!');
    }
}
