<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\P5Project;
use App\Models\P5ProjectAssessment;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use App\Services\Academic\P5ProjectService;
use App\Support\Access\StudentDataAccess;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class P5ProjectController extends Controller
{
    public function __construct(
        private readonly P5ProjectService $p5Service,
    ) {}

    public function index(Request $request): View
    {
        $dimensions = $this->p5Service->ensureOfficialDimensions();
        $schoolYears = SchoolYear::orderByDesc('name')->get();
        $selectedYearId = $request->filled('school_year_id')
            ? (int) $request->integer('school_year_id')
            : SchoolYear::where('status', SchoolYear::STATUS_ACTIVE)->value('id');
        $gradeFilter = $request->filled('grade_level')
            ? strtoupper(trim((string) $request->string('grade_level')))
            : null;

        $projects = P5Project::query()
            ->with(['schoolYear', 'coordinator', 'dimensions.characterDimension'])
            ->withCount(['assessments', 'studentNotes'])
            ->when($selectedYearId, fn ($q) => $q->where('school_year_id', $selectedYearId))
            ->when($gradeFilter, fn ($q) => $q->where('grade_level', $gradeFilter))
            ->latest('id')
            ->get();

        $teachers = User::query()
            ->whereIn('role', [...StudentDataAccess::TEACHER_ROLES, 'wali_kelas', 'waka_kurikulum'])
            ->where('account_status', '!=', 'nonaktif')
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        $themes = P5Project::THEMES;
        $canManage = $this->p5Service->canManageProjects($request->user());

        return view('p5.index', compact(
            'projects',
            'dimensions',
            'schoolYears',
            'selectedYearId',
            'gradeFilter',
            'teachers',
            'themes',
            'canManage',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_year_id' => ['nullable', 'exists:school_years,id'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'grade_level' => ['required', Rule::in(['X', 'XI', 'XII'])],
            'theme' => ['required', 'string', Rule::in(array_keys(P5Project::THEMES))],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'coordinator_id' => ['nullable', 'exists:users,id'],
            'status' => ['nullable', Rule::in([P5Project::STATUS_DRAFT, P5Project::STATUS_ACTIVE, P5Project::STATUS_CLOSED])],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'dimensions' => ['required', 'array', 'min:1'],
            'dimensions.*.character_dimension_id' => ['required', 'exists:character_dimensions,id'],
            'dimensions.*.element' => ['required', 'string', 'max:160'],
            'dimensions.*.sub_element' => ['nullable', 'string', 'max:160'],
            'dimensions.*.target_achievement' => ['required', 'string', 'max:1000'],
            'facilitators' => ['nullable', 'array'],
            'facilitators.*.user_id' => ['required_with:facilitators', 'exists:users,id'],
            'facilitators.*.classroom_id' => ['nullable', 'exists:classrooms,id'],
        ]);

        $project = $this->p5Service->createProject($request->user(), $validated);

        return redirect()
            ->route('p5.projects.show', $project)
            ->with('success', __('Projek P5 berhasil dibuat dan siap digunakan untuk penilaian fasilitator.'));
    }

    public function show(Request $request, P5Project $project): View
    {
        $project->load([
            'schoolYear',
            'academicYear',
            'coordinator',
            'dimensions.characterDimension',
            'facilitators.user',
            'facilitators.classroom',
        ]);

        $classrooms = Classroom::query()
            ->where('grade_level', $project->grade_level)
            ->when($project->school_year_id, function ($q) use ($project) {
                $q->where(function ($sq) use ($project) {
                    $sq->where('school_year_id', $project->school_year_id);
                    if ($project->schoolYear?->name) {
                        $sq->orWhere('academic_year', $project->schoolYear->name);
                    }
                });
            })
            ->orderBy('name')
            ->get();

        $selectedClassroomId = $request->filled('classroom_id')
            ? (int) $request->integer('classroom_id')
            : $classrooms->first()?->id;

        $students = collect();
        if ($selectedClassroomId) {
            $students = Student::query()
                ->with(['user', 'classroom'])
                ->where('classroom_id', $selectedClassroomId)
                ->where('status', 'active')
                ->orderBy('nis')
                ->get();
        }

        $existingAssessments = P5ProjectAssessment::query()
            ->where('p5_project_id', $project->id)
            ->when($students->isNotEmpty(), fn ($q) => $q->whereIn('student_id', $students->pluck('id')))
            ->get()
            ->groupBy('student_id')
            ->map(fn ($rows) => $rows->keyBy('p5_project_dimension_id'));

        $existingNotes = $project->studentNotes()
            ->when($students->isNotEmpty(), fn ($q) => $q->whereIn('student_id', $students->pluck('id')))
            ->get()
            ->keyBy('student_id');

        $dimensionsCatalog = $this->p5Service->ensureOfficialDimensions();
        $teachers = User::query()
            ->whereIn('role', [...StudentDataAccess::TEACHER_ROLES, 'wali_kelas', 'waka_kurikulum'])
            ->where('account_status', '!=', 'nonaktif')
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        $predicates = P5ProjectAssessment::PREDICATES;
        $themes = P5Project::THEMES;
        $canManage = $this->p5Service->canManageProjects($request->user(), $project);

        return view('p5.show', compact(
            'project',
            'classrooms',
            'selectedClassroomId',
            'students',
            'existingAssessments',
            'existingNotes',
            'dimensionsCatalog',
            'teachers',
            'predicates',
            'themes',
            'canManage',
        ));
    }

    public function update(Request $request, P5Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'school_year_id' => ['sometimes', 'nullable', 'exists:school_years,id'],
            'academic_year_id' => ['sometimes', 'nullable', 'exists:academic_years,id'],
            'grade_level' => ['sometimes', Rule::in(['X', 'XI', 'XII'])],
            'theme' => ['sometimes', 'string', Rule::in(array_keys(P5Project::THEMES))],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'coordinator_id' => ['nullable', 'exists:users,id'],
            'status' => ['sometimes', Rule::in([P5Project::STATUS_DRAFT, P5Project::STATUS_ACTIVE, P5Project::STATUS_CLOSED])],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'dimensions' => ['sometimes', 'array', 'min:1'],
            'dimensions.*.character_dimension_id' => ['required_with:dimensions', 'exists:character_dimensions,id'],
            'dimensions.*.element' => ['required_with:dimensions', 'string', 'max:160'],
            'dimensions.*.sub_element' => ['nullable', 'string', 'max:160'],
            'dimensions.*.target_achievement' => ['required_with:dimensions', 'string', 'max:1000'],
            'facilitators' => ['sometimes', 'array'],
            'facilitators.*.user_id' => ['required_with:facilitators', 'exists:users,id'],
            'facilitators.*.classroom_id' => ['nullable', 'exists:classrooms,id'],
        ]);

        $this->p5Service->updateProject($project, $request->user(), $validated);

        return redirect()
            ->route('p5.projects.show', $project)
            ->with('success', __('Konfigurasi Projek P5 berhasil diperbarui.'));
    }

    public function updateAssessments(Request $request, P5Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'assessments' => ['required', 'array', 'min:1'],
            'classroom_id' => ['nullable', 'integer'],
        ]);

        $summary = $this->p5Service->recordBulkAssessments($project, $request->user(), $validated);

        $redirectParams = ['project' => $project];
        if (! empty($validated['classroom_id'])) {
            $redirectParams['classroom_id'] = (int) $validated['classroom_id'];
        }

        return redirect()
            ->route('p5.projects.show', $redirectParams)
            ->with('success', __('Penilaian Projek P5 berhasil disimpan (:count predikat diperbarui).', [
                'count' => $summary['saved_assessments'],
            ]));
    }

    public function studentReport(Request $request, Student $student): View
    {
        if (! $this->p5Service->canViewStudentReport($request->user(), $student)) {
            abort(403, __('Anda tidak berwenang mengakses Rapor Projek P5 siswa ini.'));
        }

        $schoolYearId = $request->filled('school_year_id') ? (int) $request->integer('school_year_id') : null;
        $report = $this->p5Service->buildStudentReport($student, $schoolYearId);

        return view('p5.report', compact('report', 'student'));
    }

    public function studentReportPdf(Request $request, Student $student)
    {
        if (! $this->p5Service->canViewStudentReport($request->user(), $student)) {
            abort(403, __('Anda tidak berwenang mengunduh Rapor Projek P5 siswa ini.'));
        }

        $schoolYearId = $request->filled('school_year_id') ? (int) $request->integer('school_year_id') : null;
        $report = $this->p5Service->buildStudentReport($student, $schoolYearId);

        $pdf = Pdf::loadView('p5.report-pdf', [
            'report' => $report,
            'student' => $student,
        ])->setPaper('a4', 'portrait');

        $safeNis = preg_replace('/[^A-Za-z0-9\-_]/', '', (string) ($student->nis ?: $student->uuid));

        return $pdf->stream("Rapor-Projek-P5-{$safeNis}.pdf");
    }
}
