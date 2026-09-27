<?php

namespace App\Http\Controllers\Inclusive;

use App\Http\Controllers\Controller;
use App\Models\DifferentiatedPlan;
use Illuminate\Http\Request;

class DifferentiatedPlanController extends Controller
{
    public function index()
    {
        $plans = DifferentiatedPlan::latest()->get();

        return view('fase58.differentiation.planner', compact('plans'));
    }

    public function library()
    {
        $plans = DifferentiatedPlan::all();

        return view('fase58.differentiation.library', compact('plans'));
    }

    public function create()
    {
        return view('fase58.differentiation.planner');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'differentiation_type' => 'required|string',
            'tier_1_strategy' => 'required|array',
            'tier_2_strategy' => 'required|array',
            'tier_3_strategy' => 'required|array',
        ]);

        DifferentiatedPlan::create($validated);

        return redirect()->route('differentiation.library')->with('success', 'Rencana diferensiasi berhasil disimpan.');
    }

    public function show(DifferentiatedPlan $plan)
    {
        return view('fase58.differentiation.tier-mapping', compact('plan'));
    }

    public function edit(DifferentiatedPlan $plan)
    {
        return view('fase58.differentiation.planner', compact('plan'));
    }

    public function update(Request $request, DifferentiatedPlan $plan)
    {
        $validated = $request->validate([
            'differentiation_type' => 'required|string',
            'tier_1_strategy' => 'required|array',
            'tier_2_strategy' => 'required|array',
            'tier_3_strategy' => 'required|array',
        ]);

        $plan->update($validated);

        return redirect()->route('differentiation.library')->with('success', 'Rencana diferensiasi berhasil diperbarui.');
    }
}
