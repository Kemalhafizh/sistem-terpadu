<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\Classroom;
use App\Models\User;
use App\Services\SurveyAnalyticsService;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SurveyController extends Controller
{
    protected $analyticsService;
    protected $whatsappService;

    public function __construct(SurveyAnalyticsService $analyticsService, WhatsappService $whatsappService)
    {
        $this->analyticsService = $analyticsService;
        $this->whatsappService = $whatsappService;
    }

    /**
     * Display a listing of surveys.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            // Admins can see all surveys categorized by status
            $draftSurveys = Survey::with('classroom')->where('status', 'draft')->latest()->get();
            $activeSurveys = Survey::with('classroom')->where('status', 'active')->latest()->get();
            $closedSurveys = Survey::with('classroom')->whereIn('status', ['closed', 'archived'])->latest()->get();

            return view('surveys.index', compact('draftSurveys', 'activeSurveys', 'closedSurveys'));
        }

        // Standard users (students, parents, teachers) can only see surveys targeted at them
        // that are active, open, and NOT yet answered by them.
        $targetRole = $user->role;
        $classroomId = null;

        if ($targetRole === 'student' && $user->student) {
            $classroomId = $user->student->classroom_id;
        }

        $surveys = Survey::with('classroom')->active()
            ->where(function ($query) use ($targetRole) {
                $query->whereNull('target_role')
                      ->orWhere('target_role', $targetRole);
            })
            ->where(function ($query) use ($classroomId) {
                $query->whereNull('target_classroom_id')
                      ->orWhere('target_classroom_id', $classroomId);
            })
            ->whereDoesntHave('responses', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get()
            ->filter(fn($survey) => $survey->isOpen());

        return view('surveys.index', compact('surveys'));
    }

    /**
     * Show the form for creating a new survey.
     */
    public function create()
    {
        $this->authorizeAdmin();
        $classrooms = Classroom::orderBy('name')->get();
        return view('surveys.create', compact('classrooms'));
    }

    /**
     * Store a newly created survey and its questions.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_role' => 'nullable|string|in:student,parent,guru,admin',
            'target_classroom_id' => 'nullable|exists:classrooms,id',
            'is_anonymous' => 'required|boolean',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'max_responses' => 'nullable|integer|min:1',
            'send_notification' => 'required|boolean',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string|max:255',
            'questions.*.type' => 'required|string|in:text,textarea,radio,checkbox,select,scale,date',
            'questions.*.options' => 'nullable|array',
            'questions.*.is_required' => 'required|boolean',
            'questions.*.scale_min' => 'nullable|integer|min:0|max:5',
            'questions.*.scale_max' => 'nullable|integer|min:1|max:10',
            'questions.*.scale_min_label' => 'nullable|string|max:100',
            'questions.*.scale_max_label' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('toast_error', 'Gagal membuat survei. Periksa kembali inputan Anda.');
        }

        try {
            DB::transaction(function () use ($request) {
                $survey = Survey::create([
                    'title' => $request->title,
                    'description' => $request->description,
                    'created_by' => Auth::id(),
                    'status' => 'draft', // Created as draft first
                    'target_role' => $request->target_role,
                    'target_classroom_id' => $request->target_classroom_id,
                    'is_anonymous' => $request->is_anonymous,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'max_responses' => $request->max_responses,
                    'send_notification' => $request->send_notification,
                ]);

                foreach ($request->questions as $index => $qData) {
                    SurveyQuestion::create([
                        'survey_id' => $survey->id,
                        'question_text' => $qData['question_text'],
                        'type' => $qData['type'],
                        'options' => $qData['options'] ?? null,
                        'is_required' => $qData['is_required'],
                        'order' => $index,
                        'scale_min' => $qData['scale_min'] ?? null,
                        'scale_max' => $qData['scale_max'] ?? null,
                        'scale_min_label' => $qData['scale_min_label'] ?? null,
                        'scale_max_label' => $qData['scale_max_label'] ?? null,
                    ]);
                }
            });

            return redirect()->route('surveys.index')
                ->with('toast_success', 'Survei berhasil dibuat sebagai Draft.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('toast_error', 'Gagal menyimpan survei: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified survey (takes respondents to dynamic form, or shows preview for admin).
     */
    public function show(Survey $survey)
    {
        $user = Auth::user();

        // Admin sees the survey details/preview
        if ($user->isAdmin()) {
            $survey->load(['questions', 'classroom']);
            return view('surveys.show_preview', compact('survey'));
        }

        // Respondent validation
        if (!$survey->isOpen()) {
            return redirect()->route('surveys.index')
                ->with('toast_error', 'Survei ini sudah tidak aktif atau ditutup.');
        }

        // Role verification
        if ($survey->target_role && !$user->hasRole($survey->target_role)) {
            abort(403, 'Survei ini tidak ditujukan untuk peran Anda.');
        }

        // Classroom verification for students
        if ($survey->target_classroom_id) {
            if ($user->role !== 'student' || !$user->student || $user->student->classroom_id != $survey->target_classroom_id) {
                abort(403, 'Survei ini hanya untuk kelas tertentu.');
            }
        }

        // Check if already responded
        $hasResponded = SurveyResponse::where('survey_id', $survey->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($hasResponded) {
            return redirect()->route('surveys.index')
                ->with('toast_error', 'Anda telah mengisi survei ini.');
        }

        $survey->load(['questions', 'classroom']);
        return view('surveys.respond', compact('survey'));
    }

    /**
     * Store respondent answers.
     */
    public function respond(Request $request, Survey $survey)
    {
        $user = Auth::user();

        if (!$survey->isOpen()) {
            return redirect()->route('surveys.index')->with('toast_error', 'Survei ini sudah ditutup.');
        }

        // Role & Classroom Check
        if ($survey->target_role && !$user->hasRole($survey->target_role)) {
            abort(403);
        }
        if ($survey->target_classroom_id) {
            if ($user->role !== 'student' || !$user->student || $user->student->classroom_id != $survey->target_classroom_id) {
                abort(403);
            }
        }

        // Enforce single response restriction
        $alreadySubmitted = SurveyResponse::where('survey_id', $survey->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadySubmitted) {
            return redirect()->route('surveys.index')->with('toast_error', 'Anda sudah mengisi survei ini.');
        }

        // Dynamic validation rules based on question requirements
        $rules = [];
        $messages = [];
        foreach ($survey->questions as $question) {
            $key = 'answers.' . $question->id;
            $requiredRule = $question->is_required ? 'required' : 'nullable';

            if ($question->type === 'checkbox') {
                $rules[$key] = [$requiredRule, 'array'];
                $messages[$key . '.required'] = 'Pertanyaan "' . $question->question_text . '" wajib dipilih minimal satu.';
            } elseif ($question->type === 'scale') {
                $rules[$key] = [$requiredRule, 'integer', 'min:' . $question->scale_min, 'max:' . $question->scale_max];
                $messages[$key . '.required'] = 'Pertanyaan "' . $question->question_text . '" wajib diberi rating.';
            } else {
                $rules[$key] = [$requiredRule, 'string'];
                $messages[$key . '.required'] = 'Pertanyaan "' . $question->question_text . '" wajib diisi.';
            }
        }

        $request->validate($rules, $messages);

        // Store responses securely
        SurveyResponse::create([
            'survey_id' => $survey->id,
            'user_id' => $user->id, // Keeps unique constraints safe, obfuscated from viewer
            'answers' => $request->answers,
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('surveys.index')
            ->with('toast_success', 'Terima kasih atas partisipasi Anda! Respons berhasil disimpan.');
    }

    /**
     * Display survey analytics and aggregates dashboard.
     */
    public function results(Survey $survey)
    {
        $this->authorizeAdmin();

        $survey->load(['questions', 'classroom']);
        $analytics = $this->analyticsService->getAggregatedResults($survey);
        $responseRate = $this->analyticsService->getResponseRate($survey);

        // Fetch raw responses safely (respecting anonymity to hide names/emails)
        $rawResponses = SurveyResponse::where('survey_id', $survey->id)
            ->with('user')
            ->latest()
            ->paginate(25);

        return view('surveys.results', compact('survey', 'analytics', 'responseRate', 'rawResponses'));
    }

    /**
     * Activate the survey (draft -> active).
     */
    public function activate(Survey $survey)
    {
        $this->authorizeAdmin();

        if ($survey->status !== 'draft') {
            return redirect()->back()->with('toast_error', 'Hanya survei berstatus Draft yang dapat diaktifkan.');
        }

        $survey->update([
            'status' => 'active',
            'start_date' => $survey->start_date ?? now(),
        ]);

        // Trigger WhatsApp broadcast if configured
        if ($survey->send_notification) {
            $this->broadcastNotification($survey);
        }

        return redirect()->route('surveys.index')
            ->with('toast_success', 'Survei berhasil diaktifkan dan dipublikasikan.');
    }

    /**
     * Close the survey (active -> closed).
     */
    public function close(Survey $survey)
    {
        $this->authorizeAdmin();

        if ($survey->status !== 'active') {
            return redirect()->back()->with('toast_error', 'Hanya survei Aktif yang dapat ditutup.');
        }

        $survey->update([
            'status' => 'closed',
            'end_date' => $survey->end_date ?? now(),
        ]);

        return redirect()->route('surveys.index')
            ->with('toast_success', 'Survei berhasil ditutup.');
    }

    /**
     * Export analytics as formal PDF.
     */
    public function exportPdf(Survey $survey)
    {
        $this->authorizeAdmin();
        
        $pdf = $this->analyticsService->exportResultsPdf($survey);
        $filename = 'Laporan_Survei_' . str_replace(' ', '_', $survey->title) . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Export raw responses as Excel.
     */
    public function exportExcel(Survey $survey)
    {
        $this->authorizeAdmin();
        return $this->analyticsService->exportResultsExcel($survey);
    }

    /**
     * Delete/Archive a survey.
     */
    public function destroy(Survey $survey)
    {
        $this->authorizeAdmin();
        $survey->delete();

        return redirect()->route('surveys.index')
            ->with('toast_success', 'Survei berhasil dihapus/diarsipkan.');
    }

    /**
     * Safety check helper for admin roles.
     */
    protected function authorizeAdmin()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Akses ditolak. Peran Anda tidak diizinkan untuk mengelola survei.');
        }
    }

    /**
     * Broadcast survey notification to target respondents using WhatsApp Gateway.
     */
    protected function broadcastNotification(Survey $survey)
    {
        $message = "📢 *SISTEM INFORMASI TERPADU SMA ISLAM SULTAN AGUNG 1*\n\n"
                 . "Ada kuesioner/survei digital baru yang memerlukan partisipasi Anda:\n"
                 . "📌 *Judul:* {$survey->title}\n"
                 . "📝 *Sifat:* " . ($survey->is_anonymous ? "Anonim (Rahasia & PDP Protected)" : "Umum") . "\n\n"
                 . "Mohon luangkan waktu 2-3 menit untuk mengisi survei ini pada portal Sistem Terpadu Anda.\n"
                 . "Link: " . route('surveys.show', $survey->uuid) . "\n\n"
                 . "_Terima kasih atas kerja samanya._";

        // Query targeted users
        $query = User::query()->whereNotNull('phone_number');

        if ($survey->target_role) {
            $query->where('role', $survey->target_role);
        }

        if ($survey->target_classroom_id) {
            $query->whereHas('student', function($q) use ($survey) {
                $q->where('classroom_id', $survey->target_classroom_id);
            });
        }

        // Safe loop broadcast
        $query->chunk(100, function ($users) use ($message) {
            foreach ($users as $user) {
                $this->whatsappService->sendMessage(
                    $user->phone_number,
                    $message,
                    'survey_broadcast'
                );
            }
        });
    }
}
