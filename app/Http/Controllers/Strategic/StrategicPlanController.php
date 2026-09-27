<?php

namespace App\Http\Controllers\Strategic;

use App\Http\Controllers\Controller;
use App\Models\StrategicPlan;
use Illuminate\Http\Request;

class StrategicPlanController extends Controller
{
    public function dashboard()
    {
        $activePlan = StrategicPlan::with('objectives.kpis')->where('status', 'active')->first();

        return view('strategic.dashboard', compact('activePlan'));
    }

    public function index()
    {
        $plans = StrategicPlan::latest()->get();

        return view('strategic.plan.index', compact('plans'));
    }

    public function create()
    {
        return view('strategic.plan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'vision' => 'required|string',
            'mission' => 'required|string',
            'start_year' => 'required|integer',
            'end_year' => 'required|integer',
        ]);

        StrategicPlan::create($validated);

        return redirect()->route('strategic.plan.index')->with('success', 'Rencana Strategis berhasil dibuat.');
    }

    public function show($id)
    {
        $plan = StrategicPlan::with('objectives.kpis')->findOrFail($id);

        return view('strategic.plan.show', compact('plan'));
    }
}
