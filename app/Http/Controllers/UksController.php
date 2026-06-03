<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\UksMedicine;
use App\Models\UksVisit;
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
}
