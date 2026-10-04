<?php

namespace App\Http\Controllers\Academic;

use App\Exceptions\AiUnavailableException;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\LessonPlan;
use App\Models\QuestionBank;
use App\Models\Subject;
use App\Services\AiCopilotService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AiCopilotController extends Controller
{
    protected $copilotService;

    public function __construct(AiCopilotService $copilotService)
    {
        $this->copilotService = $copilotService;
    }

    /**
     * Display Copilot home portal.
     */
    public function index()
    {
        return view('academic.copilot.index');
    }

    /**
     * Show RPP Lesson Plan Generator form.
     */
    public function rppForm()
    {
        $subjects = Subject::orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->get();

        return view('academic.copilot.rpp', compact('subjects', 'classrooms'));
    }

    /**
     * Handle RPP generation request.
     */
    public function generateRpp(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'topic' => 'required|string|max:150',
            'competencies' => 'required|string',
            'provider' => 'required|string',
            'api_key' => 'nullable|string',
            'custom_endpoint' => 'nullable|url',
        ]);

        $subject = Subject::find($request->subject_id);
        $classroom = Classroom::find($request->classroom_id);

        $params = [
            'subject' => $subject->name,
            'topic' => $request->topic,
            'grade_level' => $classroom->name,
            'competencies' => $request->competencies,
        ];

        $rppContent = $this->copilotService->generateLessonPlan(
            $params,
            $request->api_key,
            $request->provider,
            $request->custom_endpoint
        );

        $subjects = Subject::orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->get();

        return view('academic.copilot.rpp', compact('subjects', 'classrooms', 'rppContent'))
            ->withInput($request->all());
    }

    /**
     * Save generated RPP to SISTA Lesson Plans.
     */
    public function saveRppToSista(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'topic' => 'required|string|max:150',
            'competencies' => 'required|string',
            'rpp_content' => 'required|string',
        ]);

        $activeYear = AcademicYear::where('is_active', true)->first() ?: AcademicYear::first();
        if (! $activeYear) {
            return back()->with('error', 'Tahun akademik aktif tidak ditemukan.');
        }

        // Try to parse objectives & activities from generated markdown RPP
        $content = $request->rpp_content;
        $objectives = 'Tujuan pembelajaran terangkum dalam dokumen RPP.';
        $activities = $content; // Store full markdown in activities
        $assessment = 'Asesmen sikap, pengetahuan, dan keterampilan.';
        $resources = 'Buku teks utama & media ajar.';

        // Simple parsing helper
        if (preg_match('/## 2\. TUJUAN PEMBELAJARAN(.*?)(?=##|$)/s', $content, $match)) {
            $objectives = trim($match[1]);
        }
        if (preg_match('/## 5\. ASESMEN(.*?)(?=##|$)/s', $content, $match)) {
            $assessment = trim($match[1]);
        }
        if (preg_match('/## 6\. MEDIA(.*?)(?=##|$)/s', $content, $match)) {
            $resources = trim($match[1]);
        }

        try {
            LessonPlan::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => auth()->id(),
                'subject_id' => $request->subject_id,
                'classroom_id' => $request->classroom_id,
                'academic_year_id' => $activeYear->id,
                'topic' => $request->topic,
                'week_number' => 1,
                'competency_standards' => $request->competencies,
                'learning_objectives' => $objectives,
                'learning_activities' => $activities,
                'assessment_method' => $assessment,
                'resources' => $resources,
                'status' => 'draft',
            ]);

            return redirect()->route('curriculum.lesson-plans.index')
                ->with('success', 'RPP hasil AI berhasil disimpan sebagai draft di kurikulum SISTA.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menyimpan RPP: '.$e->getMessage());
        }
    }

    /**
     * Show CBT Generator form.
     */
    public function cbtForm()
    {
        $subjects = Subject::orderBy('name')->get();

        return view('academic.copilot.cbt', compact('subjects'));
    }

    /**
     * Handle CBT questions generation request.
     */
    public function generateCbt(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'material_text' => 'required|string|min:50',
            'type' => 'required|in:multiple_choice,essay',
            'count' => 'required|integer|min:1|max:15',
            'difficulty' => 'required|in:easy,medium,hard',
            'provider' => 'required|string',
            'api_key' => 'nullable|string',
            'custom_endpoint' => 'nullable|url',
        ]);

        $params = [
            'material_text' => $request->material_text,
            'type' => $request->type,
            'count' => $request->count,
            'difficulty' => $request->difficulty,
        ];

        try {
            $questions = $this->copilotService->generateExamQuestions(
                $params,
                $request->api_key,
                $request->provider,
                $request->custom_endpoint
            );
        } catch (AiUnavailableException $e) {
            return back()->withInput($request->except('api_key'))->with('error', $e->getMessage());
        }

        $subjects = Subject::orderBy('name')->get();

        return view('academic.copilot.cbt', compact('subjects', 'questions'))
            ->withInput($request->all());
    }

    /**
     * Save selected CBT questions to Question Banks.
     */
    public function saveCbtToSista(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'questions' => 'required|array',
            'questions.*.question_text' => 'required|string',
            'questions.*.type' => 'required|string',
            'questions.*.correct_answer' => 'required|string',
            'questions.*.difficulty' => 'required|string',
        ]);

        $selectedIndexes = $request->input('selected_questions', []);
        $allQuestions = $request->input('questions', []);

        if (empty($selectedIndexes)) {
            return back()->with('error', 'Pilih minimal satu soal untuk diimpor ke bank soal.');
        }

        try {
            $importedCount = 0;
            foreach ($selectedIndexes as $index) {
                if (isset($allQuestions[$index])) {
                    $q = $allQuestions[$index];

                    QuestionBank::create([
                        'subject_id' => $request->subject_id,
                        'created_by' => auth()->id(),
                        'question_text' => $q['question_text'],
                        'type' => $q['type'],
                        'options' => $q['options'] ?? null,
                        'correct_answer' => $q['correct_answer'],
                        'explanation' => $q['explanation'] ?? '',
                        'difficulty' => $q['difficulty'],
                        'grade_level' => 'X',
                    ]);
                    $importedCount++;
                }
            }

            return redirect()->route('cbt.question-banks.index')
                ->with('success', "Berhasil mengimpor {$importedCount} soal hasil AI ke Bank Soal SISTA.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal mengimpor soal: '.$e->getMessage());
        }
    }
}
