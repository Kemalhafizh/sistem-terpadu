<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Services\FinancialAuditorService;
use Illuminate\Http\Request;

class FinancialAuditController extends Controller
{
    protected $auditorService;

    public function __construct(FinancialAuditorService $auditorService)
    {
        $this->auditorService = $auditorService;
    }

    /**
     * Dashboard Audit Keuangan & Fraud Detector.
     */
    public function index()
    {
        $auditReport = $this->auditorService->runAuditCheck();

        return view('finance.audit.index', compact('auditReport'));
    }

    /**
     * Jalankan audit langsung.
     */
    public function runAudit(Request $request)
    {
        $auditReport = $this->auditorService->runAuditCheck();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Audit keuangan berhasil dijalankan.',
                'audit_report' => $auditReport,
            ]);
        }

        return redirect()->route('finance.audit.index')
            ->with('success', 'Pemeriksaan audit pembukuan keuangan berhasil diperbarui!');
    }

    /**
     * Tampilkan detail jurnal bermasalah/selisih.
     */
    public function showUnbalancedJournalDetails($id)
    {
        $journal = JournalEntry::with(['lines.account'])->findOrFail($id);

        $totalDebit = (float) $journal->lines->sum('debit');
        $totalCredit = (float) $journal->lines->sum('credit');
        $difference = round(abs($totalDebit - $totalCredit), 2);

        return response()->json([
            'id' => $journal->id,
            'entry_date' => $journal->entry_date,
            'description' => $journal->description,
            'reference' => $journal->reference,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'difference' => $difference,
            'is_balanced' => ($difference < 0.01),
            'lines' => $journal->lines->map(function ($line) {
                return [
                    'account_code' => $line->account->code ?? '-',
                    'account_name' => $line->account->name ?? 'Tanpa Nama',
                    'account_type' => $line->account->type ?? '-',
                    'debit' => (float) $line->debit,
                    'credit' => (float) $line->credit,
                ];
            }),
        ]);
    }

    /**
     * Ekspor Laporan Resmi Audit Keuangan.
     */
    public function exportAuditReport()
    {
        $auditReport = $this->auditorService->runAuditCheck();

        return view('finance.audit.report', compact('auditReport'));
    }
}
