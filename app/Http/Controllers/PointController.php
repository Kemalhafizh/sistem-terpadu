<?php

namespace App\Http\Controllers;

use App\Http\Middleware\RoleMiddleware;
use App\Models\PointCategory;
use App\Models\Student;
use App\Models\StudentPointLog;
use App\Models\WarningLetter;
use App\Services\DisciplineService;
use App\Support\Access\Roles;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PointController extends Controller
{
    public function __construct(
        private readonly DisciplineService $disciplineService,
    ) {}

    /**
     * Display a listing of students with their total points and school SP threshold configuration.
     */
    public function index(Request $request)
    {
        $query = Student::with(['user', 'classroom', 'pointLogs']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($outer) use ($search) {
                $outer->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $students = $query->paginate(10);
        $categories = PointCategory::where('is_active', true)->get();
        $thresholds = WarningLetter::thresholds();
        $attentionThreshold = WarningLetter::attentionThreshold();
        $mandatoryCounselingThreshold = WarningLetter::mandatoryCounselingThreshold();
        $canManageThresholds = RoleMiddleware::allowsUser(
            $request->user(),
            ['waka_kesiswaan', 'bk', ...Roles::PRINCIPAL, 'admin', 'superadmin']
        );

        return view('points.index', compact(
            'students',
            'categories',
            'thresholds',
            'attentionThreshold',
            'mandatoryCounselingThreshold',
            'canManageThresholds',
        ));
    }

    /**
     * Display the point history and warning letters for a specific student.
     */
    public function show(Student $student)
    {
        $student->load(['user', 'classroom', 'pointLogs']);
        $logs = $student->pointLogs()->with(['creator', 'category'])->latest()->paginate(20);
        $categories = PointCategory::where('is_active', true)->get();
        $warningLetters = WarningLetter::with(['issuer', 'counselingSession', 'parent'])
            ->where('student_id', $student->id)
            ->orderByDesc('point_threshold')
            ->get();
        $thresholds = WarningLetter::thresholds();

        return view('points.show', compact('student', 'logs', 'categories', 'warningLetters', 'thresholds'));
    }

    /**
     * Store a new point log entry.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'point_category_id' => 'nullable|exists:point_categories,id',
            'points' => 'required_without:point_category_id|nullable|integer',
            'type' => 'required_without:point_category_id|nullable|in:penalty,reward,adjustment',
            'description' => 'required|string|max:500',
        ]);

        $points = $validated['points'] ?? null;
        $type = $validated['type'] ?? null;

        if ($request->point_category_id) {
            $category = PointCategory::findOrFail($request->point_category_id);
            $points = $category->default_points;
            $type = $category->type;
        }

        StudentPointLog::create([
            'student_id' => $validated['student_id'],
            'point_category_id' => $validated['point_category_id'] ?? null,
            'created_by' => auth()->id(),
            'points' => $points,
            'type' => $type,
            'description' => $validated['description'],
        ]);

        return back()->with('success', 'Catatan poin berhasil disimpan.');
    }

    /**
     * Update school-wide SP1/SP2/SP3 & mandatory counseling thresholds (`ALUR_KERJA.md` E1).
     */
    public function updateThresholds(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'attention_threshold' => ['required', 'integer', 'min:1'],
            'sp1_threshold' => ['required', 'integer', 'gt:attention_threshold'],
            'sp2_threshold' => ['required', 'integer', 'gt:sp1_threshold'],
            'sp3_threshold' => ['required', 'integer', 'gt:sp2_threshold'],
            'mandatory_counseling_threshold' => ['nullable', 'integer', 'min:1'],
            'stages' => ['nullable', 'array'],
            'stages.*.level' => ['required_with:stages', 'string', 'max:24'],
            'stages.*.threshold' => ['required_with:stages', 'integer', 'min:1'],
            'stages.*.label' => ['nullable', 'string', 'max:80'],
        ]);

        $this->disciplineService->updateThresholds($validated);

        return back()->with('success', __('Pengaturan ambang poin dan tahapan Surat Peringatan berhasil disimpan.'));
    }

    /**
     * Manually issue a Warning Letter (SP) for a student by Waka Kesiswaan / BK.
     */
    public function issueWarningLetter(Request $request, Student $student): RedirectResponse
    {
        $allowedLevels = array_keys(WarningLetter::thresholds());
        $validated = $request->validate([
            'level' => ['required', 'string', 'in:'.implode(',', $allowedLevels)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->disciplineService->issueWarningLetter(
            $student,
            $request->user(),
            $validated['level'],
            $validated['notes'] ?? null
        );

        return back()->with('success', __('Surat Peringatan :level berhasil diterbitkan dan orang tua telah diberi tahu.', [
            'level' => $validated['level'],
        ]));
    }

    /**
     * Export an official Warning Letter (SP) PDF document.
     */
    public function exportWarningLetterPdf(WarningLetter $warningLetter)
    {
        $warningLetter->load(['student.user', 'student.classroom', 'issuer', 'parent']);

        $pdf = Pdf::loadView('points.warning-letter-pdf', [
            'letter' => $warningLetter,
            'student' => $warningLetter->student,
            'schoolName' => school_name(),
            'principalName' => signature_name(school_principal_name()),
        ])->setPaper('a4', 'portrait');

        $nis = $warningLetter->student?->nis ?: $warningLetter->student_id;

        return $pdf->stream("Surat_Peringatan_{$warningLetter->level}_{$nis}.pdf");
    }

    /**
     * Delete a point log entry.
     */
    public function destroy(StudentPointLog $log): RedirectResponse
    {
        $user = auth()->user();
        $canDeleteAny = RoleMiddleware::allowsUser($user, ['waka_kesiswaan', 'bk', 'admin', 'superadmin']);
        abort_unless($canDeleteAny || (int) $log->created_by === (int) $user->id, 403);

        $log->delete();

        return back()->with('success', 'Catatan poin berhasil dihapus.');
    }
}
