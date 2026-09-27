<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Extracurricular;
use App\Models\ExtracurricularAchievement;
use App\Models\ExtracurricularActivity;
use App\Models\ExtracurricularAttendance;
use App\Models\ExtracurricularGrade;
use App\Models\ExtracurricularMember;
use App\Models\Student;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExtracurricularController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $extracurriculars = Extracurricular::with('advisor')
            ->withCount('activeMembers')
            ->where('is_active', true)
            ->get();

        if ($user->role === 'student') {
            $student = $user->student;
            $myEkskulIds = ExtracurricularMember::where('student_id', $student->id)
                ->where('status', 'active')
                ->pluck('extracurricular_id')
                ->toArray();

            return view('extracurricular.index', compact('extracurriculars', 'myEkskulIds'));
        }

        return view('extracurricular.index', compact('extracurriculars'));
    }

    public function show(Extracurricular $extracurricular)
    {
        $extracurricular->load(['advisor', 'activeMembers.student.classroom', 'activeMembers.student.user', 'activities.creator']);

        // Cek Keikutsertaan Siswa
        $isMember = false;
        if (auth()->user()->role === 'student') {
            $isMember = ExtracurricularMember::where('extracurricular_id', $extracurricular->id)
                ->where('student_id', auth()->user()->student->id)
                ->where('status', 'active')
                ->exists();
        }

        return view('extracurricular.show', compact('extracurricular', 'isMember'));
    }

    public function register(Request $request, Extracurricular $extracurricular)
    {
        $user = auth()->user();
        if ($user->role !== 'student') {
            return back()->with('error', 'Hanya siswa yang dapat mendaftar ekstrakurikuler.');
        }

        $student = $user->student;

        // Cegah Duplikasi Keanggotaan Aktif
        $exists = ExtracurricularMember::where('extracurricular_id', $extracurricular->id)
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->exists();

        if ($exists) {
            return back()->with('error', 'Anda sudah terdaftar aktif di ekstrakurikuler ini.');
        }

        // Batasi Maksimal 2 Ekskul per Siswa
        $currentCount = ExtracurricularMember::where('student_id', $student->id)
            ->where('status', 'active')
            ->count();
        if ($currentCount >= 2) {
            return back()->with('error', 'Anda telah mencapai batas maksimal pendaftaran (maksimal 2 ekstrakurikuler).');
        }

        // Cek Ketersediaan Kuota Slot Anggota
        if (! is_null($extracurricular->max_members) && $extracurricular->activeMembers()->count() >= $extracurricular->max_members) {
            return back()->with('error', 'Pendaftaran gagal, kuota anggota ekstrakurikuler ini telah penuh.');
        }

        ExtracurricularMember::create([
            'extracurricular_id' => $extracurricular->id,
            'student_id' => $student->id,
            'role' => 'anggota',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        return redirect()->route('ekskul.show', $extracurricular->id)->with('success', 'Berhasil mendaftar di ekstrakurikuler '.$extracurricular->name);
    }

    public function manage()
    {
        $user = auth()->user();

        // Guru hanya bisa mengelola ekskul yang dibimbingnya
        if ($user->role === 'guru') {
            $extracurriculars = Extracurricular::with('advisor')->where('advisor_id', $user->id)->get();
        } else {
            // Admin/Waka Kesiswaan bisa melihat semua
            $extracurriculars = Extracurricular::with('advisor')->get();
        }

        return view('extracurricular.manage', compact('extracurriculars'));
    }

    public function storeAttendance(Request $request)
    {
        $request->validate([
            'extracurricular_id' => 'required|exists:extracurriculars,id',
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|exists:students,id',
            'attendance.*.status' => 'required|in:H,S,I,A',
            'attendance.*.notes' => 'nullable|string|max:255',
        ]);

        $ekskul = Extracurricular::findOrFail($request->extracurricular_id);
        $this->authorizeAdvisor($ekskul);

        try {
            DB::beginTransaction();

            foreach ($request->attendance as $att) {
                ExtracurricularAttendance::updateOrCreate(
                    [
                        'extracurricular_id' => $ekskul->id,
                        'date' => $request->date,
                        'student_id' => $att['student_id'],
                    ],
                    [
                        'status' => $att['status'],
                        'notes' => $att['notes'] ?? null,
                    ]
                );
            }

            DB::commit();

            return back()->with('success', 'Presensi latihan berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memproses presensi: '.$e->getMessage());
        }
    }

    public function storeActivity(Request $request)
    {
        $request->validate([
            'extracurricular_id' => 'required|exists:extracurriculars,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'activity_date' => 'required|date',
            'images' => 'nullable|array',
            'images.*' => 'required|image|mimes:jpg,jpeg,png|max:5120', // Maks 5MB per file
        ]);

        $ekskul = Extracurricular::findOrFail($request->extracurricular_id);
        $this->authorizeAdvisor($ekskul);

        $galleryPaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('uploads/extracurricular/activities', 'public');
                $galleryPaths[] = $path;
            }
        }

        ExtracurricularActivity::create([
            'extracurricular_id' => $ekskul->id,
            'title' => $request->title,
            'description' => $request->description,
            'activity_date' => $request->activity_date,
            'gallery_paths' => $galleryPaths,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Dokumentasi kegiatan berhasil ditambahkan.');
    }

    public function create()
    {
        $advisors = User::whereIn('role', ['guru', 'admin', 'superadmin'])->get();

        return view('extracurricular.create', compact('advisors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:extracurriculars,name',
            'description' => 'nullable|string',
            'advisor_id' => 'required|exists:users,id',
            'schedule_day' => 'required|string|max:50',
            'schedule_time' => 'required|string|max:50',
            'location' => 'required|string|max:255',
            'max_members' => 'nullable|integer|min:1',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('extracurricular/covers', 'public');
        }

        Extracurricular::create($validated);

        return redirect()->route('ekskul.manage')->with('success', 'Ekstrakurikuler berhasil dibuat.');
    }

    public function edit(Extracurricular $extracurricular)
    {
        $advisors = User::whereIn('role', ['guru', 'admin', 'superadmin'])->get();

        return view('extracurricular.edit', compact('extracurricular', 'advisors'));
    }

    public function update(Request $request, Extracurricular $extracurricular)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:extracurriculars,name,'.$extracurricular->id,
            'description' => 'nullable|string',
            'advisor_id' => 'required|exists:users,id',
            'schedule_day' => 'required|string|max:50',
            'schedule_time' => 'required|string|max:50',
            'location' => 'required|string|max:255',
            'max_members' => 'nullable|integer|min:1',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        if ($request->hasFile('cover_image')) {
            if ($extracurricular->cover_image) {
                Storage::disk('public')->delete($extracurricular->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('extracurricular/covers', 'public');
        }

        $extracurricular->update($validated);

        return redirect()->route('ekskul.manage')->with('success', 'Ekstrakurikuler berhasil diperbarui.');
    }

    public function destroy(Extracurricular $extracurricular)
    {
        if ($extracurricular->cover_image) {
            Storage::disk('public')->delete($extracurricular->cover_image);
        }
        $extracurricular->delete();

        return redirect()->route('ekskul.manage')->with('success', 'Ekstrakurikuler berhasil dihapus.');
    }

    public function unregister(ExtracurricularMember $member)
    {
        $member->update(['status' => 'inactive', 'left_at' => now()]);

        return back()->with('success', 'Siswa berhasil dikeluarkan dari anggota ekskul.');
    }

    public function achievements(Extracurricular $extracurricular)
    {
        $extracurricular->load('achievements');
        $students = Student::with('user')->where('status', 'active')->get();

        return view('extracurricular.achievements', compact('extracurricular', 'students'));
    }

    public function storeAchievement(Request $request, Extracurricular $extracurricular)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'level' => 'required|in:sekolah,kecamatan,kota,provinsi,nasional,internasional',
            'event_date' => 'required|date',
            'organizer' => 'required|string|max:255',
            'participants' => 'required|array',
            'participants.*' => 'exists:students,id',
            'certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
            'notes' => 'nullable|string',
        ]);

        $certPath = null;
        if ($request->hasFile('certificate')) {
            $certPath = $request->file('certificate')->store('extracurricular/certificates', 'public');
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('extracurricular/photos', 'public');
        }

        ExtracurricularAchievement::create([
            'extracurricular_id' => $extracurricular->id,
            'title' => $validated['title'],
            'level' => $validated['level'],
            'event_date' => $validated['event_date'],
            'organizer' => $validated['organizer'],
            'participants' => $validated['participants'],
            'certificate_path' => $certPath,
            'photo_path' => $photoPath,
            'notes' => $validated['notes'],
        ]);

        return back()->with('success', 'Prestasi baru berhasil ditambahkan.');
    }

    public function grading(Extracurricular $extracurricular)
    {
        $extracurricular->load('activeMembers.student.user');

        $academicYear = AcademicYear::where('is_active', true)->first();
        if (! $academicYear) {
            return back()->with('error', 'Tidak ada tahun ajaran aktif.');
        }

        $grades = ExtracurricularGrade::whereIn('extracurricular_member_id', $extracurricular->activeMembers->pluck('id'))
            ->where('academic_year_id', $academicYear->id)
            ->get()
            ->keyBy('extracurricular_member_id');

        return view('extracurricular.grading', compact('extracurricular', 'grades', 'academicYear'));
    }

    public function storeGrades(Request $request, Extracurricular $extracurricular)
    {
        $request->validate([
            'grades' => 'required|array',
            'grades.*.member_id' => 'required|exists:extracurricular_members,id',
            'grades.*.grade' => 'nullable|in:A,B,C,D',
            'grades.*.description' => 'nullable|string|max:1000',
        ]);

        $academicYear = AcademicYear::where('is_active', true)->first();
        if (! $academicYear) {
            return back()->with('error', 'Tidak ada tahun ajaran aktif.');
        }

        foreach ($request->grades as $item) {
            ExtracurricularGrade::updateOrCreate(
                [
                    'extracurricular_member_id' => $item['member_id'],
                    'academic_year_id' => $academicYear->id,
                ],
                [
                    'grade' => $item['grade'] ?? null,
                    'description' => $item['description'] ?? null,
                ]
            );
        }

        return back()->with('success', 'Nilai rapor anggota ekskul berhasil diperbarui.');
    }

    public function attendanceReport(Extracurricular $extracurricular)
    {
        $extracurricular->load('activeMembers.student.user');

        $attendanceSummary = [];
        foreach ($extracurricular->activeMembers as $member) {
            $attendances = ExtracurricularAttendance::where('extracurricular_id', $extracurricular->id)
                ->where('student_id', $member->student_id)
                ->get();

            $hadir = $attendances->where('status', 'H')->count();
            $sakit = $attendances->where('status', 'S')->count();
            $izin = $attendances->where('status', 'I')->count();
            $alfa = $attendances->where('status', 'A')->count();
            $total = $attendances->count();

            $rate = $total > 0 ? round(($hadir / $total) * 100, 1) : 0;

            $attendanceSummary[] = [
                'student' => $member->student,
                'hadir' => $hadir,
                'sakit' => $sakit,
                'izin' => $izin,
                'alfa' => $alfa,
                'rate' => $rate,
            ];
        }

        return view('extracurricular.attendance-report', compact('extracurricular', 'attendanceSummary'));
    }

    public function semesterReport(Extracurricular $extracurricular)
    {
        $extracurricular->load(['advisor', 'activeMembers.student.user', 'achievements', 'activities']);

        return view('extracurricular.semester-report', compact('extracurricular'));
    }

    public function exportPdf(Extracurricular $extracurricular)
    {
        $extracurricular->load(['advisor', 'activeMembers.student.user', 'achievements', 'activities']);

        $pdf = Pdf::loadView('extracurricular.pdf-report', compact('extracurricular'))->setPaper('a4', 'portrait');

        return $pdf->download("laporan_semester_{$extracurricular->name}.pdf");
    }

    public function schedule()
    {
        $extracurriculars = Extracurricular::with('advisor')->where('is_active', true)->get();

        return view('extracurricular.schedule', compact('extracurriculars'));
    }

    private function authorizeAdvisor(Extracurricular $ekskul)
    {
        $user = auth()->user();
        if ($user->isAdmin() || $user->role === 'waka_kesiswaan') {
            return;
        }
        if ($user->role === 'guru' && $ekskul->advisor_id === $user->id) {
            return;
        }
        abort(403, 'Aksi tidak diotorisasi. Anda bukan pembina ekskul ini.');
    }
}
