<?php

namespace App\Http\Controllers\Crisis;

use App\Http\Controllers\Controller;
use App\Models\CrisisIncident;
use App\Models\SafetyHeadcount;
use Illuminate\Http\Request;

class HeadcountController extends Controller
{
    public function dashboard($incidentId)
    {
        $incident = CrisisIncident::findOrFail($incidentId);
        $headcounts = SafetyHeadcount::where('crisis_incident_id', $incidentId)->get();

        return view('crisis.headcount.dashboard', compact('incident', 'headcounts'));
    }

    public function storeHeadcount(Request $request, $incidentId)
    {
        $validated = $request->validate([
            'assembly_point' => 'required|string',
            'expected_count' => 'required|integer',
            'accounted_count' => 'required|integer',
            'missing_count' => 'required|integer',
        ]);
        $validated['crisis_incident_id'] = $incidentId;
        $validated['status'] = $validated['missing_count'] > 0 ? 'missing_persons' : 'all_accounted';

        SafetyHeadcount::create($validated);

        return back()->with('success', 'Headcount submitted.');
    }
}
