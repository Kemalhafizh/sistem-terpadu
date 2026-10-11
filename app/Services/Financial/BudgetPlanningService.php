<?php

namespace App\Services\Financial;

use App\Models\Account;
use App\Models\BudgetLineItem;
use App\Models\BudgetPlan;
use App\Models\BudgetRevision;
use App\Models\JournalEntryLine;
use DomainException;

/**
 * RKAS / RAPBS: TU drafts the plan, the principal approves it, and its
 * realisation comes from the ledger (or what TU records for lines whose
 * account is not in the chart of accounts).
 */
class BudgetPlanningService
{
    public function createPlan(array $data, int $userId): BudgetPlan
    {
        return BudgetPlan::create([
            'fiscal_year' => $data['fiscal_year'],
            'title' => $data['title'],
            'status' => 'draft',
            'total_revenue_planned' => 0,
            'total_expense_planned' => 0,
            'created_by' => $userId,
        ]);
    }

    public function addLineItem(BudgetPlan $plan, array $data): BudgetLineItem
    {
        if (! $plan->isEditable()) {
            throw new DomainException(__('RKAS yang sudah diajukan atau disetujui tidak dapat diubah.'));
        }

        $item = $plan->lineItems()->create([
            'category' => $data['category'],
            'sub_category' => $data['sub_category'],
            'account_code' => $data['account_code'],
            'description' => $data['description'],
            'planned_amount' => (float) $data['planned_amount'],
            'actual_amount' => null,
            'variance_amount' => null,
            'quarter' => $data['quarter'] ?? 'annual',
            'notes' => $data['notes'] ?? null,
        ]);

        $this->recalculatePlanTotals($plan);

        return $item;
    }

    /**
     * Record the realisation of a line whose account is not in the chart of
     * accounts (those come from the ledger and cannot be typed over).
     */
    public function recordRealization(BudgetLineItem $item, ?float $actual): void
    {
        if ($this->ledgerAccount($item)) {
            throw new DomainException(__('Realisasi pos ini dihitung dari buku besar akun :code.', ['code' => $item->account_code]));
        }

        $item->update([
            'actual_amount' => $actual,
            'variance_amount' => $actual === null ? null : (float) $item->planned_amount - $actual,
        ]);
    }

    public function recalculatePlanTotals(BudgetPlan $plan): void
    {
        $plan->update([
            'total_revenue_planned' => $plan->lineItems()->where('category', 'revenue')->sum('planned_amount'),
            'total_expense_planned' => $plan->lineItems()->where('category', 'expense')->sum('planned_amount'),
        ]);
    }

    public function submitForApproval(BudgetPlan $plan): BudgetPlan
    {
        if (! $plan->isEditable()) {
            throw new DomainException(__('RKAS ini sudah diajukan.'));
        }
        if (! $plan->lineItems()->exists()) {
            throw new DomainException(__('Tambahkan pos anggaran sebelum mengajukan RKAS.'));
        }

        $plan->update(['status' => 'submitted']);

        return $plan;
    }

    /** The principal approves; whoever drafted the plan cannot approve it. */
    public function approvePlan(BudgetPlan $plan, int $approverId): BudgetPlan
    {
        if ($plan->status !== 'submitted') {
            throw new DomainException(__('Hanya RKAS yang sudah diajukan yang dapat disetujui.'));
        }
        if ((int) $plan->created_by === $approverId) {
            throw new DomainException(__('RKAS tidak dapat disetujui oleh penyusunnya sendiri.'));
        }

        $plan->update([
            'status' => 'approved',
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);

        return $plan;
    }

    public function revisePlan(BudgetPlan $plan, string $reason, float $newTotal, int $userId): BudgetRevision
    {
        $lastRevision = $plan->revisions()->max('revision_number') ?? 0;
        $oldTotal = $plan->total_expense_planned;

        $revision = $plan->revisions()->create([
            'revision_number' => $lastRevision + 1,
            'reason' => $reason,
            'revised_by' => $userId,
            'old_total' => $oldTotal,
            'new_total' => $newTotal,
        ]);

        $plan->update([
            'status' => 'revised',
            'total_expense_planned' => $newTotal,
        ]);

        return $revision;
    }

    /**
     * Each line's realisation: from the ledger when its account code is in the
     * chart of accounts and the fiscal year has dates, else what TU recorded,
     * else unknown (null). Rates are null when nothing is planned or nothing
     * is known yet, not 0%.
     *
     * @return array{revenue: array<string, ?float>, expense: array<string, ?float>, net_surplus_deficit: ?float, items: list<array{item: BudgetLineItem, actual: ?float, variance: ?float, source: ?string}>}
     */
    public function getVarianceReport(BudgetPlan $plan): array
    {
        $period = $plan->period();
        $items = $plan->lineItems()->orderBy('category')->orderBy('id')->get()->map(function (BudgetLineItem $item) use ($period) {
            $account = $period ? $this->ledgerAccount($item) : null;
            $actual = $account
                ? $this->ledgerAmount($account, $item->category, $period)
                : ($item->actual_amount === null ? null : (float) $item->actual_amount);

            return [
                'item' => $item,
                'actual' => $actual,
                'variance' => $actual === null ? null : (float) $item->planned_amount - $actual,
                'source' => $account ? 'ledger' : ($actual === null ? null : 'manual'),
            ];
        });

        $summary = function (string $category) use ($items) {
            $lines = $items->filter(fn (array $line) => $line['item']->category === $category);
            $planned = (float) $lines->sum(fn (array $line) => (float) $line['item']->planned_amount);
            $known = $lines->whereNotNull('actual');
            $actual = $known->isEmpty() ? null : (float) $known->sum('actual');

            return [
                'planned' => $planned,
                'actual' => $actual,
                'variance' => $actual === null ? null : $planned - $actual,
                'rate' => $actual !== null && $planned > 0 ? round($actual / $planned * 100, 2) : null,
            ];
        };

        $revenue = $summary('revenue');
        $expense = $summary('expense');

        return [
            'revenue' => $revenue,
            'expense' => $expense,
            'net_surplus_deficit' => $revenue['actual'] === null || $expense['actual'] === null ? null : $revenue['actual'] - $expense['actual'],
            'items' => $items->values()->all(),
        ];
    }

    private function ledgerAccount(BudgetLineItem $item): ?Account
    {
        return Account::where('code', $item->account_code)->first();
    }

    /** Revenue accounts realise as credit − debit, expense accounts as debit − credit. */
    private function ledgerAmount(Account $account, string $category, array $period): float
    {
        $sums = JournalEntryLine::where('account_id', $account->id)
            ->whereHas('entry', fn ($q) => $q->whereBetween('entry_date', [$period[0]->toDateString(), $period[1]->toDateString()]))
            ->selectRaw('COALESCE(SUM(debit), 0) as debit, COALESCE(SUM(credit), 0) as credit')
            ->first();

        $debit = (float) ($sums->debit ?? 0);
        $credit = (float) ($sums->credit ?? 0);

        return $category === 'revenue' ? $credit - $debit : $debit - $credit;
    }
}
