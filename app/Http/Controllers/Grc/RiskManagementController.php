<?php

namespace App\Http\Controllers\Grc;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\RiskRegister;
use App\Services\Grc\RiskManagementService;
use Illuminate\Http\Request;

class RiskManagementController extends Controller
{
    /**
     * Display Risk Register list.
     */
    public function riskRegister()
    {
        $risks = RiskRegister::with('owner')
            ->orderBy('id', 'desc')
            ->get();

        return view('risk.register', compact('risks'));
    }

    /**
     * Create new risk assessment entry.
     */
    public function createRisk(Request $request, RiskManagementService $riskService)
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'required|string',
            'category' => 'required|in:Academic,Financial,Safety,IT/Cyber,Reputational,Regulatory',
            'likelihood' => 'required|in:rare,unlikely,possible,likely,almost_certain',
            'impact' => 'required|in:insignificant,minor,moderate,major,catastrophic',
            'mitigation_plan' => 'nullable|string',
        ]);

        $ownerId = auth()->id();
        $risk = $riskService->assessRisk($request->all(), $ownerId);

        return back()->with('success', "Risiko [{$risk->title}] berhasil ditambahkan ke Risk Register dengan Skor {$risk->risk_score} ({$risk->risk_level})!");
    }

    /**
     * Display 5x5 ERM Risk Heat Map Matrix.
     */
    public function riskMatrix(RiskManagementService $riskService)
    {
        $matrixData = $riskService->generateRiskMatrix();

        return view('risk.matrix', compact('matrixData'));
    }

    /**
     * Display Incidents Report Queue.
     */
    public function incidents()
    {
        $incidents = Incident::with(['reporter', 'assignee'])
            ->orderBy('id', 'desc')
            ->get();

        return view('risk.incidents', compact('incidents'));
    }

    /**
     * Report new operational incident.
     */
    public function createIncident(Request $request, RiskManagementService $riskService)
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'required|string',
            'category' => 'required|string',
            'severity' => 'required|in:low,medium,high,critical',
        ]);

        $reportedBy = auth()->id();
        $incident = $riskService->createIncident($request->all(), $reportedBy);

        return back()->with('success', "Insiden [{$incident->title}] berstatus [{$incident->severity}] berhasil dilaporkan ke tim GRC!");
    }

    /**
     * Log incident resolution.
     */
    public function resolveIncident(Request $request, int $id, RiskManagementService $riskService)
    {
        $request->validate([
            'resolution_notes' => 'required|string',
        ]);

        $assignedTo = auth()->id();
        $incident = $riskService->resolveIncident($id, $request->resolution_notes, $assignedTo);

        return back()->with('success', "Insiden #{$incident->id} berhasil ditandai SELESAI / RESOLVED!");
    }
}
