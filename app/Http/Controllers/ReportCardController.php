<?php

namespace App\Http\Controllers;

use App\Http\Middleware\RoleMiddleware;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ExtracurricularGrade;
use App\Models\Grade;
use App\Models\ReportCard;
use App\Models\ReportCardPdf;
use App\Models\ReportCardTemplate;
use App\Models\Student;
use App\Models\Subject;
use App\Services\Academic\ERaporService;
use App\Services\ReportCardService;
use App\Support\Access\Roles;
use App\Support\Access\StudentDataAccess;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ReportCardController extends Controller
{
    public function __construct(
        protected ReportCardService $reportCardService,
        protected ERaporService $eRaporService,
    ) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', ReportCard::class);

        $user = auth()->user();
        $classrooms = Classroom::orderBy('name')->get();
        $selectedClassroomId = $request->get('classroom_id');
        $selectedReportType = in_array($request->get('report_type'), ['pts', 'pas'], true)
            ? $request->get('report_type')
            : 'pas';
        $activeYear = AcademicYear::where('is_active', true)->first();
        $subjects = Subject::orderBy('name')->get();

        $templates = $activeYear
            ? ReportCardTemplate::where('academic_year_id', $activeYear->id)->latest()->get()
            : collect();

        $students = collect([]);
        $reportCards = collect([]);
        $completeness = null;
        $officialPdfs = collect([]);

        if ($selectedClassroomId && $activeYear) {
            $selectedClassroom = Classroom::find($selectedClassroomId);

            $students = Student::with('user')
                ->where('classroom_id', $selectedClassroomId)
                ->join('users', 'students.user_id', '=', 'users.id')
                ->orderBy('users.name')
                ->select('students.*')
                ->get();

            $reportCards = ReportCard::where('classroom_id', $selectedClassroomId)
                ->where('academic_year_id', $activeYear->id)
                ->get()
                ->keyBy('student_id');

            if ($selectedClassroom) {
                $completeness = $this->eRaporService->completeness($selectedClassroom, $activeYear, $selectedReportType);
            }

            if ($students->isNotEmpty()) {
                $officialPdfs = ReportCardPdf::whereIn('student_id', $students->pluck('user_id')->filter())
                    ->where('is_official', true)
                    ->latest('generated_at')
                    ->get()
                    ->groupBy('student_id')
                    ->map->first();
            }
        }

        return view('report-cards.index', compact(
            'classrooms',
            'selectedClassroomId',
            'selectedReportType',
            'students',
            'reportCards',
            'activeYear',
            'templates',
            'subjects',
            'completeness',
            'officialPdfs',
        ));
    }

    public function completeness(Request $request)
    {
        Gate::authorize('viewAny', ReportCard::class);

        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'report_type' => 'nullable|in:pts,pas',
        ]);

        $classroom = Classroom::findOrFail($request->integer('classroom_id'));
        $user = auth()->user();
        $isAcademicManager = $user && RoleMiddleware::allowsUser($user, ['admin', 'superadmin', 'waka_kurikulum', 'staf_tu', ...Roles::PRINCIPAL]);
        if (! $isAcademicManager && ! $classroom->isVisibleTo($user)) {
            abort(403, 'Anda tidak memiliki akses memantau kelengkapan rapor kelas ini.');
        }

        $activeYear = AcademicYear::where('is_active', true)->firstOrFail();
        $data = $this->eRaporService->completeness(
            $classroom,
            $activeYear,
            $request->get('report_type', 'pas')
        );

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Explicitly trigger report card calculation and sync.
     */
    public function sync(Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
        ]);

        $user = auth()->user();
        $classroom = Classroom::findOrFail($request->classroom_id);
        $isAcademicManager = $user && RoleMiddleware::allowsUser($user, ['admin', 'superadmin', 'waka_kurikulum', 'staf_tu']);
        $isClassroomTeacher = $user
            && RoleMiddleware::allowsUser($user, [...StudentDataAccess::TEACHER_ROLES, 'wali_kelas'])
            && $classroom->teacher_id === $user->id;

        if (! $isAcademicManager && ! $isClassroomTeacher) {
            abort(403, 'Hanya Admin, Waka Kurikulum, Staf TU, atau Wali Kelas bersangkutan yang dapat menyinkronkan rapor.');
        }

        $activeYear = AcademicYear::where('is_active', true)->first();
        if (! $activeYear) {
            return back()->with('error', 'Tahun Ajaran aktif tidak ditemukan.');
        }

        $this->reportCardService->syncClassroomReportCards($request->classroom_id, $activeYear->id);

        return back()->with('success', 'Data rapor (Ledger) berhasil dikalkulasi ulang.');
    }

    public function exportERapor(Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'report_type' => 'nullable|in:pts,pas',
            'template_id' => 'nullable|exists:report_card_templates,id',
        ]);

        $user = auth()->user();
        $classroom = Classroom::findOrFail($request->integer('classroom_id'));
        $isAcademicManager = $user && RoleMiddleware::allowsUser($user, ['admin', 'superadmin', 'waka_kurikulum', 'staf_tu', ...Roles::PRINCIPAL]);
        $isClassroomTeacher = $user
            && RoleMiddleware::allowsUser($user, [...StudentDataAccess::TEACHER_ROLES, 'wali_kelas'])
            && $classroom->teacher_id === $user->id;

        if (! $isAcademicManager && ! $isClassroomTeacher) {
            abort(403, 'Hanya Admin, Waka Kurikulum, Staf TU, atau Wali Kelas bersangkutan yang dapat mengekspor data e-Rapor.');
        }

        $activeYear = AcademicYear::where('is_active', true)->firstOrFail();
        $template = $request->filled('template_id') ? ReportCardTemplate::find($request->integer('template_id')) : null;
        $export = $this->eRaporService->exportERaporCsv(
            $classroom,
            $activeYear,
            $request->filled('subject_id') ? $request->integer('subject_id') : null,
            $request->get('report_type', 'pas'),
            $template
        );

        return response()->streamDownload(function () use ($export) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $export['headers']);
            foreach ($export['rows'] as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $export['filename'], [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function uploadOfficialPdfs(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:report_card_templates,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'files' => 'required|array|min:1',
            'files.*' => 'required|file|mimes:pdf,zip|max:51200',
        ]);

        $user = auth()->user();
        $classroom = Classroom::findOrFail($request->integer('classroom_id'));
        $isAcademicManager = $user && RoleMiddleware::allowsUser($user, ['admin', 'superadmin', 'waka_kurikulum', 'staf_tu']);
        $isClassroomTeacher = $user
            && RoleMiddleware::allowsUser($user, [...StudentDataAccess::TEACHER_ROLES, 'wali_kelas'])
            && $classroom->teacher_id === $user->id;

        if (! $isAcademicManager && ! $isClassroomTeacher) {
            abort(403, 'Hanya Admin, Waka Kurikulum, Staf TU, atau Wali Kelas bersangkutan yang dapat mengunggah PDF rapor resmi.');
        }

        $template = ReportCardTemplate::findOrFail($request->integer('template_id'));
        $summary = $this->eRaporService->uploadOfficialPdfs(
            $template,
            $classroom,
            $request->file('files', []),
            $user
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $summary,
            ]);
        }

        $message = sprintf(
            '%d PDF resmi berhasil dicocokkan dengan NISN. Tidak cocok: %d. Dilewati: %d.',
            count($summary['matched']),
            count($summary['unmatched']),
            count($summary['skipped'])
        );

        return back()->with('success', $message)->with('erapor_upload_summary', $summary);
    }

    public function publishBatch(Request $request)
    {
        $user = auth()->user();
        if (! $user || ! RoleMiddleware::allowsUser($user, ['admin', 'superadmin', 'waka_kurikulum', ...Roles::PRINCIPAL])) {
            abort(403, 'Hanya Waka Kurikulum, Kepala Sekolah, atau Admin yang dapat menerbitkan rapor.');
        }

        $validated = $request->validate([
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'grade_level' => ['required', Rule::in(['X', 'XI', 'XII'])],
            'report_type' => ['required', Rule::in(['pts', 'pas'])],
            'semester_number' => 'required|integer|min:1|max:6',
            'issued_date' => 'nullable|date',
        ]);

        $year = isset($validated['academic_year_id'])
            ? AcademicYear::findOrFail($validated['academic_year_id'])
            : AcademicYear::where('is_active', true)->firstOrFail();

        $published = $this->eRaporService->publishBatch(
            $year,
            $validated['grade_level'],
            $validated['report_type'],
            (int) $validated['semester_number'],
            $user,
            isset($validated['issued_date']) ? Carbon::parse($validated['issued_date']) : null
        );

        return back()->with('success', sprintf(
            '%d template rapor angkatan %s berhasil diterbitkan.',
            $published->count(),
            $validated['grade_level']
        ));
    }

    public function downloadFile(ReportCardPdf $pdf)
    {
        $user = auth()->user();
        abort_unless($user !== null, 401);

        $roles = $user->effectiveRoles();
        $isStaffReader = array_intersect($roles, [
            'admin',
            'superadmin',
            'waka_kurikulum',
            'staf_tu',
            'wali_kelas',
            ...Roles::PRINCIPAL,
            ...StudentDataAccess::TEACHER_ROLES,
        ]) !== [];

        if (! $isStaffReader) {
            StudentDataAccess::authorizeStudentUser($user, (int) $pdf->student_id);
        }

        $path = $pdf->file_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        $downloadName = $pdf->original_filename ?: ('Rapor_'.$pdf->student_id.'.pdf');

        return Storage::disk('local')->response($path, $downloadName, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function storeNotes(Request $request)
    {
        $request->validate([
            'report_cards' => 'required|array',
        ]);

        $user = auth()->user();
        $ids = array_keys($request->report_cards);
        $cards = ReportCard::with('classroom')->whereIn('id', $ids)->get();

        foreach ($cards as $card) {
            if (! $user || ! $user->can('update', $card)) {
                abort(403, 'Anda hanya dapat mengubah catatan wali kelas pada kelas binaan Anda sendiri.');
            }
        }

        if ($cards->isEmpty() && (! $user || ! RoleMiddleware::allowsUser($user, ['admin', 'superadmin', 'waka_kurikulum', 'staf_tu', ...StudentDataAccess::TEACHER_ROLES, 'wali_kelas']))) {
            abort(403, 'Anda tidak memiliki akses untuk menyimpan catatan wali kelas.');
        }

        foreach ($cards as $card) {
            $card->update([
                'homeroom_notes' => $request->report_cards[$card->id]['homeroom_notes'] ?? null,
            ]);
        }

        return back()->with('success', 'Catatan Wali Kelas berhasil disimpan untuk seluruh siswa.');
    }

    public function printPdf(Request $request, Student $student)
    {
        $user = auth()->user();
        $roles = $user ? $user->effectiveRoles() : [];

        // Security check using canonical role groups & effective roles
        $isStaffReader = $user && array_intersect($roles, [
            'admin',
            'superadmin',
            'waka_kurikulum',
            'staf_tu',
            'wali_kelas',
            ...Roles::PRINCIPAL,
            ...StudentDataAccess::TEACHER_ROLES,
        ]) !== [];

        if (! $isStaffReader) {
            $isOwnStudent = array_intersect($roles, StudentDataAccess::STUDENT_ROLES) !== [] && $student->user_id === $user?->id;
            $isOwnChild = array_intersect($roles, StudentDataAccess::PARENT_ROLES) !== [] && $student->parent_id === $user?->id;

            if (! $isOwnStudent && ! $isOwnChild) {
                abort(403, 'Akses ditolak. Anda tidak memiliki hak akses untuk mengunduh rapor siswa ini.');
            }
        }

        $activeYear = AcademicYear::where('is_active', true)->first();

        if (! $activeYear) {
            abort(404, 'Tahun Ajaran Aktif tidak ditemukan.');
        }

        $reportType = in_array($request->get('report_type'), ['pts', 'pas'], true)
            ? $request->get('report_type')
            : 'pas';

        $student->load('classroom.teacher', 'user');
        $gradeLevel = (string) ($student->classroom?->grade_level ?? 'X');
        $template = ReportCardTemplate::where('academic_year_id', $activeYear->id)
            ->where('report_type', $reportType)
            ->latest()
            ->first();

        $reportCard = ReportCard::where('student_id', $student->id)
            ->where('academic_year_id', $activeYear->id)
            ->first();

        $grades = Grade::with('subject')
            ->where('student_id', $student->id)
            ->where('academic_year_id', $activeYear->id)
            ->when($reportType === 'pts', fn ($q) => $q->where('type', 'pts'))
            ->get();

        $attendanceSum = (object) $this->eRaporService->calculateDayLevelAttendance($student, $activeYear);

        $issuedDate = $template?->issued_date ?? Carbon::now();
        $officials = $student->classroom
            ? $this->eRaporService->resolveOfficials($student->classroom, $issuedDate)
            : [
                'principal' => null,
                'principal_name' => null,
                'principal_nip' => null,
                'wali_kelas' => null,
            ];

        $kepalaSekolah = $officials['principal']
            ?? ($officials['principal_name'] ? (object) ['name' => $officials['principal_name']] : null);

        $tpDrafts = [];
        foreach ($grades as $grade) {
            if ($grade->subject) {
                $tpDrafts[$grade->subject->id] = $this->eRaporService->generateTpCapaian($student, $grade->subject, $activeYear)['draft'];
            }
        }

        $extracurriculars = $reportType === 'pas'
            ? ExtracurricularGrade::with('member.extracurricular')
                ->where('academic_year_id', $activeYear->id)
                ->whereHas('member', fn ($q) => $q->where('student_id', $student->id))
                ->get()
            : collect();

        $data = [
            'student' => $student,
            'academicYear' => $activeYear,
            'reportCard' => $reportCard,
            'grades' => $grades,
            'attendance' => $attendanceSum,
            'waliKelas' => $officials['wali_kelas'],
            'kepalaSekolah' => $kepalaSekolah,
            'principalNip' => $officials['principal_nip'],
            'template' => $template,
            'reportType' => $reportType,
            'phase' => $this->eRaporService->inferPhase($gradeLevel),
            'semesterNumber' => $template?->semester_number ?? $this->eRaporService->inferSemesterNumber($gradeLevel, (string) ($activeYear->semester ?? 'ganjil')),
            'tpDrafts' => $tpDrafts,
            'extracurriculars' => $extracurriculars,
            'isPreview' => true,
        ];

        $pdf = Pdf::loadView('report-cards.pdf', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('Pratinjau_Rapor_'.$student->nis.'_'.str_replace(' ', '_', $student->user->name ?? 'Siswa').'.pdf');
    }
}
