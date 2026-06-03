<?php

namespace App\Http\Controllers;

use App\Models\AlumniData;
use App\Models\Student;
use App\Services\AlumniService;
use Illuminate\Http\Request;

class TracerStudyController extends Controller
{
    protected AlumniService $alumniService;

    public function __construct(AlumniService $alumniService)
    {
        $this->alumniService = $alumniService;
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

            return view('alumni.tracer_form', compact('alumni', 'existingResponse'));
        }

        if (! $student || $student->status !== 'alumni' || ! $student->alumniData) {
            abort(403, 'Hanya siswa dengan status Alumni yang dapat mengakses dan mengisi kuesioner Tracer Study ini.');
        }

        $alumni = $student->alumniData;
        $existingResponse = $alumni->tracerResponse;

        return view('alumni.tracer_form', compact('alumni', 'existingResponse'));
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
     * Show the tracer study analytics dashboard for Humas & Admin.
     */
    public function analytics()
    {
        $summary = $this->alumniService->getAnalyticsSummary();

        return view('alumni.analytics', compact('summary'));
    }
}
