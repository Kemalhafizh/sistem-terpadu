<?php

namespace App\Http\Controllers;

use App\Models\AlumniData;
use App\Models\Student;
use App\Models\TracerResponse;
use App\Services\AlumniEnhancedService;
use App\Services\AlumniService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TracerStudyController extends Controller
{
    protected AlumniService $alumniService;

    protected AlumniEnhancedService $alumniEnhancedService;

    public function __construct(AlumniService $alumniService, AlumniEnhancedService $alumniEnhancedService)
    {
        $this->alumniService = $alumniService;
        $this->alumniEnhancedService = $alumniEnhancedService;
    }

    /**
     * Show the tracer study questionnaire form for alumni.
     */
    public function showForm()
    {
        $user = auth()->user();
        $student = $user->student;

        if ($user->role === 'admin' || $user->role === 'superadmin') {
            $alumni = AlumniData::first();
            if (! $alumni) {
                $student = Student::factory()->create(['status' => 'alumni']);
                $alumni = AlumniData::factory()->create(['student_id' => $student->id]);
            }
            $existingResponse = $alumni->tracerResponse;

            return view('alumni.tracer.form', compact('alumni', 'existingResponse'));
        }

        if (! $student || $student->status !== 'alumni' || ! $student->alumniData) {
            abort(403, 'Hanya siswa dengan status Alumni yang dapat mengakses dan mengisi kuesioner Tracer Study ini.');
        }

        $alumni = $student->alumniData;
        $existingResponse = $alumni->tracerResponse;

        return view('alumni.tracer.form', compact('alumni', 'existingResponse'));
    }

    /**
     * Handle submission of the tracer study questionnaire.
     */
    public function submitForm(Request $request)
    {
        $user = auth()->user();
        $student = $user->student;

        if ($user->role === 'admin' || $user->role === 'superadmin') {
            $alumni = AlumniData::first();
            if (! $alumni) {
                $student = Student::factory()->create(['status' => 'alumni']);
                $alumni = AlumniData::factory()->create(['student_id' => $student->id]);
            }
        } else {
            if (! $student || $student->status !== 'alumni' || ! $student->alumniData) {
                abort(403, 'Akses ditolak. Anda tidak terdaftar sebagai alumni.');
            }
            $alumni = $student->alumniData;
        }

        $validated = $request->validate([
            'current_activity' => 'required|in:study,work,entrepreneur,unemployed,other',
            'institution_name' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'employer_name' => 'nullable|string|max:255',
            'entry_year' => 'nullable|integer|min:2000|max:'.(date('Y') + 1),
            'salary_range' => 'nullable|in:< 3M,3M - 5M,5M - 10M,> 10M',
            'study_major' => 'nullable|string|max:255',
            'relevance_score' => 'required|integer|min:1|max:5',
            'satisfaction_score' => 'required|integer|min:1|max:5',
            'suggestions' => 'nullable|string',
        ]);

        $this->alumniService->submitTracerStudy($alumni, $validated);

        return redirect()->route('alumni.tracer-study.form')
            ->with('success', 'Terima kasih! Kuesioner Tracer Study Anda telah berhasil disimpan.');
    }

    /**
     * Edit response for alumni.
     */
    public function editResponse(TracerResponse $tracerResponse)
    {
        $alumni = $tracerResponse->alumni;
        $existingResponse = $tracerResponse;

        return view('alumni.tracer.form', compact('alumni', 'existingResponse'));
    }

    /**
     * Update response for alumni.
     */
    public function updateResponse(Request $request, TracerResponse $tracerResponse)
    {
        $validated = $request->validate([
            'current_activity' => 'required|in:study,work,entrepreneur,unemployed,other',
            'institution_name' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'employer_name' => 'nullable|string|max:255',
            'entry_year' => 'nullable|integer|min:2000|max:'.(date('Y') + 1),
            'salary_range' => 'nullable|in:< 3M,3M - 5M,5M - 10M,> 10M',
            'study_major' => 'nullable|string|max:255',
            'relevance_score' => 'required|integer|min:1|max:5',
            'satisfaction_score' => 'required|integer|min:1|max:5',
            'suggestions' => 'nullable|string',
        ]);

        $alumni = $tracerResponse->alumni;
        $this->alumniService->submitTracerStudy($alumni, $validated);

        return redirect()->route('alumni.tracer-study.form')
            ->with('success', 'Tanggapan kuesioner Tracer Study berhasil diperbarui.');
    }

    /**
     * Show the tracer study analytics dashboard for Humas & Admin.
     */
    public function analytics()
    {
        $summary = $this->alumniService->getAnalyticsSummary();
        $detailStats = $this->alumniEnhancedService->getAlumniDistribution();

        return view('alumni.tracer.analytics', compact('summary', 'detailStats'));
    }

    /**
     * Show side-by-side comparison of cohorts.
     */
    public function comparison(Request $request)
    {
        $graduationYears = AlumniData::select('graduation_year')->distinct()->orderBy('graduation_year', 'desc')->pluck('graduation_year');

        $yearA = $request->year_a ?? $graduationYears->first() ?? date('Y');
        $yearB = $request->year_b ?? $graduationYears->skip(1)->first() ?? (date('Y') - 1);

        $comparison = $this->alumniEnhancedService->getCohortComparison($yearA, $yearB);

        return view('alumni.tracer.comparison', compact('graduationYears', 'comparison', 'yearA', 'yearB'));
    }

    /**
     * Show invitation broadcast panel.
     */
    public function invitation(Request $request)
    {
        $graduationYears = AlumniData::select('graduation_year')->distinct()->orderBy('graduation_year', 'desc')->pluck('graduation_year');
        $selectedYear = $request->year ?? $graduationYears->first() ?? date('Y');

        // Retrieve alumni of selected year who haven't completed tracer study
        $unresponsiveAlumni = AlumniData::where('graduation_year', $selectedYear)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('tracer_responses')
                    ->whereColumn('tracer_responses.alumni_data_id', 'alumni_data.id');
            })
            ->with('student.user')
            ->get();

        $responseRate = $this->alumniEnhancedService->getResponseRate($selectedYear);

        return view('alumni.tracer.invitation', compact('graduationYears', 'selectedYear', 'unresponsiveAlumni', 'responseRate'));
    }

    /**
     * Export all tracer study responses to CSV.
     */
    public function exportExcel()
    {
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=respon_tracer_study_sma_isa1.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Nama Alumni', 'Angkatan', 'Aktivitas Saat Ini', 'Instansi/Universitas',
                'Pekerjaan', 'Tempat Bekerja', 'Tahun Mulai', 'Kisaran Gaji',
                'Program Studi', 'Skor Relevansi', 'Skor Kepuasan', 'Saran Kurikulum',
            ]);

            $responses = TracerResponse::with(['alumni.student'])->get();
            foreach ($responses as $res) {
                fputcsv($file, [
                    $res->alumni->student->name ?? '-',
                    $res->alumni->graduation_year ?? '-',
                    $res->alumni->current_activity ?? '-',
                    $res->alumni->institution_name ?? '-',
                    $res->job_title ?? '-',
                    $res->employer_name ?? '-',
                    $res->entry_year ?? '-',
                    $res->salary_range ?? '-',
                    $res->study_major ?? '-',
                    $res->relevance_score,
                    $res->satisfaction_score,
                    $res->suggestions ?? '-',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
