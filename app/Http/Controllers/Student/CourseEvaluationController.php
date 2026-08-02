<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseEvaluation;
use App\Services\Student\CourseEvaluationService;
use Illuminate\Http\Request;

class CourseEvaluationController extends Controller
{
    public function index()
    {
        $evaluations = CourseEvaluation::with(['subject', 'teacher'])->latest()->get();
        return view('evaluations.index', compact('evaluations'));
    }

    public function fillForm(int $id)
    {
        $evaluation = CourseEvaluation::with(['subject', 'teacher'])->findOrFail($id);
        return view('evaluations.form', compact('evaluation'));
    }

    public function storeResponse(Request $request, CourseEvaluationService $service)
    {
        $validated = $request->validate([
            'evaluation_id' => 'required|exists:course_evaluations,id',
            'rating' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string',
        ]);

        $service->submitEvaluation($validated['evaluation_id'], auth()->id(), $validated['rating'], $validated['comments'] ?? null);

        return redirect()->route('evaluations.index')->with('success', 'Evaluasi pembelajaran berhasil dikirim secara anonim!');
    }
}
