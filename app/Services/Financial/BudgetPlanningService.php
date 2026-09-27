<?php

namespace App\Services\Financial;

use App\Models\BudgetLineItem;
use App\Models\BudgetPlan;
use App\Models\BudgetRevision;

class BudgetPlanningService
{
    public function createPlan(array $data, int $userId): BudgetPlan
    {
        return BudgetPlan::create([
            'fiscal_year' => $data['fiscal_year'],
            'title' => $data['title'],
            'status' => 'draft',
            'total_revenue_planned' => $data['total_revenue_planned'] ?? 0,
            'total_expense_planned' => $data['total_expense_planned'] ?? 0,
            'created_by' => $userId,
        ]);
    }

    public function addLineItem(BudgetPlan $plan, array $data): BudgetLineItem
    {
        $planned = (float) $data['planned_amount'];
        $actual = (float) ($data['actual_amount'] ?? 0);
        $variance = $planned - $actual;

        $item = $plan->lineItems()->create([
            'category' => $data['category'],
            'sub_category' => $data['sub_category'],
            'account_code' => $data['account_code'],
            'description' => $data['description'],
            'planned_amount' => $planned,
            'actual_amount' => $actual,
            'variance_amount' => $variance,
            'quarter' => $data['quarter'] ?? 'annual',
            'notes' => $data['notes'] ?? null,
        ]);

        $this->recalculatePlanTotals($plan);

        return $item;
    }

    public function recalculatePlanTotals(BudgetPlan $plan): void
    {
        $revenue = $plan->lineItems()->where('category', 'revenue')->sum('planned_amount');
        $expense = $plan->lineItems()->where('category', 'expense')->sum('planned_amount');

        $plan->update([
            'total_revenue_planned' => $revenue,
            'total_expense_planned' => $expense,
        ]);
    }

    public function submitForApproval(BudgetPlan $plan): BudgetPlan
    {
        $plan->update(['status' => 'submitted']);

        return $plan;
    }

    public function approvePlan(BudgetPlan $plan, int $approverId): BudgetPlan
    {
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

    public function getVarianceReport(BudgetPlan $plan): array
    {
        $items = $plan->lineItems;
        $totalPlannedRevenue = $items->where('category', 'revenue')->sum('planned_amount');
        $totalActualRevenue = $items->where('category', 'revenue')->sum('actual_amount');
        $totalPlannedExpense = $items->where('category', 'expense')->sum('planned_amount');
        $totalActualExpense = $items->where('category', 'expense')->sum('actual_amount');

        return [
            'revenue' => [
                'planned' => $totalPlannedRevenue,
                'actual' => $totalActualRevenue,
                'variance' => $totalPlannedRevenue - $totalActualRevenue,
                'realization_rate' => $totalPlannedRevenue > 0 ? round(($totalActualRevenue / $totalPlannedRevenue) * 100, 2) : 0,
            ],
            'expense' => [
                'planned' => $totalPlannedExpense,
                'actual' => $totalActualExpense,
                'variance' => $totalPlannedExpense - $totalActualExpense,
                'absorption_rate' => $totalPlannedExpense > 0 ? round(($totalActualExpense / $totalPlannedExpense) * 100, 2) : 0,
            ],
            'net_surplus_deficit' => ($totalActualRevenue - $totalActualExpense),
            'items' => $items,
        ];
    }

    public function forecastNextYear(BudgetPlan $currentPlan, float $growthPercentage = 5.0): array
    {
        $multiplier = 1 + ($growthPercentage / 100);

        return [
            'base_fiscal_year' => $currentPlan->fiscal_year,
            'forecast_fiscal_year' => '2027/2028',
            'growth_rate' => $growthPercentage,
            'forecast_revenue' => round($currentPlan->total_revenue_planned * $multiplier, 2),
            'forecast_expense' => round($currentPlan->total_expense_planned * $multiplier, 2),
            'estimated_surplus' => round(($currentPlan->total_revenue_planned - $currentPlan->total_expense_planned) * $multiplier, 2),
        ];
    }
}
