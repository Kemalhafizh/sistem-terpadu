<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Models\ScholarshipApplication;
use App\Models\ScholarshipProgram;
use App\Models\User;
use App\Services\Financial\ScholarshipService;
use Illuminate\Http\Request;

class ScholarshipController extends Controller
{
    public function programs(ScholarshipService $service)
    {
        $programs = ScholarshipProgram::withCount('applications')->latest()->get();

        return view('scholarship.programs', compact('programs'));
    }

    public function createProgram()
    {
        return view('scholarship.apply'); // Program creation form view
    }

    public function storeProgram(Request $request, ScholarshipService $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:pip,bos,internal_merit,internal_need,external',
            'source' => 'required|in:government,yayasan,corporate,individual',
            'academic_year' => 'required|string|max:9',
            'total_quota' => 'required|integer|min:1',
            'amount_per_student' => 'required|numeric|min:0',
            'application_start_date' => 'required|date',
            'application_end_date' => 'required|date|after_or_equal:application_start_date',
        ]);

        $program = $service->createProgram($validated, auth()->id());

        return redirect()->route('financial.scholarships.programs')
            ->with('success', "Program Beasiswa [{$program->name}] berhasil dibuat!");
    }

    public function openApplications(int $id, ScholarshipService $service)
    {
        $program = ScholarshipProgram::findOrFail($id);
        $service->openApplications($program);

        return back()->with('success', "Pendaftaran Beasiswa [{$program->name}] resmi dibuka!");
    }

    public function applyForm(int $programId)
    {
        $program = ScholarshipProgram::findOrFail($programId);
        $students = User::where('role', 'student')->get();

        return view('scholarship.apply', compact('program', 'students'));
    }

    public function submitApplication(int $programId, Request $request, ScholarshipService $service)
    {
        $program = ScholarshipProgram::findOrFail($programId);

        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'gpa_score' => 'nullable|numeric|between:0,4.00',
            'family_income' => 'nullable|numeric|min:0',
            'recommendation_letter_path' => 'nullable|string',
        ]);

        $studentId = $validated['student_id'];
        $app = $service->submitApplication($program, $studentId, $validated);

        return redirect()->route('financial.scholarships.programs')
            ->with('success', 'Permohonan Beasiswa berhasil dikirim!');
    }

    public function reviewApplications(int $programId, ScholarshipService $service)
    {
        $program = ScholarshipProgram::with(['applications.student', 'applications.disbursement'])->findOrFail($programId);
        $summary = $service->getProgramSummary($program);

        return view('scholarship.review', compact('program', 'summary'));
    }

    public function review(int $appId, Request $request, ScholarshipService $service)
    {
        $app = ScholarshipApplication::findOrFail($appId);

        $validated = $request->validate([
            'status' => 'required|in:shortlisted,approved,rejected',
            'review_notes' => 'nullable|string',
        ]);

        $service->reviewApplication($app, auth()->id(), $validated['status'], $validated['review_notes'] ?? null);

        return back()->with('success', 'Status aplikasi beasiswa berhasil diperbarui!');
    }

    public function disbursementForm(int $appId)
    {
        $application = ScholarshipApplication::with(['program', 'student'])->findOrFail($appId);

        return view('scholarship.disbursement', compact('application'));
    }

    public function disburse(int $appId, Request $request, ScholarshipService $service)
    {
        $app = ScholarshipApplication::findOrFail($appId);

        $validated = $request->validate([
            'disbursement_method' => 'required|in:bank_transfer,cash,wadiah_credit',
        ]);

        $disbursement = $service->disburse($app, $validated['disbursement_method'], auth()->id());

        return redirect()->route('financial.scholarships.review', $app->scholarship_program_id)
            ->with('success', 'Dana Beasiswa sejumlah Rp '.number_format($disbursement->amount, 0, ',', '.').' berhasil dicairkan!');
    }

    public function report(ScholarshipService $service)
    {
        $programs = ScholarshipProgram::with(['applications.student', 'applications.disbursement'])->latest()->get();

        return view('scholarship.report', compact('programs'));
    }
}
