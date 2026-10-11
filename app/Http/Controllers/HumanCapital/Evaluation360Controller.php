<?php

namespace App\Http\Controllers\HumanCapital;

use App\Http\Controllers\Controller;
use App\Models\Evaluation360Cycle;
use App\Models\Evaluation360Instrument;
use App\Models\Evaluation360Response;
use App\Models\Evaluation360Summary;
use App\Models\User;
use App\Services\HumanCapital\Evaluation360Service;
use App\Support\Access\Roles;
use Illuminate\Http\Request;

/**
 * Teacher 360° evaluation (119.9). Leadership runs the cycles; teachers rate
 * peers and themselves, leadership rates as supervisor, students rate in the
 * app. A teacher's results are for leadership and the teacher; the routes
 * carry Evaluation360Cycle's roles.
 */
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

        foreach (['supervisor', 'peer', 'student', 'parent', 'self'] as $type) {
            $service->createInstrument($cycle, ['evaluator_type' => $type]);
        }

        return redirect()->route('hc.evaluation.cycles')
            ->with('success', __('Siklus evaluasi ":title" dibuka.', ['title' => $cycle->title]));
    }

    public function fillForm(int $cycleId)
    {
        $cycle = Evaluation360Cycle::findOrFail($cycleId);
        $teachers = User::whereIn('role', Evaluation360Cycle::EVALUATEE_ROLES)->orderBy('name')->get();

        return view('evaluation-360.fill-evaluation', compact('cycle', 'teachers'));
    }

    /**
     * The perspective comes from who is rating, not from the form: it used to
     * be a dropdown, so anyone could submit as "supervisor".
     */
    public function submitEvaluation(int $cycleId, Request $request, Evaluation360Service $service)
    {
        $cycle = Evaluation360Cycle::where('status', 'active')->findOrFail($cycleId);

        $validated = $request->validate([
            'evaluatee_id' => 'required|integer',
            'scores' => 'required|array|min:1',
            'scores.*' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string|max:2000',
        ]);

        $evaluatee = User::whereIn('role', Evaluation360Cycle::EVALUATEE_ROLES)->findOrFail($validated['evaluatee_id']);
        $user = auth()->user();
        $type = $evaluatee->id === $user->id ? 'self'
            : (in_array($user->role, [...Roles::LEADERSHIP, 'admin'], true) ? 'supervisor' : 'peer');

        $instrument = Evaluation360Instrument::firstOrCreate(['cycle_id' => $cycle->id, 'evaluator_type' => $type]);

        $alreadyRated = Evaluation360Response::where('instrument_id', $instrument->id)
            ->where('evaluatee_id', $evaluatee->id)->where('evaluator_id', $user->id)->exists();
        if ($alreadyRated) {
            return back()->with('error', __('Anda sudah menilai guru ini pada siklus ini.'));
        }

        $service->submitResponse($instrument, $evaluatee->id, $user->id, $validated['scores'], $validated['comments'] ?? null);
        $service->generateSummary($cycle->id, $evaluatee->id);

        return back()->with('success', __('Penilaian terkirim.'));
    }

    /** Leadership sees any teacher; a teacher only their own results. */
    public function results(int $teacherId)
    {
        $user = auth()->user();
        abort_unless($user->id === $teacherId || in_array($user->role, Evaluation360Cycle::MANAGER_ROLES, true) || $user->role === 'superadmin', 403);

        $teacher = User::whereIn('role', Evaluation360Cycle::EVALUATEE_ROLES)->findOrFail($teacherId);
        $summary = Evaluation360Summary::where('evaluatee_id', $teacherId)->latest()->first();
        $comments = $summary
            ? Evaluation360Response::where('evaluatee_id', $teacherId)
                ->whereHas('instrument', fn ($q) => $q->where('cycle_id', $summary->cycle_id))
                ->whereNotNull('comments')->where('comments', '!=', '')
                ->latest('submitted_at')->pluck('comments')
            : collect();

        return view('evaluation-360.results', compact('teacher', 'summary', 'comments'));
    }

    public function ranking()
    {
        $rankings = Evaluation360Summary::with('evaluatee')->whereNotNull('overall_score')->orderByDesc('overall_score')->take(10)->get();

        return view('evaluation-360.ranking', compact('rankings'));
    }
}
