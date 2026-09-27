<?php

namespace App\Services\Treasury;

use App\Models\PettyCashFund;
use App\Models\PettyCashTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Petty cash follows the imprest system: a fund's balance only moves when a
 * transaction is approved. Spending cannot overdraw the fund and a
 * replenishment cannot lift it above its imprest amount.
 */
class PettyCashService
{
    public function approve(PettyCashTransaction $transaction, int $approverId): PettyCashTransaction
    {
        return DB::transaction(function () use ($transaction, $approverId) {
            $tx = PettyCashTransaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();
            $this->ensurePending($tx);
            if ((int) $tx->requested_by === $approverId) {
                $this->fail('Pengajuan tidak dapat disetujui oleh pengajunya sendiri.');
            }

            $fund = PettyCashFund::whereKey($tx->fund_id)->lockForUpdate()->firstOrFail();
            if (! $fund->is_active) {
                $this->fail("Dana {$fund->fund_name} sudah nonaktif.");
            }

            $balance = $this->cents($fund->current_balance);
            $amount = $this->cents($tx->amount);
            if ($tx->type === 'pengeluaran') {
                if ($amount > $balance) {
                    $this->fail(sprintf('Saldo %s tidak cukup: saldo Rp %s, pengeluaran Rp %s.', $fund->fund_name, $this->display($balance), $this->display($amount)));
                }
                $balance -= $amount;
            } else {
                $room = $this->cents($fund->imprest_amount) - $balance;
                if ($amount > $room) {
                    $this->fail(sprintf('Pengisian ulang melebihi plafon imprest %s: maksimal Rp %s.', $fund->fund_name, $this->display(max(0, $room))));
                }
                $balance += $amount;
            }

            $fund->update(['current_balance' => $this->money($balance)]);
            // balance_applied_at marks that this approval moved the fund, so the
            // legacy-correction command never applies it a second time.
            $tx->update(['approval_status' => 'approved', 'approved_by' => $approverId, 'rejection_reason' => null, 'balance_applied_at' => now()]);

            return $tx;
        });
    }

    public function reject(PettyCashTransaction $transaction, int $deciderId, string $reason): PettyCashTransaction
    {
        return DB::transaction(function () use ($transaction, $deciderId, $reason) {
            $tx = PettyCashTransaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();
            $this->ensurePending($tx);
            $tx->update(['approval_status' => 'rejected', 'approved_by' => $deciderId, 'rejection_reason' => $reason]);

            return $tx;
        });
    }

    /**
     * What applying the legacy approvals would do, per fund.
     *
     * Before this service existed, approving a transaction never moved the
     * fund's balance. Those approvals are the ones with balance_applied_at
     * NULL. For each fund with any, the result is:
     *   current balance − legacy spending + legacy replenishments.
     * A result below zero or above the imprest amount is flagged instead of
     * applied: the recorded opening balance must have been wrong, and a
     * treasurer has to look before the books change.
     *
     * @return array<int, array{fund_id: int, fund_name: string, current: string, spending: string, replenishment: string, result: string, imprest: string, transactions: int, status: string}>
     */
    public function legacyCorrectionPlan(?int $fundId = null): array
    {
        $funds = PettyCashFund::query()
            ->when($fundId, fn ($q) => $q->whereKey($fundId))
            ->whereHas('transactions', fn ($q) => $q->unappliedApproved())
            ->orderBy('id')
            ->get();

        return $funds->map(fn (PettyCashFund $fund) => $this->planFor($fund))->all();
    }

    /**
     * Applies the plan for every fund whose result stays within 0…imprest.
     * Each fund is locked, re-planned inside the lock and marked in one DB
     * transaction, so running it twice (or concurrently) never double-counts.
     *
     * @return array{applied: array<int, array>, skipped: array<int, array>}
     */
    public function applyLegacyCorrections(?int $fundId = null): array
    {
        $applied = [];
        $skipped = [];

        foreach ($this->legacyCorrectionPlan($fundId) as $plan) {
            $result = DB::transaction(function () use ($plan) {
                $fund = PettyCashFund::whereKey($plan['fund_id'])->lockForUpdate()->firstOrFail();
                $fresh = $this->planFor($fund);
                if ($fresh['status'] !== 'ok' || $fresh['transactions'] === 0) {
                    return ['applied' => false, 'plan' => $fresh];
                }

                $fund->update(['current_balance' => $fresh['result']]);
                PettyCashTransaction::where('fund_id', $fund->id)->unappliedApproved()
                    ->update(['balance_applied_at' => now()]);

                return ['applied' => true, 'plan' => $fresh];
            });

            if ($result['applied']) {
                $applied[] = $result['plan'];
                Log::info('Petty cash legacy approvals applied to fund balance', $result['plan']);
            } else {
                $skipped[] = $result['plan'];
            }
        }

        return ['applied' => $applied, 'skipped' => $skipped];
    }

    private function planFor(PettyCashFund $fund): array
    {
        $legacy = PettyCashTransaction::where('fund_id', $fund->id)->unappliedApproved()->get(['type', 'amount']);
        $spending = $legacy->where('type', 'pengeluaran')->sum(fn ($t) => $this->cents($t->amount));
        $replenishment = $legacy->where('type', 'pengisian_ulang')->sum(fn ($t) => $this->cents($t->amount));
        $current = $this->cents($fund->current_balance);
        $imprest = $this->cents($fund->imprest_amount);
        $result = $current - $spending + $replenishment;

        return [
            'fund_id' => $fund->id,
            'fund_name' => $fund->fund_name,
            'current' => $this->money($current),
            'spending' => $this->money($spending),
            'replenishment' => $this->money($replenishment),
            'result' => $this->money($result),
            'imprest' => $this->money($imprest),
            'transactions' => $legacy->count(),
            'status' => match (true) {
                $result < 0 => 'negative',
                $result > $imprest => 'over_imprest',
                default => 'ok',
            },
        ];
    }

    private function ensurePending(PettyCashTransaction $tx): void
    {
        if ($tx->approval_status !== 'pending') {
            $this->fail('Transaksi ini sudah '.($tx->approval_status === 'approved' ? 'disetujui' : 'ditolak').' sebelumnya.');
        }
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['approval' => $message]);
    }

    private function cents(mixed $amount): int
    {
        return (int) round((float) ($amount ?? 0) * 100);
    }

    private function money(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }

    private function display(int $cents): string
    {
        return number_format($cents / 100, 0, ',', '.');
    }
}
