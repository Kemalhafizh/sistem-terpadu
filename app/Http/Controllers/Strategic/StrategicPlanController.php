<?php

namespace App\Http\Controllers\Strategic;

use App\Http\Controllers\Controller;
use App\Models\KeyPerformanceIndicator;
use App\Models\StrategicObjective;
use App\Models\StrategicPlan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StrategicPlanController extends Controller
{
    public const PERSPECTIVES = [
        'stakeholder' => 'Stakeholder (Pemangku Kepentingan)',
        'proses_internal' => 'Proses Internal',
        'pembelajaran_pertumbuhan' => 'Pembelajaran & Pertumbuhan',
        'finansial' => 'Finansial',
    ];

    public const FREQUENCIES = [
        'monthly' => 'Bulanan',
        'quarterly' => 'Triwulan',
        'semester' => 'Semester',
        'yearly' => 'Tahunan',
    ];

    public function dashboard()
    {
        $activePlan = StrategicPlan::with('objectives.kpis')->where('status', 'active')->latest('start_year')->first();

        return view('strategic.dashboard', compact('activePlan'));
    }

    public function index()
    {
        $plans = StrategicPlan::withCount('objectives')->latest()->get();

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
            'start_year' => 'required|integer|min:2000|max:2100',
            'end_year' => 'required|integer|min:2000|max:2100|gte:start_year',
            'status' => 'required|in:draft,active',
        ]);

        $plan = StrategicPlan::create($validated);

        return redirect()->route('strategic.plan.show', $plan->id)->with('success', 'Rencana Strategis berhasil dibuat. Lanjutkan dengan menambahkan sasaran strategis.');
    }

    public function show($id)
    {
        $plan = StrategicPlan::with(['objectives' => fn ($q) => $q->orderBy('sort_order')->orderBy('code'), 'objectives.kpis'])->findOrFail($id);
        $perspectives = self::PERSPECTIVES;
        $frequencies = self::FREQUENCIES;

        return view('strategic.plan.show', compact('plan', 'perspectives', 'frequencies'));
    }

    public function storeObjective(Request $request, StrategicPlan $plan)
    {
        $validated = $request->validate([
            'perspective' => ['required', Rule::in(array_keys(self::PERSPECTIVES))],
            'code' => 'required|string|max:20',
            'objective' => 'required|string|max:255',
            'description' => 'nullable|string',
            'weight' => 'required|integer|min:0|max:100',
        ]);

        $plan->objectives()->create($validated + ['sort_order' => $plan->objectives()->count() + 1]);

        return redirect()->route('strategic.plan.show', $plan->id)->with('success', "Sasaran {$validated['code']} ditambahkan.");
    }

    public function storeKpi(Request $request, StrategicObjective $objective)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'indicator' => 'required|string|max:255',
            'unit' => 'required|string|max:30',
            'baseline_value' => 'required|numeric',
            'target_value' => 'required|numeric',
            'polarity' => 'required|in:higher_is_better,lower_is_better',
            'frequency' => ['required', Rule::in(array_keys(self::FREQUENCIES))],
            'data_source' => 'nullable|string',
        ]);

        KeyPerformanceIndicator::create($validated + [
            'strategic_objective_id' => $objective->id,
            'current_value' => $validated['baseline_value'],
            'pic_user_id' => auth()->id(),
        ]);

        return redirect()->route('strategic.plan.show', $objective->strategic_plan_id)->with('success', "KPI {$validated['code']} ditambahkan.");
    }
}
