<?php

namespace App\Http\Controllers\Strategic;

use App\Http\Controllers\Controller;
use App\Models\StrategicPlan;
use App\Models\SwotAnalysis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SwotAnalysisController extends Controller
{
    public const QUADRANTS = [
        'strength' => 'Kekuatan (Strengths)',
        'weakness' => 'Kelemahan (Weaknesses)',
        'opportunity' => 'Peluang (Opportunities)',
        'threat' => 'Ancaman (Threats)',
    ];

    public function index()
    {
        $analyses = SwotAnalysis::with('items')->latest()->get();

        return view('strategic.swot.index', compact('analyses'));
    }

    public function create()
    {
        $plans = StrategicPlan::orderByDesc('start_year')->get();
        $quadrants = self::QUADRANTS;

        return view('strategic.swot.create', compact('plans', 'quadrants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'analysis_year' => 'required|integer|min:2000|max:2100',
            'strategic_plan_id' => 'nullable|exists:strategic_plans,id',
            'conclusion' => 'nullable|string',
            // One factor per line, per quadrant.
            'items' => 'nullable|array',
            'items.*' => 'nullable|string',
        ]);

        $swot = DB::transaction(function () use ($validated) {
            $swot = SwotAnalysis::create([
                'title' => $validated['title'],
                'analysis_year' => $validated['analysis_year'],
                'strategic_plan_id' => $validated['strategic_plan_id'] ?? null,
                'conclusion' => $validated['conclusion'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach (array_keys(self::QUADRANTS) as $quadrant) {
                $lines = preg_split('/\R/', (string) ($validated['items'][$quadrant] ?? ''));
                $order = 0;
                foreach (array_filter(array_map('trim', $lines)) as $line) {
                    $swot->items()->create(['quadrant' => $quadrant, 'description' => $line, 'sort_order' => ++$order]);
                }
            }

            return $swot;
        });

        return redirect()->route('strategic.swot.show', $swot->id)->with('success', 'Analisis SWOT berhasil dibuat.');
    }

    public function show($id)
    {
        $swot = SwotAnalysis::with(['items' => fn ($q) => $q->orderBy('sort_order')])->findOrFail($id);
        $quadrants = self::QUADRANTS;

        return view('strategic.swot.show', compact('swot', 'quadrants'));
    }
}
