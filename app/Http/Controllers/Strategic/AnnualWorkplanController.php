<?php

namespace App\Http\Controllers\Strategic;

use App\Http\Controllers\Controller;
use App\Models\AnnualWorkplan;
use App\Models\StrategicPlan;
use Illuminate\Http\Request;

class AnnualWorkplanController extends Controller
{
    public function index()
    {
        $workplans = AnnualWorkplan::with(['programs', 'plan'])->latest()->get();

        return view('strategic.workplan.index', compact('workplans'));
    }

    public function create()
    {
        $plans = StrategicPlan::orderByDesc('start_year')->get();

        return view('strategic.workplan.create', compact('plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // annual_workplans.strategic_plan_id is NOT NULL: storing without it was a 500.
            'strategic_plan_id' => 'required|exists:strategic_plans,id',
            'title' => 'required|string|max:255',
            'fiscal_year' => 'required|integer|min:2000|max:2100',
            'total_budget' => 'required|numeric|min:0',
        ]);
        $validated['prepared_by'] = auth()->id();

        $workplan = AnnualWorkplan::create($validated);

        return redirect()->route('strategic.workplan.show', $workplan->id)->with('success', 'RKS berhasil dibuat.');
    }

    public function show($id)
    {
        $workplan = AnnualWorkplan::with(['plan', 'programs.activities'])->findOrFail($id);

        return view('strategic.workplan.show', compact('workplan'));
    }
}
