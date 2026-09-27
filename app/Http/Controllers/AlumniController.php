<?php

namespace App\Http\Controllers;

use App\Models\AlumniData;
use App\Models\Classroom;
use App\Models\Student;
use App\Services\AlumniEnhancedService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlumniController extends Controller
{
    protected AlumniEnhancedService $alumniEnhancedService;

    public function __construct(AlumniEnhancedService $alumniEnhancedService)
    {
        $this->alumniEnhancedService = $alumniEnhancedService;
    }

    /**
     * Admin/Humas table directory.
     */
    public function index(Request $request)
    {
        $query = AlumniData::with(['student.user', 'student.classroom']);

        if ($request->filled('year')) {
            $query->where('graduation_year', $request->year);
        }

        if ($request->filled('activity')) {
            $query->where('current_activity', $request->activity);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $alumni = $query->latest()->paginate(15);
        $graduationYears = AlumniData::select('graduation_year')->distinct()->orderBy('graduation_year', 'desc')->pluck('graduation_year');

        return view('alumni.index', compact('alumni', 'graduationYears'));
    }

    /**
     * Interactive card grid directory for students/teachers/public.
     */
    public function directory(Request $request)
    {
        $query = AlumniData::with(['student.user', 'student.classroom']);

        if ($request->filled('year')) {
            $query->where('graduation_year', $request->year);
        }

        if ($request->filled('activity')) {
            $query->where('current_activity', $request->activity);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('student', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                })->orWhere('institution_name', 'like', "%{$search}%")
                    ->orWhere('current_position', 'like', "%{$search}%");
            });
        }

        $alumni = $query->latest()->paginate(12);
        $graduationYears = AlumniData::select('graduation_year')->distinct()->orderBy('graduation_year', 'desc')->pluck('graduation_year');

        return view('alumni.directory', compact('alumni', 'graduationYears'));
    }

    /**
     * Alumni geographical mapping view.
     */
    public function alumniNetworkMap()
    {
        $locations = $this->alumniEnhancedService->getAlumniGeography();

        return view('alumni.network-map', compact('locations'));
    }

    /**
     * Details profile view.
     */
    public function show(AlumniData $alumni)
    {
        $alumni->load(['student.user', 'student.classroom', 'tracerResponse']);

        return view('alumni.show', compact('alumni'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        // Only active students that are not already registered as alumni
        $students = Student::where('status', 'active')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('alumni_data')
                    ->whereColumn('alumni_data.student_id', 'students.id');
            })
            ->with(['user', 'classroom'])
            ->orderBy('nis')
            ->get();

        return view('alumni.create', compact('students'));
    }

    /**
     * Store new manual alumni.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'graduation_year' => 'required|integer|min:2000|max:'.(date('Y') + 1),
            'current_activity' => 'required|in:study,work,entrepreneur,unemployed,other',
            'institution_name' => 'nullable|string|max:255',
            'current_position' => 'nullable|string|max:255',
            'contact_info' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'linkedin_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            AlumniData::create($validated);
            Student::find($validated['student_id'])->update(['status' => 'alumni']);
        });

        return redirect()->route('alumni.index')->with('success', 'Data alumni berhasil ditambahkan secara manual.');
    }

    /**
     * Show edit form.
     */
    public function edit(AlumniData $alumni)
    {
        $alumni->load('student.user');

        return view('alumni.edit', compact('alumni'));
    }

    /**
     * Update alumni data.
     */
    public function update(Request $request, AlumniData $alumni)
    {
        $validated = $request->validate([
            'graduation_year' => 'required|integer|min:2000|max:'.(date('Y') + 1),
            'current_activity' => 'required|in:study,work,entrepreneur,unemployed,other',
            'institution_name' => 'nullable|string|max:255',
            'current_position' => 'nullable|string|max:255',
            'contact_info' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'linkedin_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
        ]);

        $alumni->update($validated);

        return redirect()->route('alumni.index')->with('success', 'Data profil alumni berhasil diperbarui.');
    }

    /**
     * Delete alumni record.
     */
    public function destroy(AlumniData $alumni)
    {
        DB::transaction(function () use ($alumni) {
            // Restore student status back to active
            Student::find($alumni->student_id)->update(['status' => 'active']);
            $alumni->delete();
        });

        return redirect()->route('alumni.index')->with('success', 'Data alumni berhasil dihapus, status siswa dikembalikan ke Aktif.');
    }

    /**
     * Bulk graduate form.
     */
    public function showBulkGraduateForm(Request $request)
    {
        $classrooms = Classroom::orderBy('name')->get();
        $selectedClassroomId = $request->classroom_id;
        $students = collect();

        if ($selectedClassroomId) {
            $students = Student::where('classroom_id', $selectedClassroomId)
                ->where('status', 'active')
                ->with('user')
                ->get();
        }

        return view('alumni.bulk-graduate', compact('classrooms', 'students', 'selectedClassroomId'));
    }

    /**
     * Process bulk graduation.
     */
    public function bulkGraduate(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'graduation_year' => 'required|integer|min:2000|max:'.(date('Y') + 1),
        ]);

        $count = $this->alumniEnhancedService->bulkGraduateStudents($request->student_ids, $request->graduation_year);

        return redirect()->route('alumni.index')->with('success', "Sebanyak {$count} siswa kelas XII berhasil diluluskan menjadi alumni.");
    }

    /**
     * Waka Humas dashboard.
     */
    public function dashboard()
    {
        $stats = $this->alumniEnhancedService->getAlumniDistribution();
        $geography = $this->alumniEnhancedService->getAlumniGeography();

        return view('alumni.dashboard', compact('stats', 'geography'));
    }

    /**
     * Export all alumni data to CSV format.
     */
    public function exportExcel()
    {
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=data_alumni_sma_isa1.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Nama Alumni', 'NISN', 'Tahun Kelulusan', 'Aktivitas Saat Ini', 'Nama Instansi', 'Posisi/Jurusan', 'Email', 'No Telepon', 'Kota', 'Provinsi']);

            $alumni = AlumniData::with(['student.user'])->get();
            foreach ($alumni as $al) {
                fputcsv($file, [
                    $al->student->name ?? '-',
                    $al->student->nisn ?? '-',
                    $al->graduation_year,
                    $al->current_activity,
                    $al->institution_name ?? '-',
                    $al->current_position ?? '-',
                    $al->email ?? '-',
                    $al->phone_number ?? '-',
                    $al->city ?? '-',
                    $al->province ?? '-',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export angkatan directory book to PDF.
     */
    public function exportPdf($year)
    {
        $alumni = $this->alumniEnhancedService->generateAlumniBookData($year);

        if ($alumni->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data alumni untuk angkatan tahun tersebut.');
        }

        $pdf = Pdf::loadView('alumni.pdf_book', compact('alumni', 'year'))->setPaper('a4', 'portrait');

        return $pdf->download("buku_alumni_angkatan_{$year}.pdf");
    }
}
