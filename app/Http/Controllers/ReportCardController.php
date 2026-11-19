<?php

namespace App\Http\Controllers;

use App\Http\Middleware\RoleMiddleware;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\ReportCard;
use App\Models\Student;
use App\Models\User;
use App\Services\ReportCardService;
use App\Support\Access\Roles;
use App\Support\Access\StudentDataAccess;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReportCardController extends Controller
{
    protected $reportCardService;

    public function __construct(ReportCardService $reportCardService)
    {
        $this->reportCardService = $reportCardService;
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', ReportCard::class);

        $classrooms = Classroom::all();
        $selectedClassroomId = $request->get('classroom_id');
        $activeYear = AcademicYear::where('is_active', true)->first();

        $students = collect([]);
        $reportCards = collect([]);

        if ($selectedClassroomId && $activeYear) {
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
        }

        return view('report-cards.index', compact('classrooms', 'selectedClassroomId', 'students', 'reportCards', 'activeYear'));
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

        // Ambil Data Rapor Kompilasi (Ledger)
        $reportCard = ReportCard::where('student_id', $student->id)
            ->where('academic_year_id', $activeYear->id)
            ->first();

        // Ambil Detail Nilai per Mapel
        $grades = Grade::with('subject')
            ->where('student_id', $student->id)
            ->where('academic_year_id', $activeYear->id)
            ->get();

        // Ambil Absensi
        $attendanceSum = Attendance::where('student_id', $student->id)
            ->where('academic_year_id', $activeYear->id)
            ->selectRaw("
                                    SUM(CASE WHEN status = 'H' THEN 1 ELSE 0 END) as hadir,
                                    SUM(CASE WHEN status = 'S' THEN 1 ELSE 0 END) as sakit,
                                    SUM(CASE WHEN status = 'I' THEN 1 ELSE 0 END) as izin,
                                    SUM(CASE WHEN status = 'A' THEN 1 ELSE 0 END) as alpa
                                ")
            ->first();

        // Load classroom with its homeroom teacher
        $student->load('classroom');

        // Get wali kelas from classroom (if relation exists)
        $waliKelas = $student->classroom?->teacher ?? null;

        // Get kepala sekolah (user with role kepala_sekolah)
        $kepalaSekolah = User::where('role', 'kepala_sekolah')->first();

        $data = [
            'student' => $student,
            'academicYear' => $activeYear,
            'reportCard' => $reportCard,
            'grades' => $grades,
            'attendance' => $attendanceSum,
            'waliKelas' => $waliKelas,
            'kepalaSekolah' => $kepalaSekolah,
        ];

        // Render PDF menggunakan facade \Barryvdh\DomPDF\Facade\Pdf
        $pdf = Pdf::loadView('report-cards.pdf', $data);

        // Setup Kertas A4 Portrait
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('Rapor_'.$student->nis.'_'.str_replace(' ', '_', $student->user->name ?? 'Siswa').'.pdf');
    }
}
