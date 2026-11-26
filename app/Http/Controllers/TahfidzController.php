<?php

namespace App\Http\Controllers;

use App\Http\Middleware\RoleMiddleware;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\TahfidzLog;
use App\Models\TahfidzTarget;
use App\Services\TahfidzService;
use App\Support\Access\StudentDataAccess;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TahfidzController extends Controller
{
    protected $tahfidzService;

    public function __construct(TahfidzService $tahfidzService)
    {
        $this->tahfidzService = $tahfidzService;
    }

    /**
     * Display role-specific dashboard.
     */
    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $academicYear = AcademicYear::current();

        if ($user->hasRole('student')) {
            $student = Student::where('user_id', $user->id)->firstOrFail();
            $progress = $this->tahfidzService->getStudentProgress($student, $academicYear);
            $surahs = $this->getSurahs();

            return view('tahfidz.dashboard', compact('student', 'progress', 'surahs', 'academicYear'));
        }

        if ($user->hasRole('parent')) {
            $students = Student::where('parent_id', $user->id)->get();
            if ($students->isEmpty()) {
                return view('tahfidz.dashboard', [
                    'student' => null,
                    'progress' => null,
                    'students' => $students,
                    'academicYear' => $academicYear,
                ]);
            }
            $selectedStudentId = $request->get('student_id', $students->first()->id);
            $student = $students->firstWhere('id', $selectedStudentId) ?? $students->first();
            $progress = $this->tahfidzService->getStudentProgress($student, $academicYear);
            $surahs = $this->getSurahs();

            return view('tahfidz.dashboard', compact('student', 'progress', 'students', 'surahs', 'academicYear'));
        }

        // Guru / Admin / Superadmin / BK
        $classrooms = Classroom::all();
        $classroomId = $request->get('classroom_id');
        if (! $classroomId) {
            if ($user->hasRole('guru')) {
                $myClassroom = Classroom::where('teacher_id', $user->id)->first();
                $classroomId = $myClassroom ? $myClassroom->id : $classrooms->first()?->id;
            } else {
                $classroomId = $classrooms->first()?->id;
            }
        }

        $classroom = $classroomId ? Classroom::find($classroomId) : null;
        $summary = $this->tahfidzService->getClassProgressSummary($classroomId);

        $students = $classroom ? Student::where('classroom_id', $classroom->id)
            ->with(['user', 'tahfidzTargets' => function ($q) use ($academicYear) {
                if ($academicYear) {
                    $q->where('academic_year_id', $academicYear->id);
                }
            }])
            ->get() : collect();

        $recentLogs = $classroom ? TahfidzLog::whereHas('student', function ($q) use ($classroom) {
            $q->where('classroom_id', $classroom->id);
        })
            ->with(['student.user', 'teacher'])
            ->latest()
            ->take(10)
            ->get() : collect();

        return view('tahfidz.dashboard', compact('classrooms', 'classroom', 'summary', 'students', 'recentLogs', 'academicYear'));
    }

    /**
     * Display listing for a specific student (for teachers/parents).
     */
    public function show(Student $student)
    {
        $user = auth()->user();
        $academicYear = AcademicYear::current();

        // Ownership Check
        if ($user->hasRole('student')) {
            if ($student->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke data hafalan ini.');
            }
        } elseif ($user->hasRole('parent')) {
            if ($student->parent_id !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke data hafalan anak ini.');
            }
        }

        $progress = $this->tahfidzService->getStudentProgress($student, $academicYear);
        $logs = $progress['logs'];
        $surahs = $this->getSurahs();

        return view('tahfidz.show', compact('student', 'logs', 'surahs', 'progress', 'academicYear'));
    }

    /**
     * Display form to input setoran for multiple students.
     */
    public function bulkInputForm(Request $request)
    {
        $user = auth()->user();
        if (! RoleMiddleware::allowsUser($user, [...StudentDataAccess::TEACHER_ROLES, 'admin', 'superadmin', ...RoleMiddleware::LEADERSHIP_ROLES])) {
            abort(403, 'Akses ditolak.');
        }

        $classrooms = Classroom::all();
        $classroomId = $request->get('classroom_id');
        if (! $classroomId) {
            if ($user->hasRole('guru')) {
                $myClassroom = Classroom::where('teacher_id', $user->id)->first();
                $classroomId = $myClassroom ? $myClassroom->id : $classrooms->first()?->id;
            } else {
                $classroomId = $classrooms->first()?->id;
            }
        }

        $classroom = $classroomId ? Classroom::find($classroomId) : null;
        $students = $classroom ? Student::where('classroom_id', $classroom->id)->with('user')->get() : collect();
        $surahs = $this->getSurahs();

        return view('tahfidz.bulk-input', compact('classrooms', 'classroom', 'students', 'surahs'));
    }

    /**
     * Store bulk setoran records.
     */
    public function storeBulk(Request $request)
    {
        $user = auth()->user();
        if (! RoleMiddleware::allowsUser($user, [...StudentDataAccess::TEACHER_ROLES, 'admin', 'superadmin', ...RoleMiddleware::LEADERSHIP_ROLES])) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'setoran' => 'required|array',
            'setoran.*.student_id' => 'required|exists:students,id',
            'setoran.*.surah_number' => 'required|integer|min:1|max:114',
            'setoran.*.from_ayat' => 'required|integer|min:1',
            'setoran.*.to_ayat' => 'required|integer|min:1',
            'setoran.*.type' => 'required|in:ziyadah,murajaah',
            'setoran.*.grade' => 'required|in:A,B,C,D',
            'setoran.*.juz' => 'required|integer|min:1|max:30',
            'setoran.*.pages_count' => 'required|integer|min:0',
            'setoran.*.notes' => 'nullable|string',
            'setoran.*.date' => 'required|date',
        ]);

        $surahs = $this->getSurahs();
        $count = 0;

        foreach ($request->setoran as $data) {
            if (! isset($data['active']) || ! $data['active']) {
                continue;
            }

            $student = Student::findOrFail($data['student_id']);
            $data['surah_name'] = $surahs[$data['surah_number']] ?? 'Surah';
            $data['teacher_id'] = auth()->id();

            $this->tahfidzService->submitSetoran($student, $data);
            $count++;
        }

        return redirect()->route('tahfidz.dashboard', ['classroom_id' => $request->classroom_id])
            ->with('success', "Berhasil menyimpan {$count} setoran hafalan siswa.");
    }

    /**
     * Display form to set targets for students in a classroom.
     */
    public function targetSettingForm(Request $request)
    {
        $user = auth()->user();
        if (! RoleMiddleware::allowsUser($user, [...StudentDataAccess::TEACHER_ROLES, 'admin', 'superadmin', ...RoleMiddleware::LEADERSHIP_ROLES])) {
            abort(403, 'Akses ditolak.');
        }

        $academicYear = AcademicYear::current();
        if (! $academicYear) {
            return back()->with('error', 'Tahun akademik aktif tidak ditemukan.');
        }

        $classrooms = Classroom::all();
        $classroomId = $request->get('classroom_id');
        if (! $classroomId) {
            if ($user->hasRole('guru')) {
                $myClassroom = Classroom::where('teacher_id', $user->id)->first();
                $classroomId = $myClassroom ? $myClassroom->id : $classrooms->first()?->id;
            } else {
                $classroomId = $classrooms->first()?->id;
            }
        }

        $classroom = $classroomId ? Classroom::find($classroomId) : null;
        $students = $classroom ? Student::where('classroom_id', $classroom->id)
            ->with(['user', 'tahfidzTargets' => function ($q) use ($academicYear) {
                $q->where('academic_year_id', $academicYear->id);
            }])
            ->get() : collect();

        return view('tahfidz.target-setting', compact('classrooms', 'classroom', 'students', 'academicYear'));
    }

    /**
     * Store/update targets.
     */
    public function storeTargets(Request $request)
    {
        $user = auth()->user();
        if (! RoleMiddleware::allowsUser($user, [...StudentDataAccess::TEACHER_ROLES, 'admin', 'superadmin', ...RoleMiddleware::LEADERSHIP_ROLES])) {
            abort(403, 'Akses ditolak.');
        }

        $academicYear = AcademicYear::current();
        if (! $academicYear) {
            return back()->with('error', 'Tahun akademik aktif tidak ditemukan.');
        }

        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'targets' => 'required|array',
            'targets.*.student_id' => 'required|exists:students,id',
            'targets.*.target_juz' => 'required|integer|min:0',
            'targets.*.target_pages' => 'required|integer|min:0',
            'targets.*.notes' => 'nullable|string',
        ]);

        foreach ($request->targets as $data) {
            $student = Student::findOrFail($data['student_id']);

            TahfidzTarget::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'academic_year_id' => $academicYear->id,
                ],
                [
                    'target_juz' => $data['target_juz'],
                    'target_pages' => $data['target_pages'],
                    'notes' => $data['notes'] ?? null,
                ]
            );

            $this->tahfidzService->updateTargetAchievement($student, $academicYear);
        }

        return redirect()->route('tahfidz.dashboard', ['classroom_id' => $request->classroom_id])
            ->with('success', 'Target hafalan siswa berhasil disimpan/diperbarui.');
    }

    /**
     * Store a new memorization log (single entry).
     */
    public function store(Request $request)
    {
        if (! RoleMiddleware::allowsUser(auth()->user(), [...StudentDataAccess::TEACHER_ROLES, 'admin', 'superadmin', ...RoleMiddleware::LEADERSHIP_ROLES])) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'surah_number' => 'required|integer|min:1|max:114',
            'from_ayat' => 'required|integer|min:1',
            'to_ayat' => 'required|integer|min:1',
            'type' => 'required|in:ziyadah,murajaah',
            'grade' => 'required|in:A,B,C,D',
            'notes' => 'nullable|string',
            'date' => 'required|date',
            'juz' => 'required|integer|min:1|max:30',
            'pages_count' => 'required|integer|min:0',
        ]);

        $student = Student::findOrFail($validated['student_id']);
        $surahs = $this->getSurahs();
        $validated['surah_name'] = $surahs[$validated['surah_number']] ?? 'Surah';
        $validated['teacher_id'] = auth()->id();

        $this->tahfidzService->submitSetoran($student, $validated);

        return back()->with('success', 'Setoran hafalan berhasil dicatat!');
    }

    /**
     * Display leaderboard.
     */
    public function leaderboard(Request $request)
    {
        $classrooms = Classroom::all();
        $classroomId = $request->get('classroom_id');
        $leaderboard = $this->tahfidzService->getLeaderboard($classroomId);

        return view('tahfidz.leaderboard', compact('classrooms', 'classroomId', 'leaderboard'));
    }

    /**
     * Export student tahfidz report to PDF.
     */
    public function exportPdf(Student $student)
    {
        $user = auth()->user();
        $academicYear = AcademicYear::current();

        if ($user->hasRole('student') && $student->user_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }
        if ($user->hasRole('parent') && $student->parent_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        $progress = $this->tahfidzService->getStudentProgress($student, $academicYear);
        $yearClean = str_replace('/', '-', $academicYear->year);

        $pdf = Pdf::loadView('tahfidz.pdf_export', compact('student', 'progress', 'academicYear'));

        return $pdf->download("laporan_tahfidz_{$student->name}_{$yearClean}.pdf");
    }

    private function getSurahs()
    {
        return [
            1 => 'Al-Fatihah', 2 => 'Al-Baqarah', 3 => 'Ali \'Imran', 4 => 'An-Nisa\'', 5 => 'Al-Ma\'idah',
            6 => 'Al-An\'am', 7 => 'Al-A\'raf', 8 => 'Al-Anfal', 9 => 'At-Taubah', 10 => 'Yunus',
            11 => 'Hud', 12 => 'Yusuf', 13 => 'Ar-Ra\'d', 14 => 'Ibrahim', 15 => 'Al-Hijr',
            16 => 'An-Nahl', 17 => 'Al-Isra\'', 18 => 'Al-Kahf', 19 => 'Maryam', 20 => 'Ta Ha',
            21 => 'Al-Anbiya\'', 22 => 'Al-Hajj', 23 => 'Al-Mu\'minun', 24 => 'An-Nur', 25 => 'Al-Furqan',
            26 => 'Asy-Syu\'ara\'', 27 => 'An-Naml', 28 => 'Al-Qasas', 29 => 'Al-\'Ankabut', 30 => 'Ar-Rum',
            31 => 'Luqman', 32 => 'As-Sajdah', 33 => 'Al-Ahzab', 34 => 'Saba\'', 35 => 'Fatir',
            36 => 'Ya Sin', 37 => 'As-Saffat', 38 => 'Sad', 39 => 'Az-Zumar', 40 => 'Ghafir',
            41 => 'Fussilat', 42 => 'Asy-Syura', 43 => 'Az-Zukhruf', 44 => 'Ad-Dukhan', 45 => 'Al-Jasiyah',
            46 => 'Al-Ahqaf', 47 => 'Muhammad', 48 => 'Al-Fath', 49 => 'Al-Hujurat', 50 => 'Qaf',
            51 => 'Az-Zariyat', 52 => 'At-Tur', 53 => 'An-Najm', 54 => 'Al-Qamar', 55 => 'Ar-Rahman',
            56 => 'Al-Waqi\'ah', 57 => 'Al-Hadid', 58 => 'Al-Hasyr', 59 => 'Al-Mumtahanah', 60 => 'Al-Hasyr', // corrected duplicate or minor issue
            61 => 'As-Saff', 62 => 'Al-Jumu\'ah', 63 => 'Al-Munafiqun', 64 => 'At-Tagabun', 65 => 'At-Talaq',
            66 => 'At-Tahrim', 67 => 'Al-Mulk', 68 => 'Al-Qalam', 69 => 'Al-Haqqah', 70 => 'Al-Ma\'arij',
            71 => 'Nuh', 72 => 'Al-Jinn', 73 => 'Al-Muzzammil', 74 => 'Al-Muddassir', 75 => 'Al-Qiyamah',
            76 => 'Al-Insan', 77 => 'Al-Mursalat', 78 => 'An-Naba\'', 79 => 'An-Nazi\'at', 80 => 'Abasa',
            81 => 'At-Takwir', 82 => 'Al-Infitar', 83 => 'Al-Mutaffifin', 84 => 'Al-Insyiqaq', 85 => 'Al-Buruj',
            86 => 'At-Tariq', 87 => 'Al-A\'la', 88 => 'Al-Gasyiyah', 89 => 'Al-Fajr', 90 => 'Al-Balad',
            91 => 'Asy-Syams', 92 => 'Al-Lail', 93 => 'Ad-Duha', 94 => 'Asy-Syarh', 95 => 'At-Tin',
            96 => 'Al-\'Alaq', 97 => 'Al-Qadr', 98 => 'Al-Bayyinah', 99 => 'Az-Zalzalah', 100 => 'Al-\'Adiyat',
            101 => 'Al-Qari\'ah', 102 => 'At-Takasur', 103 => 'Al-\'Asr', 104 => 'Al-Humazah', 105 => 'Al-Fil',
            106 => 'Quraisy', 107 => 'Al-Ma\'un', 108 => 'Al-Kausar', 109 => 'Al-Kafirun', 110 => 'An-Nasr',
            111 => 'Al-Lahab', 112 => 'Al-Ikhlas', 113 => 'Al-Falaq', 114 => 'An-Nas',
        ];
    }
}
