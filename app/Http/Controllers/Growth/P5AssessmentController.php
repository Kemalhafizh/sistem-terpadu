<?php

namespace App\Http\Controllers\Growth;

use App\Http\Controllers\Controller;
use App\Models\CharacterDimension;
use App\Models\MeritPoint;
use App\Models\User;
use App\Services\Growth\P5AssessmentService;
use App\Support\Access\StudentDataAccess;
use Illuminate\Http\Request;

class P5AssessmentController extends Controller
{
    public function dimensions()
    {
        $dimensions = CharacterDimension::orderBy('display_order')->get();

        return view('character.dimensions', compact('dimensions'));
    }

    public function assessForm(P5AssessmentService $service)
    {
        $students = User::whereIn('role', StudentDataAccess::STUDENT_ROLES)->get();
        $dimensions = CharacterDimension::where('is_active', true)->orderBy('display_order')->get();

        return view('character.assess', compact('students', 'dimensions'));
    }

    public function storeAssessment(Request $request, P5AssessmentService $service)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'character_dimension_id' => 'required|exists:character_dimensions,id',
            'score' => 'required|integer|between:1,4',
            'evidence_notes' => 'nullable|string',
        ]);

        $service->assessStudent(
            $validated['student_id'],
            auth()->id(),
            $validated['character_dimension_id'],
            $validated
        );

        return back()->with('success', 'Asesmen Karakter P5 / Akhlak Mahmudah berhasil disimpan!');
    }

    public function studentReport(int $studentId, P5AssessmentService $service)
    {
        StudentDataAccess::authorizeStudentUser(auth()->user(), $studentId);

        $student = User::whereIn('role', StudentDataAccess::STUDENT_ROLES)->findOrFail($studentId);
        $radar = $service->getStudentCharacterRadar($studentId);

        return view('character.student-report', compact('student', 'radar'));
    }

    public function leaderboard()
    {
        $topMerits = MeritPoint::with(['student', 'awarder', 'dimension'])
            ->selectRaw('student_id, sum(points) as total_points')
            ->groupBy('student_id')
            ->orderByDesc('total_points')
            ->take(10)
            ->get();

        return view('character.leaderboard', compact('topMerits'));
    }

    public function awardMerit(Request $request, P5AssessmentService $service)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'points' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
            'character_dimension_id' => 'nullable|exists:character_dimensions,id',
        ]);

        $service->awardMeritPoints(
            $validated['student_id'],
            auth()->id(),
            $validated['points'],
            $validated['reason'],
            $validated['character_dimension_id'] ?? null
        );

        return back()->with('success', 'Merit Points karakter positif berhasil diberikan!');
    }
}
