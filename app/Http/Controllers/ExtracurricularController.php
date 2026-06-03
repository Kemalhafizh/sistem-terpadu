<?php

namespace App\Http\Controllers;

use App\Models\Extracurricular;
use App\Models\ExtracurricularMember;
use App\Models\ExtracurricularAttendance;
use App\Models\ExtracurricularActivity;
use App\Models\Student;
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
        $extracurricular->load(['advisor', 'activeMembers.student.classroom', 'activities']);
        
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
        if (!is_null($extracurricular->max_members) && $extracurricular->activeMembers()->count() >= $extracurricular->max_members) {
            return back()->with('error', 'Pendaftaran gagal, kuota anggota ekstrakurikuler ini telah penuh.');
        }

        ExtracurricularMember::create([
            'extracurricular_id' => $extracurricular->id,
            'student_id' => $student->id,
            'role' => 'anggota',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        return redirect()->route('ekskul.show', $extracurricular->id)->with('success', 'Berhasil mendaftar di ekstrakurikuler ' . $extracurricular->name);
    }

    public function manage()
    {
        $user = auth()->user();
        
        // Guru hanya bisa mengelola ekskul yang dibimbingnya
        if ($user->role === 'guru') {
            $extracurriculars = Extracurricular::where('advisor_id', $user->id)->get();
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
            return back()->with('error', 'Gagal memproses presensi: ' . $e->getMessage());
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
