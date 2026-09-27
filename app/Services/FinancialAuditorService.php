<?php

namespace App\Services;

use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Collection;

class FinancialAuditorService
{
    /**
     * Audit total debit vs kredit global di seluruh buku besar.
     */
    public function auditLedgerBalance(): array
    {
        $totalDebit = (float) JournalEntryLine::sum('debit');
        $totalCredit = (float) JournalEntryLine::sum('credit');
        $difference = round(abs($totalDebit - $totalCredit), 2);
        $isBalanced = ($difference < 0.01);

        return [
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'difference' => $difference,
            'is_balanced' => $isBalanced,
        ];
    }

    /**
     * Temukan entri jurnal spesifik yang jumlah debit dan kreditnya tidak seimbang (Unbalanced).
     */
    public function findUnbalancedEntries(): Collection
    {
        return JournalEntry::with(['lines.account'])
            ->get()
            ->filter(function ($entry) {
                $totalDebit = round($entry->lines->sum('debit'), 2);
                $totalCredit = round($entry->lines->sum('credit'), 2);

                return abs($totalDebit - $totalCredit) >= 0.01;
            })
            ->map(function ($entry) {
                $totalDebit = (float) $entry->lines->sum('debit');
                $totalCredit = (float) $entry->lines->sum('credit');
                $diff = round(abs($totalDebit - $totalCredit), 2);

                $entry->total_debit = $totalDebit;
                $entry->total_credit = $totalCredit;
                $entry->imbalance_amount = $diff;
                $entry->issue_type = 'unbalanced_entry';
                $entry->issue_severity = 'critical';

                return $entry;
            })
            ->values();
    }

    /**
     * Deteksi transaksi pengeluaran/beban tanpa akun kas/bank berpasangan (Orphan Cash Transaction).
     */
    public function detectOrphanOrUnpairedCashTransactions(): Collection
    {
        return JournalEntry::with(['lines.account'])
            ->get()
            ->filter(function ($entry) {
                if ($entry->lines->isEmpty()) {
                    return true;
                }

                $hasExpenseLine = $entry->lines->contains(function ($line) {
                    $accountType = strtolower($line->account->type ?? '');

                    return str_contains($accountType, 'expense') || str_contains($accountType, 'beban');
                });

                $hasCashOrAssetLine = $entry->lines->contains(function ($line) {
                    $accountType = strtolower($line->account->type ?? '');
                    $accountName = strtolower($line->account->name ?? '');

                    return str_contains($accountType, 'asset') || str_contains($accountType, 'kas') || str_contains($accountName, 'kas') || str_contains($accountName, 'bank');
                });

                // Jika ada beban tetapi tidak ada akun kas/bank berpasangan
                return $hasExpenseLine && ! $hasCashOrAssetLine;
            })
            ->map(function ($entry) {
                $entry->issue_type = 'unpaired_cash_account';
                $entry->issue_severity = 'high';
                $entry->issue_description = 'Pengeluaran beban tidak disertai dengan akun pasangan Kas/Bank.';

                return $entry;
            })
            ->values();
    }

    /**
     * Deteksi anomali angka transaksi (nilai negatif, 0 debit/kredit, atau jumlah fantastis tanpa referensi).
     */
    public function detectAbnormalTransactions(): Collection
    {
        return JournalEntry::with(['lines.account'])
            ->get()
            ->filter(function ($entry) {
                foreach ($entry->lines as $line) {
                    // Nilai negatif
                    if ($line->debit < 0 || $line->credit < 0) {
                        return true;
                    }
                    // Debit & Kredit sama-sama 0
                    if ($line->debit == 0 && $line->credit == 0) {
                        return true;
                    }
                    // Transaksi jumbo tanpa nomor referensi
                    if (($line->debit > 500000000 || $line->credit > 500000000) && empty($entry->reference)) {
                        return true;
                    }
                }

                return false;
            })
            ->map(function ($entry) {
                $entry->issue_type = 'abnormal_value';
                $entry->issue_severity = 'medium';
                $entry->issue_description = 'Ditemukan nilai angka negatif, nol ganda, atau transaksi besar tanpa referensi.';

                return $entry;
            })
            ->values();
    }

    /**
     * Eksekusi seluruh modul audit dan kembalikan laporan ringkasan lengkap.
     */
    public function runAuditCheck(): array
    {
        $globalBalance = $this->auditLedgerBalance();
        $unbalancedEntries = $this->findUnbalancedEntries();
        $unpairedCashEntries = $this->detectOrphanOrUnpairedCashTransactions();
        $abnormalEntries = $this->detectAbnormalTransactions();

        $totalEntriesCount = JournalEntry::count();
        $criticalCount = $unbalancedEntries->count();
        $highCount = $unpairedCashEntries->count();
        $mediumCount = $abnormalEntries->count();

        // Hitung skor kesehatan (100% minus penalti)
        $penalty = ($criticalCount * 25) + ($highCount * 10) + ($mediumCount * 5);
        if (! $globalBalance['is_balanced']) {
            $penalty += 30;
        }

        $healthScore = max(0, min(100, 100 - $penalty));

        $isClean = ($healthScore === 100 && $criticalCount === 0);

        return [
            'audited_at' => now()->format('d-m-Y H:i:s'),
            'global_balance' => $globalBalance,
            'health_score' => $healthScore,
            'total_entries' => $totalEntriesCount,
            'unbalanced_entries' => $unbalancedEntries,
            'unpaired_cash_entries' => $unpairedCashEntries,
            'abnormal_entries' => $abnormalEntries,
            'findings_summary' => [
                'critical' => $criticalCount,
                'high' => $highCount,
                'medium' => $mediumCount,
                'total_issues' => $criticalCount + $highCount + $mediumCount,
            ],
            'is_clean' => $isClean,
        ];
    }
}
