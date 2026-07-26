<?php

namespace App\Http\Controllers\HumanCapital;

use App\Http\Controllers\Controller;
use App\Models\Evaluation360Cycle;
use App\Models\Evaluation360Instrument;
use App\Models\Evaluation360Summary;
use App\Models\User;
use App\Services\HumanCapital\Evaluation360Service;
use Illuminate\Http\Request;

class Evaluation360Controller extends Controller
{
    public function cycles()
    {
        $cycles = Evaluation360Cycle::withCount('instruments')->latest()->get();
        return view('evaluation-360.cycles', compact('cycles'));
    }

    public function storeCycle(Request $request, Evaluation360Service $service)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'academic_year' => 'required|string|max:9',
            'semester' => 'required|in:ganjil,genap',
        ]);

        $cycle = $service->createCycle($validated, auth()->id());

        // Create default instruments
        foreach (['supervisor', 'peer', 'student', 'parent', 'self'] as $type) {
            $service->createInstrument($cycle, ['evaluator_type' => $type]);
        }

        return redirect()->route('hc.evaluation.cycles')
            ->with('success', "Siklus Penilaian Evaluasi 360° & EDOM [{$cycle->title}] berhasil diaktifkan!");
    }

    public function fillForm(int $cycleId)
    {
        $cycle = Evaluation360Cycle::findOrFail($cycleId);
        $teachers = User::where('role', 'guru')->get();

        return view('evaluation-360.fill-evaluation', compact('cycle', 'teachers'));
    }

    public function submitEvaluation(int $cycleId, Request $request, Evaluation360Service $service)
    {
        $cycle = Evaluation360Cycle::findOrFail($cycleId);

        $validated = $request->validate([
            'evaluatee_id' => 'required|exists:users,id',
            'evaluator_type' => 'required|in:supervisor,peer,student,parent,self',
            'scores' => 'required|array',
            'comments' => 'nullable|string',
        ]);

        $instrument = Evaluation360Instrument::firstOrCreate([
            'cycle_id' => $cycle->id,
            'evaluator_type' => $validated['evaluator_type'],
        ]);

        $service->submitResponse($instrument, $validated['evaluatee_id'], auth()->id(), $validated['scores'], $validated['comments'] ?? null);

        // Auto generate summary
        $service->generateSummary($cycle->id, $validated['evaluatee_id']);

        return back()->with('success', 'Kuesioner Evaluasi Kinerja Guru 360° berhasil dikirim!');
    }

    public function results(int $teacherId)
    {
        $teacher = User::where('role', 'guru')->findOrFail($teacherId);
        $summary = Evaluation360Summary::where('evaluatee_id', $teacherId)->latest()->first();

        return view('evaluation-360.results', compact('teacher', 'summary'));
    }

    public function ranking()
    {
        $rankings = Evaluation360Summary::with('evaluatee')->orderByDesc('overall_score')->take(10)->get();
        return view('evaluation-360.ranking', compact('rankings'));
    }
}
