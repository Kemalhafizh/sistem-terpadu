<?php

namespace App\Http\Controllers\HRD;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Models\Classroom;
use App\Models\EmployeeDocument;
use App\Models\EmployeeTraining;
use App\Models\PerformanceEvaluation;
use App\Models\EmployeeAttendance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Employee::with('user');

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('employee_number', 'like', "%{$search}%");
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('employment_status', $request->status);
        }

        $employees = $query->latest()->paginate(10);

        return view('hrd.employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all users who have staff/teacher roles but don't have an employee record yet
        $availableUsers = User::whereIn('role', [
            'guru', 'staf_tu', 'admin', 'superadmin', 'kepala_sekolah', 'waka_kurikulum', 'waka_kesiswaan', 'waka_sarpras', 'waka_humas', 'bk',
        ])->doesntHave('employee')->get();

        return view('hrd.employees.create', compact('availableUsers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id|unique:employees,user_id',
            'employee_number' => 'required|string|unique:employees,employee_number',
            'position' => 'required|string|max:255',
            'employment_status' => 'required|in:gty,gtt,staf_tetap,honorer',
            'hire_date' => 'required|date',
            'base_salary' => 'nullable|numeric',
            'hourly_rate' => 'nullable|numeric',
            'bank_name' => 'nullable|string',
            'bank_account' => 'nullable|string',
        ]);

        Employee::create($validated);

        return redirect()->route('hrd.employees.index')->with('success', 'Data pegawai berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        $employee->load(['user', 'attendances' => function ($q) {
            $q->latest('date')->take(10);
        }, 'leaveRequests' => function ($q) {
            $q->latest()->take(5);
        }, 'documents', 'trainings']);

        return view('hrd.employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        return view('hrd.employees.edit', compact('employee'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'employee_number' => 'required|string|unique:employees,employee_number,'.$employee->id,
            'position' => 'required|string|max:255',
            'employment_status' => 'required|in:gty,gtt,staf_tetap,honorer',
            'hire_date' => 'required|date',
            'base_salary' => 'nullable|numeric',
            'hourly_rate' => 'nullable|numeric',
            'bank_name' => 'nullable|string',
            'bank_account' => 'nullable|string',
        ]);

        $employee->update($validated);

        return redirect()->route('hrd.employees.index')->with('success', 'Data pegawai berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('hrd.employees.index')->with('success', 'Data pegawai berhasil dihapus.');
    }

    /**
     * HRD Stats Dashboard.
     */
    public function dashboard()
    {
        $totalEmployees = Employee::count();
        $totalGuru = Employee::where('position', 'like', '%Guru%')->orWhere('position', 'like', '%Pengajar%')->count();
        $totalStaf = Employee::where('position', 'not like', '%Guru%')->where('position', 'not like', '%Pengajar%')->count();
        
        $statusDistribution = [
            'gty' => Employee::where('employment_status', 'gty')->count(),
            'gtt' => Employee::where('employment_status', 'gtt')->count(),
            'staf_tetap' => Employee::where('employment_status', 'staf_tetap')->count(),
            'honorer' => Employee::where('employment_status', 'honorer')->count(),
        ];

        // Alerts: Documents expiring in next 90 days
        $expiringDocuments = EmployeeDocument::whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(90))
            ->where('expiry_date', '>=', now())
            ->with('employee.user')
            ->get();

        return view('hrd.dashboard', compact('totalEmployees', 'totalGuru', 'totalStaf', 'statusDistribution', 'expiringDocuments'));
    }

    /**
     * View employee documents page.
     */
    public function documents(Employee $employee)
    {
        $employee->load('documents');
        return view('hrd.documents.index', compact('employee'));
    }

    /**
     * Upload document.
     */
    public function storeDocument(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:sk,kontrak,sertifikasi,ijazah,ktp,npwp,bpjs,lainnya',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:issue_date',
            'notes' => 'nullable|string',
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('hrd/documents', 'public');
            
            EmployeeDocument::create([
                'employee_id' => $employee->id,
                'title' => $validated['title'],
                'type' => $validated['type'],
                'file_path' => $path,
                'issue_date' => $validated['issue_date'],
                'expiry_date' => $validated['expiry_date'],
                'notes' => $validated['notes'],
            ]);
            
            return back()->with('success', 'Dokumen berhasil diunggah.');
        }

        return back()->with('error', 'Gagal mengunggah berkas.');
    }

    /**
     * Delete document.
     */
    public function deleteDocument(Employee $employee, EmployeeDocument $document)
    {
        $document->delete();
        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    /**
     * View employee trainings page.
     */
    public function trainings(Employee $employee)
    {
        $employee->load('trainings');
        return view('hrd.trainings.index', compact('employee'));
    }

    /**
     * Save training.
     */
    public function storeTraining(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:workshop,seminar,sertifikasi,diklat,webinar,lainnya',
            'organizer' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'hours' => 'nullable|integer|min:1',
            'certificate_number' => 'nullable|string|max:255',
            'certificate_expiry' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
          ]);

          EmployeeTraining::create(array_merge($validated, ['employee_id' => $employee->id]));

          return back()->with('success', 'Riwayat pelatihan berhasil ditambahkan.');
    }

    /**
     * Show performance evaluation form.
     */
    public function evaluation(Employee $employee)
    {
        return view('hrd.evaluations.form', compact('employee'));
    }

    /**
     * Save performance evaluation.
     */
    public function storeEvaluation(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'period' => 'required|string|max:255',
            'pedagogik' => 'required|numeric|min:0|max:100',
            'profesional' => 'required|numeric|min:0|max:100',
            'sosial' => 'required|numeric|min:0|max:100',
            'kepribadian' => 'required|numeric|min:0|max:100',
            'strengths' => 'nullable|string',
            'improvements' => 'nullable|string',
            'goals' => 'nullable|string',
        ]);

        $scores = [
            'pedagogik' => $validated['pedagogik'],
            'profesional' => $validated['profesional'],
            'sosial' => $validated['sosial'],
            'kepribadian' => $validated['kepribadian'],
        ];

        $totalScore = array_sum($scores) / count($scores);
        
        if ($totalScore >= 85) {
            $grade = 'A';
        } elseif ($totalScore >= 70) {
            $grade = 'B';
        } elseif ($totalScore >= 55) {
            $grade = 'C';
        } elseif ($totalScore >= 40) {
            $grade = 'D';
        } else {
            $grade = 'E';
        }

        PerformanceEvaluation::create([
            'employee_id' => $employee->id,
            'evaluator_id' => auth()->id(),
            'period' => $validated['period'],
            'criteria_scores' => $scores,
            'total_score' => $totalScore,
            'grade' => $grade,
            'strengths' => $validated['strengths'],
            'improvements' => $validated['improvements'],
            'goals' => $validated['goals'],
            'status' => 'submitted'
        ]);

        return redirect()->route('hrd.evaluations.report')->with('success', 'Evaluasi kinerja berhasil disimpan.');
    }

    /**
     * Evaluations report.
     */
    public function evaluationReport()
    {
        $evaluations = PerformanceEvaluation::with('employee.user')->latest()->get();
        return view('hrd.evaluations.report', compact('evaluations'));
    }

    /**
     * Certification and document tracker.
     */
    public function certificationTracker()
    {
        $expiringTrainings = EmployeeTraining::whereNotNull('certificate_expiry')
            ->where('certificate_expiry', '<=', now()->addDays(90))
            ->where('certificate_expiry', '>=', now())
            ->with('employee.user')
            ->get();

        $expiringDocuments = EmployeeDocument::whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(90))
            ->where('expiry_date', '>=', now())
            ->with('employee.user')
            ->get();

        return view('hrd.trainings.tracker', compact('expiringTrainings', 'expiringDocuments'));
    }

    /**
     * Interactive Organogram / Org-Chart.
     */
    public function orgChart()
    {
        $kepsek = Employee::whereHas('user', function($q) { $q->where('role', 'kepala_sekolah'); })->first();
        
        $wakas = Employee::whereHas('user', function($q) {
            $q->whereIn('role', ['waka_kurikulum', 'waka_kesiswaan', 'waka_sarpras', 'waka_humas']);
        })->get();

        $staffAndBk = Employee::whereHas('user', function($q) {
            $q->whereIn('role', ['staf_tu', 'bk']);
        })->get();

        $gurus = Employee::whereHas('user', function($q) {
            $q->where('role', 'guru');
        })->get();

        return view('hrd.org-chart', compact('kepsek', 'wakas', 'staffAndBk', 'gurus'));
    }

    /**
     * Monthly attendance report.
     */
    public function attendanceReport(Request $request)
    {
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        $employees = Employee::with('user')->get();
        $reportData = [];

        foreach ($employees as $employee) {
            $attendances = EmployeeAttendance::where('employee_id', $employee->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->get();

            $hadir = $attendances->where('status', 'hadir')->count();
            $terlambat = $attendances->where('status', 'terlambat')->count();
            $izin = $attendances->where('status', 'izin')->count();
            $sakit = $attendances->where('status', 'sakit')->count();
            $cuti = $attendances->where('status', 'cuti')->count();
            $alfa = $attendances->where('status', 'alfa')->count();

            $totalWorkdays = 25; // standard KBM workdays in a month
            $present = $hadir + $terlambat;
            $rate = $totalWorkdays > 0 ? round(($present / $totalWorkdays) * 100, 1) : 0;

            $reportData[] = [
                'employee' => $employee,
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'izin' => $izin,
                'sakit' => $sakit,
                'cuti' => $cuti,
                'alfa' => $alfa,
                'present' => $present,
                'rate' => $rate,
            ];
        }

        return view('hrd.attendance-report', compact('reportData', 'month', 'year'));
    }

    /**
     * Export attendance to CSV.
     */
    public function exportAttendanceCsv(Request $request)
    {
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        
        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=laporan_kehadiran_pegawai_{$month}_{$year}.csv",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0'
        ];
        
        $callback = function() use ($month, $year) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Nama Pegawai', 'NIP', 'Jabatan', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Cuti', 'Alpa', 'Tingkat Kehadiran (%)']);
            
            $employees = Employee::with('user')->get();
            foreach ($employees as $employee) {
                $attendances = EmployeeAttendance::where('employee_id', $employee->id)
                    ->whereMonth('date', $month)
                    ->whereYear('date', $year)
                    ->get();
                    
                $hadir = $attendances->where('status', 'hadir')->count();
                $terlambat = $attendances->where('status', 'terlambat')->count();
                $izin = $attendances->where('status', 'izin')->count();
                $sakit = $attendances->where('status', 'sakit')->count();
                $cuti = $attendances->where('status', 'cuti')->count();
                $alfa = $attendances->where('status', 'alfa')->count();
                
                $totalWorkdays = 25;
                $present = $hadir + $terlambat;
                $rate = round(($present / $totalWorkdays) * 100, 1);
                
                fputcsv($file, [
                    $employee->user->name ?? '-',
                    $employee->employee_number,
                    $employee->position,
                    $hadir,
                    $terlambat,
                    $izin,
                    $sakit,
                    $cuti,
                    $alfa,
                    $rate
                 ]);
             }
             fclose($file);
         };
         
         return response()->stream($callback, 200, $headers);
    }

    /**
     * Download Employee Card PDF.
     */
    public function exportEmployeeCard(Employee $employee)
    {
        $employee->load('user');
        
        $pdf = Pdf::loadView('hrd.employee-card', compact('employee'))->setPaper([0, 0, 241, 153], 'portrait');
        
        return $pdf->download("kartu_pegawai_{$employee->employee_number}.pdf");
    }
}
