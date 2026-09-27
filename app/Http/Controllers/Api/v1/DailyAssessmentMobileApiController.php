<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\DailyAssessment;
use App\Models\DailyAssessmentScore;
use App\Services\RemedialWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DailyAssessmentMobileApiController extends Controller
{
    protected RemedialWorkflowService $remedialService;

    public function __construct(RemedialWorkflowService $remedialService)
    {
        $this->remedialService = $remedialService;
    }

    public function teacherAssessments(Request $request): JsonResponse
    {
        $teacherId = $request->user()->id;
        $assessments = DailyAssessment::with(['subject', 'classroom'])
            ->where('teacher_id', $teacherId)
            ->withCount('scores')
            ->latest()
            ->get();

        $dashboard = $this->remedialService->getTeacherRemedialDashboard($teacherId);

        return response()->json([
            'success' => true,
            'data' => [
                'assessments' => $assessments,
                'remedial_dashboard' => $dashboard,
            ],
        ]);
    }

    public function storeAssessment(Request $request): JsonResponse
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'title' => 'required|string|max:255',
            'assessment_date' => 'required|date',
            'kkm' => 'required|numeric|min:0|max:100',
            'max_score' => 'nullable|numeric|min:1',
            'description' => 'nullable|string',
        ]);

        $assessment = DailyAssessment::create([
            'subject_id' => $request->subject_id,
            'classroom_id' => $request->classroom_id,
            'teacher_id' => $request->user()->id,
            'academic_year_id' => AcademicYear::where('is_active', true)->first()->id ?? 1,
            'title' => $request->title,
            'type' => $request->input('type', 'ulangan_harian'),
            'assessment_date' => $request->assessment_date,
            'kkm' => (float) $request->kkm,
            'max_score' => (float) ($request->max_score ?? 100.0),
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Penilaian harian berhasil dibuat',
            'data' => $assessment,
        ], 201);
    }

    public function batchScores(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'scores' => 'required|array|min:1',
            'scores.*.student_id' => 'required|exists:users,id',
            'scores.*.score' => 'required|numeric|min:0|max:100',
            'scores.*.notes' => 'nullable|string',
        ]);

        $assessment = DailyAssessment::findOrFail($id);

        foreach ($request->scores as $item) {
            DailyAssessmentScore::updateOrCreate(
                [
                    'daily_assessment_id' => $id,
                    'student_id' => $item['student_id'],
                ],
                [
                    'score' => (float) $item['score'],
                    'notes' => $item['notes'] ?? null,
                ]
            );
        }

        // Auto trigger remedial calculation info
        $kkm = (float) $assessment->kkm;
        $totalUnder = DailyAssessmentScore::where('daily_assessment_id', $id)->where('score', '<', $kkm)->count();

        return response()->json([
            'success' => true,
            'message' => 'Nilai siswa berhasil disimpan secara massal',
            'data' => [
                'total_saved' => count($request->scores),
                'students_under_kkm' => $totalUnder,
                'kkm' => $kkm,
            ],
        ]);
    }

    public function autoRemedial(Request $request, int $id): JsonResponse
    {
        $deadline = $request->input('deadline');
        $result = $this->remedialService->autoAssignRemedial($id, $request->user()->id, $deadline);

        return response()->json([
            'success' => true,
            'message' => "Otomasi penugasan remedial berhasil. {$result['assigned_to_remedial']} siswa ditugaskan remedial.",
            'data' => $result,
        ]);
    }

    public function studentAssessments(Request $request): JsonResponse
    {
        $studentId = $request->user()->id;

        $scores = DailyAssessmentScore::with(['assessment.subject', 'assessment.classroom'])
            ->where('student_id', $studentId)
            ->latest()
            ->get()
            ->map(function ($scoreItem) use ($studentId) {
                $finalScore = $this->remedialService->getFinalScore($studentId, $scoreItem->daily_assessment_id);

                return [
                    'id' => $scoreItem->id,
                    'assessment_title' => $scoreItem->assessment->title ?? 'PH',
                    'subject_name' => $scoreItem->assessment->subject->name ?? 'Mapel',
                    'assessment_date' => $scoreItem->assessment->assessment_date,
                    'kkm' => (float) ($scoreItem->assessment->kkm ?? 75.0),
                    'original_score' => (float) $scoreItem->score,
                    'final_score' => $finalScore,
                    'is_tuntas' => $finalScore >= (float) ($scoreItem->assessment->kkm ?? 75.0),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $scores,
        ]);
    }

    public function studentRemedials(Request $request): JsonResponse
    {
        $studentId = $request->user()->id;
        $remedials = $this->remedialService->getStudentRemedials($studentId);

        return response()->json([
            'success' => true,
            'data' => $remedials,
        ]);
    }

    public function submitRemedialScore(Request $request, int $sessionId): JsonResponse
    {
        $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'teacher_notes' => 'nullable|string',
        ]);

        $session = $this->remedialService->submitRemedialScore(
            $sessionId,
            (float) $request->score,
            $request->teacher_notes
        );

        return response()->json([
            'success' => true,
            'message' => 'Nilai remedial siswa berhasil disimpan',
            'data' => $session,
        ]);
    }
}
