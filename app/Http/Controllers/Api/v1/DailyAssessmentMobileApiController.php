<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\DailyAssessment;
use App\Models\DailyAssessmentScore;
use App\Models\RemedialSession;
use App\Models\Schedule;
use App\Models\Student;
use App\Services\RemedialWorkflowService;
use App\Support\Access\StudentDataAccess;
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
        // scored_count counts entered scores only: the web creates a blank row per
        // student when an assessment is made. students_count is the class size.
        $assessments = DailyAssessment::with(['subject', 'classroom' => fn ($q) => $q->withCount('students')])
            ->where('teacher_id', $teacherId)
            ->withCount(['scores', 'scores as scored_count' => fn ($q) => $q->whereNotNull('score')])
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

        // Assessments used to land in academic year 1 when none was active, and
        // any teacher could create one for any class and subject.
        $year = AcademicYear::current();
        if (! $year) {
            return response()->json(['success' => false, 'message' => __('Tahun akademik aktif tidak ditemukan')], 400);
        }
        if (! Schedule::teaches($request->user(), (int) $request->classroom_id, (int) $request->subject_id, $year->id)) {
            return response()->json([
                'success' => false,
                'message' => __('Anda tidak mengajar mata pelajaran ini di kelas tersebut pada tahun ajaran aktif.'),
            ], 403);
        }

        $assessment = DailyAssessment::create([
            'subject_id' => $request->subject_id,
            'classroom_id' => $request->classroom_id,
            'teacher_id' => $request->user()->id,
            'academic_year_id' => $year->id,
            'title' => $request->title,
            'type' => $request->input('type', 'ulangan_harian'),
            'assessment_date' => $request->assessment_date,
            'kkm' => (float) $request->kkm,
            'max_score' => (float) ($request->max_score ?? 100.0),
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Penilaian harian berhasil dibuat'),
            'data' => $assessment,
        ], 201);
    }

    /**
     * The assessed class with each student's current score, so the teacher edits
     * what is stored instead of starting blank. `score` is null until entered.
     */
    public function assessmentScores(Request $request, int $id): JsonResponse
    {
        $assessment = DailyAssessment::with(['subject:id,name', 'classroom:id,name'])->findOrFail($id);
        StudentDataAccess::authorizeTeacherOwns($request->user(), $assessment->teacher_id);

        $scores = DailyAssessmentScore::where('daily_assessment_id', $id)->get()->keyBy('student_id');

        // name is an accessor over the related User, so sort after loading.
        $students = Student::where('classroom_id', $assessment->classroom_id)
            ->select('id', 'user_id', 'nis', 'nisn')
            ->with('user:id,name')
            ->get()
            ->sortBy('name')
            ->values()
            ->map(function (Student $student) use ($scores) {
                $row = $scores->get($student->id);

                return [
                    'student_id' => $student->id,
                    'name' => $student->name,
                    'nis' => $student->nis,
                    'nisn' => $student->nisn,
                    'score' => $row?->score !== null ? (float) $row->score : null,
                    'notes' => $row?->notes,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'assessment' => [
                    'id' => $assessment->id,
                    'title' => $assessment->title,
                    'assessment_date' => $assessment->assessment_date?->toDateString(),
                    'kkm' => (float) $assessment->kkm,
                    'max_score' => (float) $assessment->max_score,
                    'subject_name' => $assessment->subject?->name,
                    'classroom_name' => $assessment->classroom?->name,
                ],
                'students' => $students,
            ],
        ]);
    }

    public function batchScores(Request $request, int $id): JsonResponse
    {
        StudentDataAccess::authorizeTeacherOwns($request->user(), DailyAssessment::findOrFail($id)->teacher_id);

        $request->validate([
            'scores' => 'required|array|min:1',
            // daily_assessment_scores.student_id references students.id (not users.id).
            'scores.*.student_id' => 'required|integer|exists:students,id',
            'scores.*.score' => 'required|numeric|min:0|max:100',
            'scores.*.notes' => 'nullable|string',
        ]);

        $assessment = DailyAssessment::findOrFail($id);

        // Only students of the assessed classroom can receive a score.
        $studentIds = collect($request->scores)->pluck('student_id')->map(fn ($v) => (int) $v)->unique();
        $outside = $studentIds->diff(
            Student::whereIn('id', $studentIds)->where('classroom_id', $assessment->classroom_id)->pluck('id')
        );
        if ($outside->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => __('Sebagian siswa bukan anggota kelas penilaian ini.'),
                'errors' => ['scores' => [__('Siswa di luar kelas: :ids', ['ids' => $outside->implode(', ')])]],
            ], 422);
        }

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
            'message' => __('Nilai siswa berhasil disimpan secara massal'),
            'data' => [
                'total_saved' => count($request->scores),
                'students_under_kkm' => $totalUnder,
                'kkm' => $kkm,
            ],
        ]);
    }

    public function autoRemedial(Request $request, int $id): JsonResponse
    {
        StudentDataAccess::authorizeTeacherOwns($request->user(), DailyAssessment::findOrFail($id)->teacher_id);

        $deadline = $request->input('deadline');
        $result = $this->remedialService->autoAssignRemedial($id, $request->user()->id, $deadline);

        return response()->json([
            'success' => true,
            'message' => __('Otomasi penugasan remedial berhasil. Siswa yang ditugaskan remedial: :count.', ['count' => $result['assigned_to_remedial']]),
            'data' => $result,
        ]);
    }

    public function studentAssessments(Request $request): JsonResponse
    {
        // Scores are keyed by students.id, not the signed-in users.id: looking them up
        // by users.id returned whichever student happened to have that id.
        $student = Student::where('user_id', $request->user()->id)->first();
        if (! $student) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $scores = DailyAssessmentScore::with(['dailyAssessment.subject', 'dailyAssessment.classroom'])
            ->where('student_id', $student->id)
            ->whereNotNull('score')
            ->latest()
            ->get()
            ->map(function ($scoreItem) use ($student) {
                $finalScore = $this->remedialService->getFinalScore($student->id, $scoreItem->daily_assessment_id);

                return [
                    'id' => $scoreItem->id,
                    'assessment_title' => $scoreItem->dailyAssessment->title ?? 'PH',
                    'subject_name' => $scoreItem->dailyAssessment->subject->name ?? 'Mapel',
                    'assessment_date' => $scoreItem->dailyAssessment->assessment_date,
                    'kkm' => (float) ($scoreItem->dailyAssessment->kkm ?? 75.0),
                    'original_score' => (float) $scoreItem->score,
                    'final_score' => $finalScore,
                    'is_tuntas' => $finalScore >= (float) ($scoreItem->dailyAssessment->kkm ?? 75.0),
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
        StudentDataAccess::authorizeTeacherOwns($request->user(), RemedialSession::with('assessment')->findOrFail($sessionId)->assessment?->teacher_id);

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
            'message' => __('Nilai remedial siswa berhasil disimpan'),
            'data' => $session,
        ]);
    }
}
