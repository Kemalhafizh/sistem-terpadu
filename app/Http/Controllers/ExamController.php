<?php

namespace App\Http\Controllers;

use App\Events\StudentCheatedEvent;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\QuestionBank;
use App\Models\Subject;
use App\Services\CbtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ExamController extends Controller
{
    protected CbtService $cbtService;

    public function __construct(CbtService $cbtService)
    {
        $this->cbtService = $cbtService;
    }

    /**
     * Display a listing of exams.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Exam::with(['subject', 'classroom', 'creator']);

        // Filter by role
        if ($user->role === 'guru') {
            $query->where('created_by', $user->id);
        } elseif ($user->role === 'siswa') {
            $student = $user->student;
            if ($student) {
                $query->where(function ($q) use ($student) {
                    $q->whereNull('classroom_id')
                      ->orWhere('classroom_id', $student->classroom_id);
                })->where('status', 'published');
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Filters
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('classroom_id')) {
            $query->where('classroom_id', $request->classroom_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $exams = $query->latest()->paginate(15)->withQueryString();

        // Metrics
        $metricsQuery = Exam::query();
        if ($user->role === 'guru') {
            $metricsQuery->where('created_by', $user->id);
        }

        $now = now();
        $allExams = $metricsQuery->get();
        $metrics = [
            'total' => $allExams->count(),
            'draft' => $allExams->where('status', 'draft')->count(),
            'ongoing' => $allExams->where('status', 'published')
                ->filter(fn ($e) => $now->between($e->start_time, $e->end_time))
                ->count(),
            'completed' => $allExams->where('status', 'completed')->count(),
        ];

        $subjects = Subject::all();
        $classrooms = Classroom::all();

        return view('cbt.exams.index', compact('exams', 'subjects', 'classrooms', 'metrics'));
    }

    /**
     * Show the form for creating a new exam.
     */
    public function create()
    {
        Gate::authorize('create', Exam::class);

        $subjects = Subject::all();
        $classrooms = Classroom::all();
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        $questionBanks = QuestionBank::with('subject')->latest()->get();

        return view('cbt.exams.create', compact('subjects', 'classrooms', 'academicYears', 'questionBanks'));
    }

    /**
     * Store a newly created exam.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Exam::class);

        $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'type' => 'required|in:uts,uas,ulangan_harian,try_out',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'duration_minutes' => 'required|integer|min:5|max:300',
            'passing_score' => 'required|numeric|min:0|max:100',
            'max_violations' => 'required|integer|min:1|max:10',
            'question_ids' => 'required|array|min:1',
            'question_ids.*' => 'exists:question_banks,id',
        ]);

        $exam = Exam::create([
            'title' => $request->title,
            'subject_id' => $request->subject_id,
            'classroom_id' => $request->classroom_id,
            'academic_year_id' => $request->academic_year_id,
            'created_by' => auth()->id(),
            'type' => $request->type,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'duration_minutes' => $request->duration_minutes,
            'total_questions' => count($request->question_ids),
            'shuffle_questions' => $request->boolean('shuffle_questions', true),
            'shuffle_options' => $request->boolean('shuffle_options', true),
            'is_secure_mode' => $request->boolean('is_secure_mode', true),
            'max_violations' => $request->max_violations,
            'passing_score' => $request->passing_score,
            'status' => 'published',
        ]);

        // Attach questions with order
        $questionData = [];
        foreach ($request->question_ids as $index => $qId) {
            $questionData[$qId] = ['order' => $index + 1, 'weight' => 1.00];
        }
        $exam->questions()->attach($questionData);

        return redirect()->route('cbt.exams.index')
            ->with('success', 'Ujian berhasil dibuat dan dipublikasikan.');
    }

    /**
     * Display the specified exam.
     */
    public function show(Exam $exam)
    {
        Gate::authorize('view', $exam);

        $exam->load(['subject', 'classroom', 'academicYear', 'creator', 'questions', 'attempts.student.user']);

        return view('cbt.exams.show', compact('exam'));
    }

    /**
     * Show the form for editing the specified exam.
     */
    public function edit(Exam $exam)
    {
        Gate::authorize('update', $exam);

        $subjects = Subject::all();
        $classrooms = Classroom::all();
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        $questionBanks = QuestionBank::with('subject')->latest()->get();

        return view('cbt.exams.edit', compact('exam', 'subjects', 'classrooms', 'academicYears', 'questionBanks'));
    }

    /**
     * Update the specified exam.
     */
    public function update(Request $request, Exam $exam)
    {
        Gate::authorize('update', $exam);

        $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'type' => 'required|in:uts,uas,ulangan_harian,try_out',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'duration_minutes' => 'required|integer|min:5|max:300',
            'passing_score' => 'required|numeric|min:0|max:100',
            'max_violations' => 'required|integer|min:1|max:10',
        ]);

        $exam->update($request->only([
            'title', 'subject_id', 'classroom_id', 'academic_year_id',
            'type', 'start_time', 'end_time', 'duration_minutes',
            'passing_score', 'max_violations',
        ]) + [
            'shuffle_questions' => $request->boolean('shuffle_questions', true),
            'shuffle_options' => $request->boolean('shuffle_options', true),
            'is_secure_mode' => $request->boolean('is_secure_mode', true),
        ]);

        return redirect()->route('cbt.exams.show', $exam)
            ->with('success', 'Ujian berhasil diperbarui.');
    }

    /**
     * Remove the specified exam.
     */
    public function destroy(Exam $exam)
    {
        Gate::authorize('delete', $exam);

        $exam->delete();

        return redirect()->route('cbt.exams.index')
            ->with('success', 'Ujian berhasil dihapus.');
    }

    /**
     * Student takes the exam — initializes or retrieves attempt.
     */
    public function take(Exam $exam)
    {
        Gate::authorize('take', $exam);

        $user = auth()->user();
        $student = $user->student;

        if (!$student) {
            abort(403, 'Data siswa tidak ditemukan.');
        }

        // Check if exam is ongoing
        if (!$exam->isOngoing()) {
            return redirect()->route('cbt.exams.index')
                ->with('error', 'Ujian belum dimulai atau sudah berakhir.');
        }

        // Get or create attempt
        $attempt = ExamAttempt::firstOrCreate(
            ['exam_id' => $exam->id, 'student_id' => $student->id],
            ['started_at' => now(), 'status' => 'in_progress']
        );

        // Check if already submitted or blocked
        if (in_array($attempt->status, ['submitted', 'graded', 'blocked_violation', 'timeout'])) {
            return redirect()->route('cbt.exams.results', $exam)
                ->with('info', 'Anda sudah menyelesaikan ujian ini.');
        }

        // Check if expired
        if ($attempt->isExpired()) {
            $this->cbtService->autoGrade($attempt->fresh());
            $attempt->update(['status' => 'timeout', 'is_auto_submitted' => true, 'submitted_at' => now()]);
            return redirect()->route('cbt.exams.results', $exam)
                ->with('info', 'Waktu ujian Anda telah habis. Jawaban telah disimpan otomatis.');
        }

        // Get deterministic question order
        $questionOrder = $this->cbtService->getOrInitializeQuestionOrder($exam, $student, $attempt);

        // Load questions in order
        $exam->load('questions');
        $questionsMap = $exam->questions->keyBy('id');
        $questions = collect($questionOrder)->map(fn ($id) => $questionsMap->get($id))->filter();

        // Prepare shuffled options for each question
        $shuffledOptions = [];
        foreach ($questions as $question) {
            if ($question->type === 'multiple_choice' && $exam->shuffle_options) {
                $shuffledOptions[$question->id] = $this->cbtService->getShuffledOptions($question, $student->id, $exam->id);
            } else {
                $shuffledOptions[$question->id] = $question->options ?? [];
            }
        }

        return view('cbt.exams.take', compact('exam', 'attempt', 'questions', 'questionOrder', 'shuffledOptions'));
    }

    /**
     * Start exam (enter fullscreen confirmation).
     */
    public function start(Exam $exam)
    {
        Gate::authorize('take', $exam);

        return redirect()->route('cbt.exams.take', $exam);
    }

    /**
     * Auto-save answers via AJAX.
     */
    public function saveAnswers(Request $request, Exam $exam)
    {
        $user = auth()->user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->first();

        if (!$attempt) {
            return response()->json(['error' => 'Attempt not found or already submitted'], 404);
        }

        $answers = $request->input('answers', []);
        $attempt->update(['answers' => $answers]);

        return response()->json(['success' => true, 'message' => 'Jawaban tersimpan.']);
    }

    /**
     * Log a cheating violation via AJAX.
     */
    public function logViolation(Request $request, Exam $exam)
    {
        $user = auth()->user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->first();

        if (!$attempt) {
            return response()->json(['error' => 'Attempt not found'], 404);
        }

        $violationType = $request->input('type', 'unknown');
        $logs = $attempt->violation_logs ?? [];
        $logs[] = [
            'type' => $violationType,
            'time' => now()->toDateTimeString(),
            'user_agent' => $request->userAgent(),
        ];

        $attempt->increment('violation_count');
        $attempt->update(['violation_logs' => $logs]);

        // Broadcast to proctor
        broadcast(new StudentCheatedEvent($attempt->fresh(), $violationType))->toOthers();

        // Check if max violations exceeded
        $isBlocked = false;
        if ($attempt->fresh()->violation_count >= $exam->max_violations) {
            $attempt->update([
                'status' => 'blocked_violation',
                'submitted_at' => now(),
                'is_auto_submitted' => true,
            ]);
            $this->cbtService->autoGrade($attempt->fresh());
            $isBlocked = true;
        }

        return response()->json([
            'success' => true,
            'violation_count' => $attempt->fresh()->violation_count,
            'is_blocked' => $isBlocked,
        ]);
    }

    /**
     * Submit the exam.
     */
    public function submit(Request $request, Exam $exam)
    {
        $user = auth()->user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        if (!$attempt || in_array($attempt->status, ['submitted', 'graded', 'blocked_violation'])) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Already submitted'], 409);
            }
            return redirect()->route('cbt.exams.index')
                ->with('info', 'Ujian sudah disubmit sebelumnya.');
        }

        // Save final answers
        $answers = $request->input('answers', $attempt->answers ?? []);
        $attempt->update([
            'answers' => $answers,
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        // Auto-grade
        $this->cbtService->autoGrade($attempt->fresh());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'redirect' => route('cbt.exams.results', $exam),
            ]);
        }

        return redirect()->route('cbt.exams.results', $exam)
            ->with('success', 'Ujian berhasil dikumpulkan. Nilai telah dihitung.');
    }

    /**
     * Real-time proctor monitoring dashboard.
     */
    public function monitor(Exam $exam)
    {
        Gate::authorize('monitor', $exam);

        $exam->load(['subject', 'classroom']);

        $rawAttempts = ExamAttempt::with(['student.user'])
            ->where('exam_id', $exam->id)
            ->get();

        $attempts = $rawAttempts->map(function ($a) use ($exam) {
            return [
                'uuid' => $a->uuid,
                'student_name' => $a->student?->name ?? 'Siswa',
                'student_nis' => $a->student?->nis ?? '-',
                'status' => $a->status,
                'violation_count' => $a->violation_count,
                'correct_count' => $a->correct_count,
                'score' => $a->score,
                'answers_count' => is_array($a->answers) ? count(array_filter($a->answers, fn ($v) => $v !== null && $v !== '')) : 0,
                'total_questions' => $exam->total_questions,
                'started_at' => $a->started_at?->format('H:i'),
                'submitted_at' => $a->submitted_at?->format('H:i'),
            ];
        })->values()->toArray();

        return view('cbt.exams.monitor', compact('exam', 'attempts'));
    }

    /**
     * Exam results & analytics dashboard.
     */
    public function results(Exam $exam)
    {
        $user = auth()->user();
        $exam->load(['subject', 'classroom', 'questions']);

        $attemptsQuery = ExamAttempt::with(['student.user'])
            ->where('exam_id', $exam->id);

        // Students can only see their own results
        if ($user->role === 'siswa' && $user->student) {
            $attemptsQuery->where('student_id', $user->student->id);
        }

        $allAttempts = $attemptsQuery->get();
        $attempts = $attemptsQuery->orderByDesc('score')->paginate(20);

        // Calculate stats
        $scores = $allAttempts->pluck('score')->filter(fn ($s) => !is_null($s));
        $passedCount = $scores->filter(fn ($s) => $s >= $exam->passing_score)->count();

        $stats = [
            'average' => $scores->count() > 0 ? round($scores->avg(), 2) : 0,
            'highest' => $scores->count() > 0 ? round($scores->max(), 2) : 0,
            'lowest' => $scores->count() > 0 ? round($scores->min(), 2) : 0,
            'pass_rate' => $scores->count() > 0 ? round(($passedCount / $scores->count()) * 100, 1) : 0,
            'total_students' => $allAttempts->count(),
            'passed_count' => $passedCount,
        ];

        return view('cbt.exams.results', compact('exam', 'attempts', 'stats'));
    }
}
