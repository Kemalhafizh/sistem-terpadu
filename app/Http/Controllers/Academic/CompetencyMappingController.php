<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\LearningObjective;
use App\Models\LearningOutcome;
use App\Models\Subject;
use App\Models\User;
use App\Services\Academic\CompetencyMappingService;
use Illuminate\Http\Request;

class CompetencyMappingController extends Controller
{
    public function outcomes()
    {
        $subjects = Subject::all();
        $outcomes = LearningOutcome::with(['subject', 'objectives.atpMappings'])->latest()->get();

        return view('competency.outcomes', compact('subjects', 'outcomes'));
    }

    public function storeOutcome(Request $request, CompetencyMappingService $service)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'phase' => 'required|in:E,F',
            'element' => 'required|string|max:255',
            'cp_code' => 'required|string|unique:learning_outcomes,cp_code',
            'cp_description' => 'required|string',
        ]);

        $service->createOutcome($validated);

        return back()->with('success', 'Capaian Pembelajaran (CP) Kurikulum Merdeka berhasil disimpan!');
    }

    public function storeObjective(int $outcomeId, Request $request, CompetencyMappingService $service)
    {
        $outcome = LearningOutcome::findOrFail($outcomeId);

        $validated = $request->validate([
            'tp_code' => 'required|string',
            'tp_description' => 'required|string',
            'semester' => 'required|in:ganjil,genap',
            'bloom_taxonomy_level' => 'required|in:remember,understand,apply,analyze,evaluate,create',
        ]);

        $service->createObjective($outcome, $validated);

        return back()->with('success', 'Tujuan Pembelajaran (TP) berhasil ditambahkan!');
    }

    public function atpMapping(int $objectiveId, CompetencyMappingService $service)
    {
        $objective = LearningObjective::with(['outcome.subject', 'atpMappings'])->findOrFail($objectiveId);
        return view('competency.atp-mapping', compact('objective'));
    }

    public function storeAtp(int $objectiveId, Request $request, CompetencyMappingService $service)
    {
        $objective = LearningObjective::findOrFail($objectiveId);

        $validated = $request->validate([
            'week_number' => 'required|integer|between:1,20',
            'teaching_hours' => 'required|integer|min:1',
            'learning_activities' => 'required|string',
            'assessment_method' => 'required|string',
        ]);

        $service->mapToAtp($objective, $validated);

        return back()->with('success', 'Alur Tujuan Pembelajaran (ATP) mingguan berhasil dipetakan!');
    }

    public function classHeatmap(int $subjectId, CompetencyMappingService $service)
    {
        $subject = Subject::findOrFail($subjectId);
        $heatmap = $service->getClassCompetencyHeatmap($subjectId);

        return view('competency.class-heatmap', compact('subject', 'heatmap'));
    }
}
