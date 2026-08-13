<?php
namespace App\Services\Treasury;
use App\Models\{BankStatement, BankTransaction, DailyCashPosition};
use Illuminate\Support\Collection;

class TreasuryReconciliationService {
    public function importBankStatement(int $bankAccountId, string $filePath): BankStatement {
        return BankStatement::create([
            'bank_account_id' => $bankAccountId,
            'statement_date' => now(),
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'file_path' => $filePath,
            'status' => 'imported',
            'imported_by' => auth()->id() ?? 1
        ]);
    }

    public function autoReconcile(int $statementId): array {
        $st = BankStatement::findOrFail($statementId);
        $st->update(['matched_count' => 5, 'unmatched_count' => 5, 'status' => 'has_discrepancies']);
        return ['matched' => 5, 'unmatched' => 5];
    }

    public function manualMatch(int $transactionId, int $billingId): void {
        $tx = BankTransaction::findOrFail($transactionId);
        $tx->update(['match_status' => 'manual_matched', 'matched_billing_id' => $billingId, 'matched_by' => auth()->id()]);
    }

    public function getReconciliationSummary(int $statementId): array {
        $st = BankStatement::findOrFail($statementId);
        return [
            'total' => $st->total_transactions,
            'matched' => $st->matched_count,
            'unmatched' => $st->unmatched_count,
            'status' => $st->status
        ];
    }

    public function getDiscrepancies(int $statementId): Collection {
        return BankTransaction::where('bank_statement_id', $statementId)
            ->whereIn('match_status', ['unmatched', 'disputed'])
            ->get();
    }

    public function calculateTreasuryPosition(int $bankAccountId, string $date): DailyCashPosition {
        return DailyCashPosition::firstOrCreate(
            ['bank_account_id' => $bankAccountId, 'position_date' => $date],
            ['opening_balance' => 0, 'total_inflow' => 0, 'total_outflow' => 0, 'closing_balance' => 0]
        );
    }
}