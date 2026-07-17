<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\UksMedicine;
use App\Models\UksVisit;
use App\Models\StudentHealthRecord;
use App\Models\HealthScreening;
use App\Models\HealthScreeningResult;
use App\Models\UksMedicineRestock;
use App\Services\UksService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UksController extends Controller
{
    use AuthorizesRequests;

    protected $uksService;

    public function __construct(UksService $uksService)
    {
        $this->uksService = $uksService;
    }

    /**
     * Dashboard UKS: Statistik, Obat Menipis, Grafik ApexCharts
     */
    public function dashboard()
    {
        $this->authorize('manageUks', UksVisit::class);

        $todayVisitsCount = UksVisit::whereDate('visit_time', now()->toDateString())->count();
        $criticalCount = UksVisit::whereDate('visit_time', now()->toDateString())->where('severity', 'berat')->count();
        $lowStockCount = UksMedicine::lowStock()->count();
        $expiringSoonCount = UksMedicine::expiringSoon(30)->count();

        $recentVisits = UksVisit::with(['student.user', 'student.classroom', 'handler'])
            ->latest('visit_time')
            ->limit(5)
            ->get();

        $lowStockMedicines = UksMedicine::lowStock()->limit(5)->get();

        // Data Grafik Kunjungan 7 Hari Terakhir
        $chartData = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->toDateString();

            return [
                'date' => now()->subDays($daysAgo)->translatedFormat('d M'),
                'count' => UksVisit::whereDate('visit_time', $date)->count(),
                'berat' => UksVisit::whereDate('visit_time', $date)->where('severity', 'berat')->count(),
            ];
        });

        return view('uks.dashboard', compact(
            'todayVisitsCount', 'criticalCount', 'lowStockCount', 'expiringSoonCount',
            'recentVisits', 'lowStockMedicines', 'chartData'
        ));
    }

    /**
     * Kunjungan Index (Daftar Kunjungan UKS)
     */
    public function visitsIndex(Request $request)
    {
        $this->authorize('manageUks', UksVisit::class);

        $query = UksVisit::with(['student.user', 'student.classroom', 'handler']);

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }
        if ($request->filled('disposition')) {
            $query->where('disposition', $request->disposition);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('student.user', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                })->orWhere('complaint', 'like', "%{$search}%")
                    ->orWhere('diagnosis', 'like', "%{$search}%");
            });
        }

        $visits = $query->latest('visit_time')->paginate(15);

        return view('uks.visits.index', compact('visits'));
    }

    /**
     * Tampilkan form catat kunjungan
     */
    public function visitsCreate()
    {
        $this->authorize('manageUks', UksVisit::class);

        $students = Student::with(['user', 'classroom'])->where('status', 'active')->get()->sortBy('user.name');
        $medicines = UksMedicine::where('stock', '>', 0)->get();

        return view('uks.visits.create', compact('students', 'medicines'));
    }

    /**
     * Simpan kunjungan baru
     */
    public function visitsStore(Request $request)
    {
        $this->authorize('manageUks', UksVisit::class);

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'visit_time' => 'required|date_format:Y-m-d\TH:i',
            'leave_time' => 'nullable|date_format:Y-m-d\TH:i|after_or_equal:visit_time',
            'complaint' => 'required|string|max:255',
            'diagnosis' => 'nullable|string',
            'treatment' => 'nullable|string',
            'severity' => 'required|in:ringan,sedang,berat',
            'disposition' => 'required|in:kembali_kelas,istirahat_uks,pulang,rujuk_rs',
            'notes' => 'nullable|string',
            'medicines' => 'nullable|array',
            'medicines.*.id' => 'nullable|exists:uks_medicines,id',
            'medicines.*.quantity' => 'nullable|integer|min:1',
            'blood_type' => 'nullable|string|max:3',
            'allergies' => 'nullable|string',
        ]);

        // Simpan alergi / golongan darah baru jika ada perubahan
        $student = Student::findOrFail($request->student_id);
        if ($request->filled('blood_type') || $request->filled('allergies')) {
            $student->update([
                'blood_type' => $request->blood_type,
                'allergies' => $request->allergies,
            ]);
        }

        $medicinesInput = $request->input('medicines', []);

        $this->uksService->recordVisit($validated, $medicinesInput);

        return redirect()->route('uks.visits.index')->with('success', 'Kunjungan siswa berhasil dicatatkan!');
    }

    /**
     * Tampilkan detail kunjungan siswa
     */
    public function visitsShow(UksVisit $visit)
    {
        $this->authorize('manageUks', UksVisit::class);

        $visit->load(['student.user', 'student.classroom', 'handler', 'medicines']);

        return view('uks.visits.show', compact('visit'));
    }

    /**
     * Hapus/Batalkan kunjungan UKS (Kembalikan stok)
     */
    public function visitsDestroy(UksVisit $visit)
    {
        $this->authorize('manageUks', UksVisit::class);

        $this->uksService->deleteVisit($visit);

        return redirect()->route('uks.visits.index')->with('success', 'Catatan kunjungan UKS berhasil dibatalkan dan sediaan obat telah disesuaikan kembali.');
    }

    /**
     * Kelola Obat UKS (Index & Store & Update & Destroy)
     */
    public function medicinesIndex()
    {
        $this->authorize('manageUks', UksVisit::class);

        $medicines = UksMedicine::orderBy('name')->paginate(15);

        return view('uks.medicines.index', compact('medicines'));
    }

    public function medicinesStore(Request $request)
    {
        $this->authorize('manageUks', UksVisit::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:P3K,Vitamin,Obat Umum',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:1',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        UksMedicine::create($validated);

        return redirect()->route('uks.medicines.index')->with('success', 'Obat baru berhasil ditambahkan!');
    }

    public function medicinesUpdate(Request $request, UksMedicine $medicine)
    {
        $this->authorize('manageUks', UksVisit::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:P3K,Vitamin,Obat Umum',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:1',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $medicine->update($validated);

        return redirect()->route('uks.medicines.index')->with('success', 'Detail obat berhasil diperbarui!');
    }

    public function medicinesDestroy(UksMedicine $medicine)
    {
        $this->authorize('manageUks', UksVisit::class);

        if ($medicine->visits()->count() > 0) {
            return redirect()->route('uks.medicines.index')->with('error', 'Obat tidak dapat dihapus karena sudah memiliki riwayat penggunaan kunjungan.');
        }

        $medicine->delete();

        return redirect()->route('uks.medicines.index')->with('success', 'Obat berhasil dihapus dari inventaris!');
    }

    /**
     * Riwayat Kunjungan Spesifik per Siswa (Timeline Medis)
     */
    public function studentHistory(Student $student)
    {
        // Policy check agar parent hanya bisa akses anaknya sendiri
        $this->authorize('viewStudentHistory', [UksVisit::class, $student]);

        $student->load(['user', 'classroom']);
        $visits = UksVisit::with(['handler', 'medicines'])
            ->where('student_id', $student->id)
            ->latest('visit_time')
            ->get();

        return view('uks.student-history', compact('student', 'visits'));
    }

    /**
     * Cetak Laporan Bulanan UKS ke PDF
     */
    public function exportMonthlyReport(Request $request)
    {
        $this->authorize('manageUks', UksVisit::class);

        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $visits = UksVisit::with(['student.user', 'student.classroom', 'handler'])
            ->whereMonth('visit_time', $month)
            ->whereYear('visit_time', $year)
            ->get();

        $severitySummary = [
            'ringan' => $visits->where('severity', 'ringan')->count(),
            'sedang' => $visits->where('severity', 'sedang')->count(),
            'berat' => $visits->where('severity', 'berat')->count(),
        ];

        $dispositionSummary = [
            'kembali_kelas' => $visits->where('disposition', 'kembali_kelas')->count(),
            'istirahat_uks' => $visits->where('disposition', 'istirahat_uks')->count(),
            'pulang' => $visits->where('disposition', 'pulang')->count(),
            'rujuk_rs' => $visits->where('disposition', 'rujuk_rs')->count(),
        ];

        $topComplaints = $visits->groupBy('complaint')
            ->map(fn ($group) => $group->count())
            ->sortDesc()
            ->take(5);

        // Ambil pemakaian obat bulanan
        $medicineUsage = DB::table('uks_visit_medicines')
            ->join('uks_medicines', 'uks_visit_medicines.uks_medicine_id', '=', 'uks_medicines.id')
            ->join('uks_visits', 'uks_visit_medicines.uks_visit_id', '=', 'uks_visits.id')
            ->whereMonth('uks_visits.visit_time', $month)
            ->whereYear('uks_visits.visit_time', $year)
            ->select('uks_medicines.name', DB::raw('SUM(uks_visit_medicines.quantity) as total_used'))
            ->groupBy('uks_medicines.name')
            ->orderByDesc('total_used')
            ->get();

        $pdf = Pdf::loadView('uks.pdf.monthly_report', compact(
            'visits', 'severitySummary', 'dispositionSummary', 'topComplaints',
            'medicineUsage', 'month', 'year'
        ));

        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("Laporan_Bulanan_UKS_{$year}_{$month}.pdf");
    }

    /**
     * Tampilkan form edit kunjungan UKS
     */
    public function editVisit($id)
    {
        $this->authorize('manageUks', UksVisit::class);
        $visit = UksVisit::with('medicines')->findOrFail($id);
        $students = Student::with(['user', 'classroom'])->where('status', 'active')->get()->sortBy('user.name');
        $medicines = UksMedicine::all();
        return view('uks.visits.edit', compact('visit', 'students', 'medicines'));
    }

    /**
     * Update data kunjungan UKS
     */
    public function updateVisit(Request $request, $id)
    {
        $this->authorize('manageUks', UksVisit::class);
        $visit = UksVisit::findOrFail($id);

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'visit_time' => 'required|date_format:Y-m-d\TH:i',
            'leave_time' => 'nullable|date_format:Y-m-d\TH:i|after_or_equal:visit_time',
            'complaint' => 'required|string|max:255',
            'diagnosis' => 'nullable|string',
            'treatment' => 'nullable|string',
            'severity' => 'required|in:ringan,sedang,berat',
            'disposition' => 'required|in:kembali_kelas,istirahat_uks,pulang,rujuk_rs',
            'notes' => 'nullable|string',
            'medicines' => 'nullable|array',
            'medicines.*.id' => 'nullable|exists:uks_medicines,id',
            'medicines.*.quantity' => 'nullable|integer|min:1',
        ]);

        $medicinesInput = $request->input('medicines', []);
        $this->uksService->updateVisit($visit, $validated, $medicinesInput);

        return redirect()->route('uks.visits.index')->with('success', 'Catatan kunjungan UKS berhasil diperbarui!');
    }

    /**
     * Kelola rekam medis siswa (index)
     */
    public function healthRecords()
    {
        $this->authorize('manageUks', UksVisit::class);
        
        // Ensure all active students have a record initialized
        $activeStudentIds = Student::where('status', 'active')->pluck('id');
        foreach ($activeStudentIds as $studentId) {
            StudentHealthRecord::firstOrCreate(
                ['student_id' => $studentId],
                [
                    'blood_type' => 'unknown',
                    'allergies' => [],
                    'chronic_conditions' => [],
                    'medications' => [],
                ]
            );
        }

        $records = StudentHealthRecord::with('student.user', 'student.classroom')->paginate(15);
        return view('uks.health-records.index', compact('records'));
    }

    /**
     * Detail rekam medis individual
     */
    public function showHealthRecord($studentId)
    {
        $this->authorize('manageUks', UksVisit::class);
        $student = Student::with(['user', 'classroom'])->findOrFail($studentId);
        $record = StudentHealthRecord::firstOrCreate(
            ['student_id' => $student->id],
            [
                'blood_type' => 'unknown',
                'allergies' => [],
                'chronic_conditions' => [],
                'medications' => [],
            ]
        );
        return view('uks.health-records.show', compact('student', 'record'));
    }

    /**
     * Update rekam medis siswa
     */
    public function updateHealthRecord(Request $request)
    {
        $this->authorize('manageUks', UksVisit::class);
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'blood_type' => 'required|in:A,B,AB,O,unknown',
            'allergies' => 'nullable|string',
            'chronic_conditions' => 'nullable|string',
            'medications' => 'nullable|string',
            'medical_notes' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:255',
            'insurance_number' => 'nullable|string|max:255',
            'height_cm' => 'nullable|numeric|min:30|max:250',
            'weight_kg' => 'nullable|numeric|min:5|max:200',
            'vision_status' => 'required|in:normal,minus,plus,silinder,unknown',
        ]);

        $record = StudentHealthRecord::where('student_id', $request->student_id)->firstOrFail();

        $allergies = $request->allergies ? array_map('trim', explode(',', $request->allergies)) : [];
        $chronic = $request->chronic_conditions ? array_map('trim', explode(',', $request->chronic_conditions)) : [];
        $meds = $request->medications ? array_map('trim', explode(',', $request->medications)) : [];

        $record->update([
            'blood_type' => $request->blood_type,
            'allergies' => $allergies,
            'chronic_conditions' => $chronic,
            'medications' => $meds,
            'medical_notes' => $request->medical_notes,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'insurance_number' => $request->insurance_number,
            'height_cm' => $request->height_cm,
            'weight_kg' => $request->weight_kg,
            'vision_status' => $request->vision_status,
        ]);

        return redirect()->route('uks.health-records.index')->with('success', 'Rekam medis siswa berhasil diperbarui!');
    }

    /**
     * Daftar health screening
     */
    public function screeningIndex()
    {
        $this->authorize('manageUks', UksVisit::class);
        $screenings = HealthScreening::with('academicYear')->latest()->paginate(15);
        $academicYears = \App\Models\AcademicYear::all();
        $classrooms = \App\Models\Classroom::all();
        return view('uks.screenings.index', compact('screenings', 'academicYears', 'classrooms'));
    }

    /**
     * Buat screening baru
     */
    public function storeScreening(Request $request)
    {
        $this->authorize('manageUks', UksVisit::class);
        $request->validate([
            'title' => 'required|string|max:255',
            'screening_date' => 'required|date',
            'academic_year_id' => 'required|exists:academic_years,id',
            'target_classrooms' => 'required|array',
        ]);

        HealthScreening::create([
            'title' => $request->title,
            'screening_date' => $request->screening_date,
            'academic_year_id' => $request->academic_year_id,
            'target_classrooms' => $request->target_classrooms,
            'status' => 'planned',
        ]);

        return redirect()->route('uks.screenings.index')->with('success', 'Jadwal screening kesehatan baru berhasil dibuat!');
    }

    /**
     * Form input hasil screening
     */
    public function inputScreeningResults($id)
    {
        $this->authorize('manageUks', UksVisit::class);
        $screening = HealthScreening::findOrFail($id);
        
        $students = Student::with('user')
            ->whereIn('classroom_id', $screening->target_classrooms)
            ->where('status', 'active')
            ->get()
            ->sortBy('user.name');

        $existingResults = HealthScreeningResult::where('screening_id', $screening->id)
            ->get()
            ->keyBy('student_id');

        return view('uks.screenings.input-results', compact('screening', 'students', 'existingResults'));
    }

    /**
     * Simpan hasil screening batch
     */
    public function storeScreeningResults(Request $request)
    {
        $this->authorize('manageUks', UksVisit::class);
        $request->validate([
            'screening_id' => 'required|exists:health_screenings,id',
            'results' => 'required|array',
            'results.*.student_id' => 'required|exists:students,id',
            'results.*.height_cm' => 'nullable|numeric|min:30|max:250',
            'results.*.weight_kg' => 'nullable|numeric|min:5|max:200',
            'results.*.vision_left' => 'nullable|string',
            'results.*.vision_right' => 'nullable|string',
            'results.*.dental_status' => 'nullable|string',
            'results.*.findings' => 'nullable|string',
            'results.*.recommendations' => 'nullable|string',
        ]);

        $screening = HealthScreening::findOrFail($request->screening_id);

        DB::transaction(function () use ($request, $screening) {
            foreach ($request->results as $res) {
                HealthScreeningResult::updateOrCreate(
                    [
                        'screening_id' => $screening->id,
                        'student_id' => $res['student_id'],
                    ],
                    [
                        'height_cm' => $res['height_cm'] ?? null,
                        'weight_kg' => $res['weight_kg'] ?? null,
                        'vision_left' => $res['vision_left'] ?? null,
                        'vision_right' => $res['vision_right'] ?? null,
                        'dental_status' => $res['dental_status'] ?? null,
                        'findings' => $res['findings'] ?? null,
                        'recommendations' => $res['recommendations'] ?? null,
                    ]
                );

                // Update global student health record
                $record = StudentHealthRecord::firstOrCreate(['student_id' => $res['student_id']]);
                $record->update([
                    'height_cm' => $res['height_cm'] ?? $record->height_cm,
                    'weight_kg' => $res['weight_kg'] ?? $record->weight_kg,
                ]);
            }

            $screening->update(['status' => 'completed']);
        });

        return redirect()->route('uks.screenings.index')->with('success', 'Hasil screening kesehatan berhasil disimpan!');
    }

    /**
     * Laporan hasil screening
     */
    public function screeningReport($id)
    {
        $this->authorize('manageUks', UksVisit::class);
        $screening = HealthScreening::with('academicYear')->findOrFail($id);
        $results = HealthScreeningResult::with('student.user', 'student.classroom')
            ->where('screening_id', $screening->id)
            ->get();

        $totalScreened = $results->count();
        $averageHeight = $results->avg('height_cm') ? round($results->avg('height_cm'), 1) : 0;
        $averageWeight = $results->avg('weight_kg') ? round($results->avg('weight_kg'), 1) : 0;

        return view('uks.screenings.report', compact('screening', 'results', 'totalScreened', 'averageHeight', 'averageWeight'));
    }

    /**
     * Export laporan screening ke PDF
     */
    public function exportScreeningPdf($id)
    {
        $this->authorize('manageUks', UksVisit::class);
        $screening = HealthScreening::with('academicYear')->findOrFail($id);
        $results = HealthScreeningResult::with('student.user', 'student.classroom')
            ->where('screening_id', $screening->id)
            ->get();

        $pdf = Pdf::loadView('uks.pdf.screening_report', compact('screening', 'results'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download("Laporan_Screening_{$screening->id}.pdf");
    }

    /**
     * Log pemberian obat
     */
    public function medicineDispenseLog()
    {
        $this->authorize('manageUks', UksVisit::class);
        
        $logs = DB::table('uks_visit_medicines')
            ->join('uks_medicines', 'uks_visit_medicines.uks_medicine_id', '=', 'uks_medicines.id')
            ->join('uks_visits', 'uks_visit_medicines.uks_visit_id', '=', 'uks_visits.id')
            ->join('students', 'uks_visits.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->select(
                'uks_visit_medicines.quantity',
                'uks_visit_medicines.created_at',
                'uks_medicines.name as medicine_name',
                'users.name as student_name'
            )
            ->latest('uks_visit_medicines.created_at')
            ->paginate(15);

        return view('uks.medicines.dispense-log', compact('logs'));
    }

    /**
     * Log restock obat
     */
    public function restockLog()
    {
        $this->authorize('manageUks', UksVisit::class);
        $logs = UksMedicineRestock::with(['medicine', 'restocker'])->latest()->paginate(15);
        $medicines = UksMedicine::all();
        return view('uks.medicines.restock-log', compact('logs', 'medicines'));
    }

    /**
     * Simpan restock obat baru
     */
    public function storeRestock(Request $request)
    {
        $this->authorize('manageUks', UksVisit::class);
        $request->validate([
            'uks_medicine_id' => 'required|exists:uks_medicines,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            UksMedicineRestock::create([
                'uks_medicine_id' => $request->uks_medicine_id,
                'quantity' => $request->quantity,
                'restocked_by' => auth()->id(),
                'notes' => $request->notes,
            ]);

            $medicine = UksMedicine::findOrFail($request->uks_medicine_id);
            $medicine->increment('stock', $request->quantity);
        });

        return redirect()->route('uks.medicines.restock-log')->with('success', 'Stok obat berhasil ditambahkan!');
    }
}
