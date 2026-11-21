<?php

namespace App\Http\Controllers;

use App\Http\Middleware\RoleMiddleware;
use App\Models\CounselingSession;
use App\Models\Student;
use App\Models\StudentPointLog;
use App\Models\User;
use App\Services\CounselingService;
use App\Support\Access\Roles;
use App\Support\Access\StudentDataAccess;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CounselingController extends Controller
{
    use AuthorizesRequests;

    protected $counselingService;

    public function __construct(CounselingService $counselingService)
    {
        $this->counselingService = $counselingService;
    }

    /**
     * Dashboard Bimbingan & Konseling (BK Online)
     */
    public function dashboard()
    {
        $this->authorize('manage', CounselingSession::class);

        // Angka Ringkasan Statistik
        $todaySessionsCount = CounselingSession::whereDate('scheduled_at', now()->toDateString())->count();
        $requestedQueueCount = CounselingSession::where('status', 'requested')->count();
        $upcomingSessionsCount = CounselingSession::upcoming()->count();
        $needsFollowUpCount = CounselingSession::needsFollowUp()->count();

        // Daftar Antrian Permintaan Sesi Konseling
        $requestedSessions = CounselingSession::with(['student.user', 'student.classroom', 'requester'])
            ->where('status', 'requested')
            ->latest('created_at')
            ->limit(5)
            ->get();

        // Daftar Agenda Sesi Hari Ini
        $todaySessions = CounselingSession::with(['student.user', 'student.classroom', 'counselor'])
            ->whereDate('scheduled_at', now()->toDateString())
            ->orderBy('scheduled_at')
            ->get();

        // Agenda Mendatang
        $upcomingSessions = CounselingSession::with(['student.user', 'student.classroom', 'counselor'])
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>', now())
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get();

        // Sesi yang Memerlukan Tindak Lanjut Terdekat
        $followUpSessions = CounselingSession::with(['student.user', 'student.classroom', 'counselor'])
            ->needsFollowUp()
            ->limit(5)
            ->get();

        // Statistik Kategori Masalah (Pribadi vs Akademik vs Karir vs Sosial)
        $categoryStats = CounselingSession::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        // Lengkapi kategori yang kosong dengan default 0
        $categoryStats = array_merge([
            'pribadi' => 0,
            'akademik' => 0,
            'sosial' => 0,
            'karir' => 0,
        ], $categoryStats);

        // Guru BK terdaftar untuk pilihan jadwalkan
        $counselors = User::where('role', 'bk')->get();

        return view('counseling.dashboard', compact(
            'todaySessionsCount', 'requestedQueueCount', 'upcomingSessionsCount', 'needsFollowUpCount',
            'requestedSessions', 'todaySessions', 'upcomingSessions', 'followUpSessions', 'categoryStats', 'counselors'
        ));
    }

    /**
     * Daftar Semua Sesi Konseling (Filter & Search)
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', CounselingSession::class);

        $query = CounselingSession::with(['student.user', 'student.classroom', 'counselor', 'requester']);

        $user = auth()->user();

        // Pembatasan hak akses berbasis role
        if (RoleMiddleware::allowsUser($user, StudentDataAccess::STUDENT_ROLES)) {
            $student = $user->student;
            if (! $student) {
                abort(403, 'Anda tidak terdaftar sebagai Siswa.');
            }
            $query->where('student_id', $student->id);
        } elseif (RoleMiddleware::allowsUser($user, StudentDataAccess::PARENT_ROLES)) {
            $query->whereHas('student', function ($q) use ($user) {
                $q->where('parent_id', $user->id);
            });
        }

        // Filter Filter Parameter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Pencarian (Hanya untuk BK / Management)
        if ($request->filled('search') && RoleMiddleware::allowsUser($user, ['bk', 'waka_kesiswaan', ...Roles::PRINCIPAL, 'admin', 'superadmin'])) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('student.user', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                })->orWhere('reason', 'like', "%{$search}%");
            });
        }

        $sessions = $query->latest('scheduled_at')->paginate(15);

        // Guru BK terdaftar untuk modal penjadwalan
        $counselors = User::where('role', 'bk')->get();

        return view('counseling.index', compact('sessions', 'counselors'));
    }

    /**
     * Form permohonan sesi konseling
     */
    public function requestForm()
    {
        $this->authorize('create', CounselingSession::class);

        $user = auth()->user();
        $students = collect();

        if (RoleMiddleware::allowsUser($user, StudentDataAccess::STUDENT_ROLES)) {
            $students = collect([$user->student]);
        } elseif (RoleMiddleware::allowsUser($user, StudentDataAccess::PARENT_ROLES)) {
            $students = Student::with(['user', 'classroom'])->where('parent_id', $user->id)->get();
        } elseif (RoleMiddleware::allowsUser($user, ['bk', 'admin', 'superadmin'])) {
            $students = Student::with(['user', 'classroom'])->where('status', 'active')->get()->sortBy('user.name');
        }

        return view('counseling.request', compact('students'));
    }

    /**
     * Kirim/Simpan Permohonan Sesi Konseling Baru
     */
    public function requestSession(Request $request)
    {
        $this->authorize('create', CounselingSession::class);

        $rules = [
            'student_id' => 'required|exists:students,id',
            'type' => 'required|in:individual,group,home_visit,conference',
            'category' => 'required|in:akademik,pribadi,sosial,karir',
            'reason' => 'required|string|max:500',
            'scheduled_at' => 'nullable|date_format:Y-m-d\TH:i|after:now',
        ];

        // Jika Guru BK yang membuat, scheduled_at wajib diisi
        if (RoleMiddleware::allowsUser(auth()->user(), ['bk', 'admin', 'superadmin'])) {
            $rules['scheduled_at'] = 'required|date_format:Y-m-d\TH:i';
        }

        $validated = $request->validate($rules);

        // Pastikan siswa yang dipilih valid jika user adalah siswa atau orang tua
        $user = auth()->user();
        if (RoleMiddleware::allowsUser($user, StudentDataAccess::STUDENT_ROLES) && $user->student?->id != $validated['student_id']) {
            abort(403, 'Aksi tidak diizinkan.');
        }
        if (RoleMiddleware::allowsUser($user, StudentDataAccess::PARENT_ROLES)) {
            $allowedStudentIds = Student::where('parent_id', $user->id)->pluck('id')->toArray();
            if (! in_array($validated['student_id'], $allowedStudentIds)) {
                abort(403, 'Aksi tidak diizinkan.');
            }
        }

        // Set default scheduled_at jika kosong (untuk siswa/ortu)
        if (empty($validated['scheduled_at'])) {
            $validated['scheduled_at'] = now()->addDays(2)->setHour(9)->setMinute(0); // Default Lusa Jam 9 Pagi
        }

        $this->counselingService->requestSession($validated);

        return redirect()->route('counseling.index')->with('success', 'Permohonan bimbingan konseling berhasil dikirimkan!');
    }

    /**
     * Konfirmasi jadwal & konselor oleh BK
     */
    public function schedule(Request $request, CounselingSession $session)
    {
        $this->authorize('manage', CounselingSession::class);

        $validated = $request->validate([
            'counselor_id' => 'required|exists:users,id',
            'scheduled_at' => 'required|date_format:Y-m-d\TH:i',
        ]);

        // Verifikasi counselor adalah Guru BK
        $counselor = User::findOrFail($validated['counselor_id']);
        if ($counselor->role !== 'bk') {
            return back()->with('error', 'Konselor harus seorang guru BK.');
        }

        $this->counselingService->scheduleSession($session, $validated['counselor_id'], $validated['scheduled_at']);

        return redirect()->route('counseling.index')->with('success', 'Jadwal sesi konseling berhasil dikonfirmasi dan ditetapkan!');
    }

    /**
     * Mulai pelaksanaan sesi konseling (status in_progress)
     */
    public function startSession(CounselingSession $session)
    {
        $this->authorize('manage', CounselingSession::class);

        if ($session->status !== 'scheduled') {
            return back()->with('error', 'Hanya sesi terjadwal yang dapat dimulai.');
        }

        $this->counselingService->startSession($session);

        return redirect()->route('counseling.session', $session)->with('success', 'Sesi konseling telah dimulai secara interaktif!');
    }

    /**
     * Menampilkan halaman pencatatan rekam konseling interaktif
     */
    public function session(CounselingSession $session)
    {
        $this->authorize('view', $session);

        if (! in_array($session->status, ['in_progress', 'completed'])) {
            return redirect()->route('counseling.index')->with('error', 'Sesi ini belum berjalan atau tidak valid.');
        }

        $session->load(['student.user', 'student.classroom', 'counselor']);

        // Riwayat sesi bimbingan siswa terdahulu untuk referensi
        $pastSessions = CounselingSession::where('student_id', $session->student_id)
            ->where('id', '!=', $session->id)
            ->where('status', 'completed')
            ->latest('scheduled_at')
            ->get();

        return view('counseling.session', compact('session', 'pastSessions'));
    }

    /**
     * Menyelesaikan sesi konseling & menyimpan rekam catatan (terenkripsi)
     */
    public function completeSession(Request $request, CounselingSession $session)
    {
        $this->authorize('manage', CounselingSession::class);

        $validated = $request->validate([
            'session_notes' => 'required|string|min:10',
            'action_plan' => 'required|string|min:5',
            'follow_up_notes' => 'nullable|string',
            'follow_up_date' => 'nullable|date|after:today',
        ]);

        $this->counselingService->completeSession($session, $validated);

        return redirect()->route('counseling.index')->with('success', 'Sesi konseling berhasil diselesaikan dan rekam catatan dienkripsi secara aman!');
    }

    /**
     * Membatalkan sesi konseling
     */
    public function cancelSession(Request $request, CounselingSession $session)
    {
        // Siswa/Parent hanya boleh membatalkan jika statusnya masih requested/scheduled
        $user = auth()->user();
        if (RoleMiddleware::allowsUser($user, [...StudentDataAccess::STUDENT_ROLES, ...StudentDataAccess::PARENT_ROLES])) {
            if ($session->status !== 'requested' && $session->status !== 'scheduled') {
                return back()->with('error', 'Sesi tidak dapat dibatalkan pada tahap ini.');
            }
            if (RoleMiddleware::allowsUser($user, StudentDataAccess::STUDENT_ROLES) && $session->student_id !== $user->student?->id) {
                abort(403);
            }
            if (RoleMiddleware::allowsUser($user, StudentDataAccess::PARENT_ROLES) && $session->student?->parent_id !== $user->id) {
                abort(403);
            }
        } else {
            $this->authorize('manage', CounselingSession::class);
        }

        $validated = $request->validate([
            'cancel_reason' => 'required|string|max:255',
        ]);

        $this->counselingService->cancelSession($session, $validated['cancel_reason']);

        return redirect()->route('counseling.index')->with('success', 'Sesi konseling telah dibatalkan.');
    }

    /**
     * Riwayat Konseling & Poin Masalah Siswa (Profil Holistik)
     */
    public function studentProfile(Student $student)
    {
        $this->authorize('viewStudentProfile', [CounselingSession::class, $student]);

        $student->load(['user', 'classroom']);

        // Riwayat Konseling Siswa
        $sessions = CounselingSession::with(['counselor', 'requester'])
            ->where('student_id', $student->id)
            ->latest('scheduled_at')
            ->get();

        // Riwayat Pelanggaran Poin Kedisiplinan Siswa
        $pointLogs = StudentPointLog::with(['category', 'creator'])
            ->where('student_id', $student->id)
            ->latest('created_at')
            ->get();

        // Total akumulasi poin pelanggaran
        $totalPoints = $student->total_points;

        return view('counseling.student-profile', compact('student', 'sessions', 'pointLogs', 'totalPoints'));
    }

    /**
     * Cetak PDF Berita Acara Konseling Resmi (A4)
     */
    public function exportSessionReport(CounselingSession $session)
    {
        $this->authorize('downloadReport', [CounselingSession::class, $session]);

        $session->load(['student.user', 'student.classroom', 'counselor']);

        if ($session->status !== 'completed') {
            return back()->with('error', 'Laporan berita acara hanya dapat dicetak untuk sesi yang telah selesai.');
        }

        $pdf = Pdf::loadView('counseling.pdf.session_report', compact('session'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("Berita_Acara_Konseling_{$session->student->user->name}_{$session->scheduled_at->format('Ymd')}.pdf");
    }
}
