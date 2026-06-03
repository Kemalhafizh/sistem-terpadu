<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\GradeService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GradeController extends Controller
{
    /**
     * @var GradeService
     */
    protected $gradeService;

    public function __construct(GradeService $gradeService)
    {
        $this->gradeService = $gradeService;
    }

    private function getGradeContext(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $subjects = ($user && $user->role === 'guru') ? $user->subjects : Subject::orderBy('name')->get();
        $classrooms = Classroom::all();

        $selectedClassroomId = $request->input('classroom_id');
        $selectedSubjectId = $request->input('subject_id');
        $activeYear = AcademicYear::where('is_active', true)->first();

        $students = collect([]);
        $grades = collect([]);

        if ($selectedClassroomId && $selectedSubjectId && $activeYear) {
            $students = Student::with('user')
                ->where('classroom_id', $selectedClassroomId)
                ->get()
                ->sortBy(fn ($s) => $s->user?->name);

            $grades = Grade::where('subject_id', $selectedSubjectId)
                ->where('academic_year_id', $activeYear->id)
                ->whereIn('student_id', $students->pluck('id'))
                ->get();
        }

        return [
            'subjects' => $subjects,
            'classrooms' => $classrooms,
            'selectedClassroomId' => $selectedClassroomId,
            'selectedSubjectId' => $selectedSubjectId,
            'activeYear' => $activeYear,
            'students' => $students,
            'grades' => $grades,
        ];
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Grade::class);

        $context = $this->getGradeContext($request);

        // Group grades by student_id, then key each by type
        $gradeData = $context['grades']->groupBy('student_id')->map(function (Collection $studentGrades) {
            return $studentGrades->keyBy('type');
        });

        $subject = $context['selectedSubjectId'] ? Subject::find($context['selectedSubjectId']) : null;

        $data = [
            'classrooms' => $context['classrooms'],
            'subjects' => $context['subjects'],
            'selectedClassroomId' => $context['selectedClassroomId'],
            'selectedSubjectId' => $context['selectedSubjectId'],
            'students' => $context['students'],
            'gradeData' => $gradeData,
            'subject' => $subject,
        ];

        return view('grades.index', $data);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Grade::class);

        $context = $this->getGradeContext($request);
        $existingGrades = $context['grades']->groupBy('student_id');

        return view('grades.create', [
            'classrooms' => $context['classrooms'],
            'subjects' => $context['subjects'],
            'selectedClassroomId' => $context['selectedClassroomId'],
            'selectedSubjectId' => $context['selectedSubjectId'],
            'students' => $context['students'],
            'existingGrades' => $existingGrades,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Grade::class);

        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id' => 'required|exists:subjects,id',
            'grades' => 'required|array',
            'grades.*.tugas' => 'nullable|numeric|min:0|max:100',
            'grades.*.uh' => 'nullable|numeric|min:0|max:100',
            'grades.*.pts' => 'nullable|numeric|min:0|max:100',
            'grades.*.pas' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $count = $this->gradeService->recordBulkGrades(
                $request->grades,
                (int) $request->subject_id,
                Auth::id()
            );

            return redirect()->route('grades.create', [
                'classroom_id' => $request->classroom_id,
                'subject_id' => $request->subject_id,
            ])->with('success', "Data nilai berhasil disimpan secara massal ($count nilai).");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
