<?php

namespace App\Http\Controllers;

use App\Models\AlumniData;
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
     * Admins get a read-only preview filled with an existing alumnus.
     */
    public function showForm()
    {
        $user = auth()->user();

        if ($this->isAdmin($user)) {
            // This used to create a fake student + alumnus with model factories
            // (dev-only classes) when none existed: a 500 in production.
            $alumni = AlumniData::first();
            if (! $alumni) {
                return redirect()->route('alumni.tracer-study.analytics')
                    ->with('info', 'Belum ada data alumni, jadi pratinjau kuesioner belum bisa ditampilkan.');
            }

            return view('alumni.tracer.form', ['alumni' => $alumni, 'existingResponse' => $alumni->tracerResponse]);
        }

        $alumni = $this->ownAlumniOrAbort($user, 'Hanya siswa dengan status Alumni yang dapat mengakses dan mengisi kuesioner Tracer Study ini.');

        return view('alumni.tracer.form', ['alumni' => $alumni, 'existingResponse' => $alumni->tracerResponse]);
    }

    /**
     * Handle submission of the tracer study questionnaire.
     */
    public function submitForm(Request $request)
    {
        $user = auth()->user();

        // Admins only preview the form. Submitting used to overwrite the
        // answers of whichever alumnus happened to be first in the table.
        abort_if($this->isAdmin($user), 403, 'Admin hanya dapat meninjau kuesioner, bukan mengisinya atas nama alumni.');

        $alumni = $this->ownAlumniOrAbort($user, 'Akses ditolak. Anda tidak terdaftar sebagai alumni.');

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
        $this->authorizeResponse($tracerResponse);

        $alumni = $tracerResponse->alumni;
        $existingResponse = $tracerResponse;

        return view('alumni.tracer.form', compact('alumni', 'existingResponse'));
    }

    /**
     * Update response for alumni.
     */
    public function updateResponse(Request $request, TracerResponse $tracerResponse)
    {
        $this->authorizeResponse($tracerResponse);

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

    private function isAdmin($user): bool
    {
        return in_array($user->role, ['admin', 'superadmin'], true);
    }

    private function ownAlumniOrAbort($user, string $message): AlumniData
    {
        $student = $user->student;

        abort_if(! $student || $student->status !== 'alumni' || ! $student->alumniData, 403, $message);

        return $student->alumniData;
    }

    /**
     * Alumni may only edit their own answers (the route takes any response id).
     */
    private function authorizeResponse(TracerResponse $tracerResponse): void
    {
        $user = auth()->user();

        if ($this->isAdmin($user)) {
            return;
        }

        $own = $user->student?->alumniData;
        abort_unless($own && $tracerResponse->alumni_data_id === $own->id, 403, 'Anda hanya dapat mengubah jawaban kuesioner milik sendiri.');
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
