<?php

namespace App\Http\Controllers\Crisis;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CrisisIncident;
use App\Models\CrisisProtocol;
use App\Services\Crisis\CrisisManagementService;

class CrisisController extends Controller
{
    protected $service;
    
    public function __construct(CrisisManagementService $service)
    {
        $this->service = $service;
    }

    public function commandCenter()
    {
        $activeIncidents = CrisisIncident::whereNotIn('status', ['resolved', 'post_mortem'])->get();
        $protocols = CrisisProtocol::where('is_active', true)->get();
        return view('crisis.command-center', compact('activeIncidents', 'protocols'));
    }

    public function declareIncident()
    {
        return view('crisis.declare');
    }

    public function storeIncident(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'type' => 'required|string',
            'severity' => 'required|string',
            'description' => 'required|string',
            'location_detail' => 'nullable|string',
        ]);
        
        $validated['reported_by'] = auth()->id() ?? 1;

        $this->service->declareIncident($validated);
        return redirect()->route('crisis.command_center')->with('success', 'Incident declared.');
    }

    public function showIncident($id)
    {
        $incident = CrisisIncident::findOrFail($id);
        $timeline = $this->service->getIncidentTimeline($incident);
        return view('crisis.show', compact('incident', 'timeline'));
    }

    public function storeResponse(Request $request, $id)
    {
        $incident = CrisisIncident::findOrFail($id);
        $validated = $request->validate([
            'action_type' => 'required|string',
            'description' => 'required|string',
        ]);
        $validated['responder_id'] = auth()->id() ?? 1;
        $this->service->logResponse($incident, $validated);
        return back()->with('success', 'Response logged.');
    }

    public function resolveIncident($id)
    {
        $incident = CrisisIncident::findOrFail($id);
        $this->service->resolveIncident($incident, request('notes', ''));
        return back()->with('success', 'Incident resolved.');
    }

    public function protocols()
    {
        $protocols = CrisisProtocol::all();
        return view('crisis.protocols.index', compact('protocols'));
    }

    public function createProtocol()
    {
        return view('crisis.protocols.create');
    }

    public function storeProtocol(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'crisis_type' => 'required|string',
            'steps' => 'required|array',
            'emergency_contacts' => 'required|array',
            'assembly_points' => 'required|array',
        ]);
        $validated['approved_by'] = auth()->id() ?? 1;
        CrisisProtocol::create($validated);
        return redirect()->route('crisis.protocols.index')->with('success', 'Protocol created.');
    }

    public function postMortemReport($id)
    {
        $incident = CrisisIncident::findOrFail($id);
        return view('crisis.post-mortem', compact('incident'));
    }
}