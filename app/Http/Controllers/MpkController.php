<?php

namespace App\Http\Controllers;

use App\Http\Middleware\RoleMiddleware;
use App\Models\AcademicYear;
use App\Models\MpkAudit;
use App\Models\MpkLpj;
use App\Models\OsisTransaction;
use App\Models\StudentAspiration;
use App\Support\Access\Roles;
use App\Support\Access\StudentDataAccess;
use Illuminate\Http\Request;

class MpkController extends Controller
{
    // =========================================================
    //  DASHBOARD & OSIS FINANCIAL AUDIT
    // =========================================================

    public function index()
    {
        $this->authorize('viewDashboard', MpkAudit::class);

        // Fetch OSIS Transactions with Audit Relation
        $transactions = OsisTransaction::with(['recorder', 'audit.auditor'])
            ->orderBy('transaction_date', 'desc')
            ->paginate(15);

        // Agregasi Keuangan OSIS
        $totalIncome = OsisTransaction::where('type', 'income')->sum('amount');
        $totalExpense = OsisTransaction::where('type', 'expense')->sum('amount');
        $currentBalance = $totalIncome - $totalExpense;

        // Metrik Audit MPK
        $totalTransactions = OsisTransaction::count();
        $verifiedCount = MpkAudit::where('status', 'verified')->count();
        $flaggedCount = MpkAudit::where('status', 'flagged')->count();
        $pendingAuditCount = $totalTransactions - ($verifiedCount + $flaggedCount);

        return view('extracurricular.mpk.index', compact(
            'transactions',
            'totalIncome',
            'totalExpense',
            'currentBalance',
            'totalTransactions',
            'verifiedCount',
            'flaggedCount',
            'pendingAuditCount'
        ));
    }

    public function storeAudit(Request $request)
    {
        $this->authorize('manageAudit', MpkAudit::class);

        $request->validate([
            'osis_transaction_id' => 'required|exists:osis_transactions,id',
            'status' => 'required|in:verified,flagged',
            'notes' => 'nullable|string|max:1000',
        ]);

        MpkAudit::updateOrCreate(
            ['osis_transaction_id' => $request->osis_transaction_id],
            [
                'status' => $request->status,
                'notes' => $request->notes,
                'audited_by' => auth()->id(),
            ]
        );

        $message = $request->status === 'verified'
            ? 'Transaksi OSIS berhasil diverifikasi.'
            : 'Transaksi OSIS berhasil ditandai sebagai mencurigakan (Flagged).';

        return back()->with('success', $message);
    }

    // =========================================================
    //  LPJ HUB: SIDE & ACCOUNTABILITY REVIEW
    // =========================================================

    public function lpjIndex()
    {
        $user = auth()->user();
        $activeYear = AcademicYear::current();

        $lpjs = MpkLpj::with(['academicYear', 'submitter', 'reviewer'])
            ->orderBy('created_at', 'desc')
            ->get();

        $academicYears = AcademicYear::orderBy('year', 'desc')->get();

        // Check if user has permission to review
        $canReview = $user->can('reviewLpj', MpkAudit::class);

        return view('extracurricular.mpk.lpj', compact('lpjs', 'academicYears', 'activeYear', 'canReview'));
    }

    public function storeLpj(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'file' => 'required|file|mimes:pdf|max:10240', // Maks 10MB
        ]);

        $filePath = $request->file('file')->store('uploads/mpk/lpjs', 'public');

        MpkLpj::create([
            'academic_year_id' => $request->academic_year_id,
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $filePath,
            'status' => 'submitted',
            'submitted_by' => auth()->id(),
        ]);

        return back()->with('success', 'Laporan Pertanggungjawaban (LPJ) berhasil diajukan untuk ditinjau.');
    }

    public function reviewLpj(Request $request, MpkLpj $lpj)
    {
        $this->authorize('reviewLpj', MpkAudit::class);

        $request->validate([
            'status' => 'required|in:approved,approved_with_notes,rejected',
            'reviewer_notes' => 'nullable|string|max:2000',
        ]);

        $lpj->update([
            'status' => $request->status,
            'reviewer_notes' => $request->reviewer_notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $statusText = [
            'approved' => 'disetujui sepenuhnya',
            'approved_with_notes' => 'disetujui dengan catatan',
            'rejected' => 'ditolak',
        ][$request->status];

        return back()->with('success', "Keputusan sidang berhasil disimpan. LPJ dinyatakan {$statusText}.");
    }

    // =========================================================
    //  STUDENT VOICE BOX: ASPIRATIONS HUB
    // =========================================================

    public function aspirationsIndex()
    {
        $user = auth()->user();

        // Non-students can see all aspirations
        if ($user->isAdmin() || RoleMiddleware::allowsUser($user, [...StudentDataAccess::TEACHER_ROLES, 'waka_kesiswaan', ...Roles::PRINCIPAL])) {
            $aspirations = StudentAspiration::with(['student.user', 'resolver'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
            $canResolve = true;
        } else {
            // Students can only see their own plus non-anonymous public ones
            $studentId = $user->student?->id;
            $aspirations = StudentAspiration::with(['student.user', 'resolver'])
                ->where('student_id', $studentId)
                ->orWhere(function ($query) {
                    $query->where('is_anonymous', false)
                        ->whereIn('status', ['reviewed', 'delegated', 'resolved']);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(15);
            $canResolve = false;
        }

        return view('extracurricular.mpk.aspirations', compact('aspirations', 'canResolve'));
    }

    public function storeAspiration(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:2000',
            'category' => 'required|in:it,sarpras,akademik,keuangan,kegiatan,umum',
            'is_anonymous' => 'nullable|boolean',
        ]);

        $studentId = auth()->user()->student?->id;

        StudentAspiration::create([
            'title' => $request->title,
            'body' => $request->body,
            'category' => $request->category,
            'is_anonymous' => $request->has('is_anonymous') ? $request->is_anonymous : false,
            'student_id' => $studentId,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Aspirasi Anda berhasil dikirim ke MPK. Terima kasih atas partisipasi Anda.');
    }

    public function resolveAspiration(Request $request, StudentAspiration $aspiration)
    {
        $this->authorize('resolveAspiration', MpkAudit::class);

        $request->validate([
            'status' => 'required|in:reviewed,delegated,resolved',
            'resolution_notes' => 'required|string|max:2000',
        ]);

        $aspiration->update([
            'status' => $request->status,
            'resolution_notes' => $request->resolution_notes,
            'resolved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Status aspirasi berhasil diperbarui dengan solusi yang dicatat.');
    }
}
