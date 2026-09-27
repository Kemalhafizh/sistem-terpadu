<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ComplianceDocument;
use App\Models\ComplianceMilestone;
use App\Models\EdsStandard;
use App\Services\ComplianceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ComplianceController extends Controller
{
    protected ComplianceService $complianceService;

    public function __construct(ComplianceService $complianceService)
    {
        $this->complianceService = $complianceService;
    }

    /**
     * Display the main compliance dashboard.
     */
    public function index()
    {
        $this->authorize('viewDashboard', ComplianceDocument::class);

        $report = $this->complianceService->getFullComplianceReport();
        $milestones = ComplianceMilestone::orderBy('due_date', 'asc')->get();
        $activeYear = AcademicYear::current();

        $milestonesProgress = $milestones->count() > 0
            ? round(($milestones->where('status', 'completed')->count() / $milestones->count()) * 100)
            : 0;

        $documentsCount = ComplianceDocument::count();

        return view('compliance.index', compact('report', 'milestones', 'activeYear', 'milestonesProgress', 'documentsCount'));
    }

    /**
     * Display the compliance checklist page.
     */
    public function checklist()
    {
        $this->authorize('viewDashboard', ComplianceDocument::class);

        $report = $this->complianceService->getFullComplianceReport();
        $standards = EdsStandard::with('indicators')->get();

        return view('compliance.checklist', compact('report', 'standards'));
    }

    /**
     * Display the document repository.
     */
    public function documentRepository()
    {
        $this->authorize('viewDashboard', ComplianceDocument::class);

        $standards = EdsStandard::orderBy('order', 'asc')->get();
        $documents = ComplianceDocument::with(['standard', 'uploader'])
            ->get()
            ->groupBy('standard_id');

        return view('compliance.documents', compact('standards', 'documents'));
    }

    /**
     * Upload an accreditation document.
     */
    public function uploadDocument(Request $request)
    {
        $this->authorize('manageDocuments', ComplianceDocument::class);

        $request->validate([
            'standard_id' => 'required|exists:eds_standards,id',
            'name' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // max 10MB
            'notes' => 'nullable|string',
        ]);

        $path = $request->file('file')->store('compliance/documents', 'public');

        ComplianceDocument::create([
            'standard_id' => $request->standard_id,
            'name' => $request->name,
            'file_path' => $path,
            'uploaded_by' => auth()->id(),
            'notes' => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Dokumen bukti akreditasi berhasil diunggah.');
    }

    /**
     * Delete an uploaded document.
     */
    public function deleteDocument(ComplianceDocument $document)
    {
        $this->authorize('manageDocuments', ComplianceDocument::class);

        // Delete physically
        Storage::disk('public')->delete($document->file_path);

        // Delete from database
        $document->delete();

        return redirect()->back()->with('success', 'Dokumen bukti akreditasi berhasil dihapus.');
    }

    /**
     * Display the preparation timeline / milestones.
     */
    public function timeline()
    {
        $this->authorize('viewDashboard', ComplianceDocument::class);

        $milestones = ComplianceMilestone::orderBy('due_date', 'asc')->get();
        $activeYear = AcademicYear::current();

        $milestonesProgress = $milestones->count() > 0
            ? round(($milestones->where('status', 'completed')->count() / $milestones->count()) * 100)
            : 0;

        return view('compliance.timeline', compact('milestones', 'activeYear', 'milestonesProgress'));
    }

    /**
     * Store a new compliance milestone.
     */
    public function storeMilestone(Request $request)
    {
        $this->authorize('manageMilestones', ComplianceDocument::class);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
        ]);

        ComplianceMilestone::create([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Milestone persiapan akreditasi baru berhasil disimpan.');
    }

    /**
     * Toggle the status of a compliance milestone.
     */
    public function toggleMilestone(ComplianceMilestone $milestone)
    {
        $this->authorize('manageMilestones', ComplianceDocument::class);

        if ($milestone->status === 'completed') {
            $milestone->update([
                'status' => 'pending',
                'completed_at' => null,
            ]);
            $message = 'Status milestone diubah kembali menjadi Pending.';
        } else {
            $milestone->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            $message = 'Milestone berhasil diselesaikan!';
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Export the accreditation readiness report to PDF.
     */
    public function exportReport()
    {
        $this->authorize('viewDashboard', ComplianceDocument::class);

        $report = $this->complianceService->getFullComplianceReport();
        $documents = ComplianceDocument::with(['standard', 'uploader'])->get();
        $milestones = ComplianceMilestone::orderBy('due_date', 'asc')->get();
        $activeYear = AcademicYear::current();

        $schoolName = school_name();
        $schoolAddress = school_address();

        $pdf = Pdf::loadView('compliance.report-pdf', compact('report', 'documents', 'milestones', 'activeYear', 'schoolName', 'schoolAddress'))
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);

        $safeYear = $activeYear ? str_replace(['/', '\\'], '-', $activeYear->year) : 'Aktif';

        return $pdf->download("SISTA-Kepatuhan-Akreditasi-{$safeYear}.pdf");
    }
}
