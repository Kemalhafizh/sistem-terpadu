<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\MutabaahLog;
use App\Models\MutabaahWeeklySummary;
use App\Models\Student;
use App\Services\MutabaahService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class MutabaahController extends Controller
{
    protected $mutabaahService;

    public function __construct(MutabaahService $mutabaahService)
    {
        $this->mutabaahService = $mutabaahService;
    }

    /**
     * Display the mutabaah calendar and daily overview.
     */
    public function index(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $student = $this->getStudentFromRequest($request);

        if (!$student) {
            return back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $carbonDate = Carbon::parse($date);
        $startOfMonth = $carbonDate->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth = $carbonDate->copy()->endOfMonth()->format('Y-m-d');

        // Fetch monthly logs for calendar view
        $monthlyLogs = MutabaahLog::where('student_id', $student->id)
            ->whereNull('mutabaah_item_id')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy(function ($log) {
                return $log->date->format('Y-m-d');
            });

        $log = $monthlyLogs->get($date);

        return view('mutabaah.index', compact('student', 'monthlyLogs', 'log', 'date'));
    }

    /**
     * Store/update a daily detailed mutabaah log.
     */
    public function storeDailyLog(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'date' => 'required|date',
            'sholat_subuh' => 'required|integer|in:0,1,2',
            'sholat_dzuhur' => 'required|integer|in:0,1,2',
            'sholat_ashar' => 'required|integer|in:0,1,2',
            'sholat_maghrib' => 'required|integer|in:0,1,2',
            'sholat_isya' => 'required|integer|in:0,1,2',
            'sholat_dhuha' => 'required|integer|in:0,1,2',
            'sholat_tahajud' => 'required|integer|in:0,1,2',
            'tadarus_pages' => 'required|integer|min:0',
            'puasa_sunnah' => 'boolean',
            'sedekah' => 'boolean',
            'dzikir_pagi' => 'boolean',
            'dzikir_sore' => 'boolean',
            'catatan_harian' => 'nullable|string',
        ]);

        $user = auth()->user();
        $student = Student::find($validated['student_id']);

        if ($user->role === 'student' && $student->user_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        // Convert boolean checks
        $validated['puasa_sunnah'] = $request->boolean('puasa_sunnah');
        $validated['sedekah'] = $request->boolean('sedekah');
        $validated['dzikir_pagi'] = $request->boolean('dzikir_pagi');
        $validated['dzikir_sore'] = $request->boolean('dzikir_sore');

        $this->mutabaahService->submitDailyLog($student, $validated, $validated['date']);

        return back()->with('success', 'Amalan ibadah harian berhasil disimpan!');
    }

    /**
     * Show detailed form for daily input.
     */
    public function inputHarian(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $student = $this->getStudentFromRequest($request);

        if (!$student) {
            return back()->with('error', 'Siswa tidak ditemukan.');
        }

        if (auth()->user()->role !== 'student') {
            abort(403, 'Hanya siswa yang dapat menginput amalan harian.');
        }

        $log = MutabaahLog::where('student_id', $student->id)
            ->whereNull('mutabaah_item_id')
            ->where('date', $date)
            ->first();

        return view('mutabaah.input-harian', compact('student', 'log', 'date'));
    }

    /**
     * Display weekly summaries for a student.
     */
    public function rekapMingguan(Request $request)
    {
        $student = $this->getStudentFromRequest($request);

        if (!$student) {
            return back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $summaries = MutabaahWeeklySummary::where('student_id', $student->id)
            ->orderBy('week_start', 'desc')
            ->paginate(10);

        return view('mutabaah.rekap-mingguan', compact('student', 'summaries'));
    }

    /**
     * Display weekly summaries for all students in a classroom (for Wali Kelas).
     */
    public function rekapKelas(Request $request, $classroomId)
    {
        $user = auth()->user();
        $classroom = Classroom::findOrFail($classroomId);

        // Authorization check: Homeroom teacher or Admin
        if (!in_array($user->role, ['admin', 'superadmin', 'kepala_sekolah']) && $classroom->teacher_id !== $user->id) {
            abort(403, 'Anda bukan wali kelas dari kelas ini.');
        }

        $weekStart = $request->get('week_start', now()->startOfWeek()->format('Y-m-d'));
        $weekEnd = Carbon::parse($weekStart)->endOfWeek()->format('Y-m-d');

        $students = Student::where('classroom_id', $classroomId)->with('user')->get();

        // Get summaries for this week
        $summaries = MutabaahWeeklySummary::whereIn('student_id', $students->pluck('id'))
            ->where('week_start', $weekStart)
            ->get()
            ->keyBy('student_id');

        return view('mutabaah.rekap-kelas', compact('classroom', 'students', 'summaries', 'weekStart', 'weekEnd'));
    }

    /**
     * Homeroom teacher writes comments on a student's weekly summary.
     */
    public function storeWaliKelasComment(Request $request, $summaryId)
    {
        $request->validate([
            'wali_kelas_comment' => 'required|string',
        ]);

        $summary = MutabaahWeeklySummary::findOrFail($summaryId);
        $classroom = $summary->student->classroom;

        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'superadmin', 'kepala_sekolah']) && $classroom->teacher_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        $summary->update([
            'wali_kelas_comment' => $request->wali_kelas_comment,
        ]);

        return back()->with('success', 'Catatan pembinaan wali kelas berhasil disimpan!');
    }

    /**
     * Parent acknowledges child's weekly summary.
     */
    public function parentAcknowledge($summaryId)
    {
        $user = auth()->user();
        $summary = MutabaahWeeklySummary::findOrFail($summaryId);

        if ($user->role !== 'parent' || $summary->student->parent_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        $summary->update([
            'parent_acknowledged' => true,
        ]);

        return back()->with('success', 'Laporan mingguan telah dikonfirmasi dan ditandatangani.');
    }

    /**
     * Parent portal detailed view of child's mutabaah.
     */
    public function parentView(Request $request, $studentId)
    {
        $user = auth()->user();
        $student = Student::findOrFail($studentId);

        if ($user->role !== 'parent' || $student->parent_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        $summaries = MutabaahWeeklySummary::where('student_id', $student->id)
            ->orderBy('week_start', 'desc')
            ->take(5)
            ->get();

        return view('mutabaah.parent-view', compact('student', 'summaries'));
    }

    /**
     * Classroom overall statistics for mutabaah.
     */
    public function statistik(Request $request)
    {
        $user = auth()->user();
        
        $classroomId = $request->get('classroom_id');
        $classrooms = [];

        if (in_array($user->role, ['admin', 'superadmin', 'kepala_sekolah', 'waka_kesiswaan'])) {
            $classrooms = Classroom::all();
            if (!$classroomId) {
                $classroomId = $classrooms->first()?->id;
            }
        } elseif ($user->role === 'guru') {
            $classrooms = Classroom::where('teacher_id', $user->id)->get();
            if (!$classroomId) {
                $classroomId = $classrooms->first()?->id;
            }
        } else {
            // Students/Parents redirect to student individual stats page
            $student = $this->getStudentFromRequest($request);
            if (!$student) abort(404);
            
            $scoreTrend = MutabaahLog::where('student_id', $student->id)
                ->whereNull('mutabaah_item_id')
                ->orderBy('date')
                ->take(15)
                ->get(['date', 'score']);

            return view('mutabaah.statistik-individu', compact('student', 'scoreTrend'));
        }

        if (!$classroomId) {
            return back()->with('error', 'Kelas tidak tersedia.');
        }

        $classroom = Classroom::find($classroomId);
        $weekStart = $request->get('week_start', now()->startOfWeek()->format('Y-m-d'));

        $students = Student::where('classroom_id', $classroomId)->get();
        
        $summaries = MutabaahWeeklySummary::whereIn('student_id', $students->pluck('id'))
            ->where('week_start', $weekStart)
            ->get();

        // Calculate analytics
        $avgClassScore = $summaries->avg('total_score') ?? 0;
        $gradeDistribution = $summaries->groupBy('grade')->map->count();

        // Top 5 performers
        $topPerformers = $summaries->sortByDesc('total_score')->take(5);

        return view('mutabaah.statistik', compact(
            'classrooms',
            'classroom',
            'weekStart',
            'avgClassScore',
            'gradeDistribution',
            'topPerformers',
            'summaries'
        ));
    }

    /**
     * Export weekly classroom summary to PDF.
     */
    public function exportPdf(Request $request, $classroomId)
    {
        $classroom = Classroom::findOrFail($classroomId);
        $weekStart = $request->get('week_start', now()->startOfWeek()->format('Y-m-d'));
        $weekEnd = Carbon::parse($weekStart)->endOfWeek()->format('Y-m-d');

        $students = Student::where('classroom_id', $classroomId)->with('user')->get();
        $summaries = MutabaahWeeklySummary::whereIn('student_id', $students->pluck('id'))
            ->where('week_start', $weekStart)
            ->get()
            ->keyBy('student_id');

        $pdf = Pdf::loadView('mutabaah.pdf_export', compact('classroom', 'students', 'summaries', 'weekStart', 'weekEnd'));
        return $pdf->download("rekap_mutabaah_{$classroom->name}_{$weekStart}.pdf");
    }

    /**
     * Verify all logs for a date.
     */
    public function verify(Request $request, Student $student)
    {
        $date = $request->date;
        $user = auth()->user();

        if ($user->role === 'parent' && $student->parent_id !== $user->id) {
            abort(403);
        }

        MutabaahLog::where('student_id', $student->id)
            ->where('date', $date)
            ->update(['verified_by' => $user->id]);

        return back()->with('success', "Amalan harian anak pada tanggal {$date} telah diverifikasi.");
    }

    /**
     * Legacy toggle for backward compatibility (dynamic items / mobile app / dashboard widget).
     */
    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'item_id' => 'required|exists:mutabaah_items,id',
            'date' => 'required|date',
            'status' => 'required|boolean',
        ]);

        $log = MutabaahLog::updateOrCreate(
            [
                'student_id' => $validated['student_id'],
                'mutabaah_item_id' => $validated['item_id'],
                'date' => $validated['date'],
            ],
            [
                'is_completed' => $validated['status'],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Status amalan berhasil diperbarui.',
        ]);
    }

    /**
     * Helper to load student from requests.
     */
    private function getStudentFromRequest(Request $request)
    {
        $user = auth()->user();
        if ($user->role === 'student') {
            return Student::where('user_id', $user->id)->first();
        }
        if ($request->has('student_id')) {
            $student = Student::find($request->student_id);
            if ($user->role === 'parent' && $student?->parent_id !== $user->id) {
                abort(403);
            }
            return $student;
        }
        if ($user->role === 'parent') {
            return Student::where('parent_id', $user->id)->first();
        }
        return Student::first();
    }
}
