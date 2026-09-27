<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\DailyAssessment;
use App\Models\DailyAssessmentScore;
use App\Models\Student;
use App\Models\Subject;
use App\Services\DailyAssessmentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyAssessmentController extends Controller
{
    protected $service;

    public function __construct(DailyAssessmentService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of assessments.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $classrooms = Classroom::all();
        $subjects = Subject::all();

        $query = DailyAssessment::with(['subject', 'classroom', 'academicYear', 'scores']);

        if ($user->role === 'guru') {
            $query->where('teacher_id', $user->id);
        }

        if ($request->filled('classroom_id')) {
            $query->where('classroom_id', $request->classroom_id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        $assessments = $query->latest()->get();

        return view('daily-assessments.index', compact('assessments', 'classrooms', 'subjects'));
    }

    /**
     * Show the form for creating a new assessment.
     */
    public function create()
    {
        $classrooms = Classroom::all();
        $subjects = Subject::all();

        return view('daily-assessments.create', compact('classrooms', 'subjects'));
    }

    /**
     * Store a newly created assessment in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'title' => 'required|string|max:255',
            'type' => 'required|in:ulangan_harian,kuis,praktikum,project,presentasi,tugas',
            'assessment_date' => 'required|date',
            'max_score' => 'required|numeric|min:0|max:100',
            'kkm' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string|max:1000',
        ]);

        $data = $request->all();
        $data['teacher_id'] = Auth::id();

        try {
            $assessment = $this->service->createAssessment($data);

            return redirect()->route('daily-assessments.input-scores', $assessment->id)
                ->with('success', 'Penilaian harian berhasil dibuat. Silakan masukkan nilai siswa.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat penilaian: '.$e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified assessment.
     */
    public function show($id)
    {
        $assessment = DailyAssessment::with(['subject', 'classroom', 'academicYear', 'scores.student.user'])
            ->findOrFail($id);

        $scores = $assessment->scores;
        $totalStudents = $scores->count();
        $validScores = $scores->filter(fn ($s) => $s->final_score !== null);

        $average = $validScores->isEmpty() ? 0 : round($validScores->avg('final_score'), 1);
        $kkm = $assessment->effective_kkm;

        $passedCount = $scores->filter(fn ($s) => $s->is_passed)->count();
        $remedialCount = $scores->filter(fn ($s) => $s->needs_remedial && $s->remedial_status !== 'completed')->count();

        // Distribution brackets
        $distribution = [0, 0, 0, 0, 0];
        foreach ($validScores as $s) {
            $val = $s->final_score;
            if ($val >= 90) {
                $distribution[4]++;
            } elseif ($val >= 80) {
                $distribution[3]++;
            } elseif ($val >= 70) {
                $distribution[2]++;
            } elseif ($val >= 60) {
                $distribution[1]++;
            } else {
                $distribution[0]++;
            }
        }

        return view('daily-assessments.show', compact('assessment', 'scores', 'totalStudents', 'average', 'passedCount', 'remedialCount', 'distribution', 'kkm'));
    }

    /**
     * Show form to input scores.
     */
    public function inputScores($id)
    {
        $assessment = DailyAssessment::with(['subject', 'classroom', 'scores.student.user'])
            ->findOrFail($id);

        return view('daily-assessments.input-scores', compact('assessment'));
    }

    /**
     * Store student scores.
     */
    public function storeScores(Request $request, $id)
    {
        $request->validate([
            'grades' => 'required|array',
            'grades.*.score' => 'nullable|numeric|min:0|max:100',
            'grades.*.notes' => 'nullable|string|max:500',
        ]);

        try {
            $this->service->batchStoreScores($id, $request->grades);

            return redirect()->route('daily-assessments.show', $id)
                ->with('success', 'Nilai siswa berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan nilai: '.$e->getMessage());
        }
    }

    /**
     * Display listing of students needing remedial.
     */
    public function remedialList()
    {
        $user = Auth::user();

        $query = DailyAssessmentScore::where('needs_remedial', true)
            ->where('remedial_status', '!=', 'completed')
            ->with(['student.user', 'dailyAssessment.subject', 'dailyAssessment.classroom']);

        if ($user->role === 'guru') {
            $query->whereHas('dailyAssessment', function ($q) use ($user) {
                $q->where('teacher_id', $user->id);
            });
        }

        $remedials = $query->latest()->get();

        return view('daily-assessments.remedial-list', compact('remedials'));
    }

    /**
     * Show form to input remedial score.
     */
    public function inputRemedialScore($scoreId)
    {
        $score = DailyAssessmentScore::with(['student.user', 'dailyAssessment.subject'])->findOrFail($scoreId);

        return view('daily-assessments.input-remedial', compact('score'));
    }

    /**
     * Store remedial score.
     */
    public function storeRemedialScore(Request $request, $scoreId)
    {
        $request->validate([
            'remedial_score' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $this->service->storeRemedial($scoreId, (float) $request->remedial_score, $request->notes);

            return redirect()->route('daily-assessments.remedial')
                ->with('success', 'Nilai remedial berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan nilai remedial: '.$e->getMessage());
        }
    }

    /**
     * Display classroom analytics.
     */
    public function analytics(Request $request, $classroomId)
    {
        $classroom = Classroom::findOrFail($classroomId);
        $subjects = Subject::all();

        $selectedSubjectId = $request->input('subject_id') ?? $subjects->first()?->id;

        $analytics = $this->service->getClassAnalytics($classroomId, $selectedSubjectId);

        return view('daily-assessments.analytics', compact('classroom', 'subjects', 'selectedSubjectId', 'analytics'));
    }

    /**
     * Export PDF report of assessment scores.
     */
    public function exportPdf($id)
    {
        $assessment = DailyAssessment::with(['subject', 'classroom', 'academicYear', 'scores.student.user'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('daily-assessments.pdf_export', compact('assessment'));
        $pdf->setPaper('a4', 'portrait');

        $safeTitle = str_replace(' ', '_', $assessment->title);

        return $pdf->download("Daftar_Nilai_Harian_{$safeTitle}.pdf");
    }
}
