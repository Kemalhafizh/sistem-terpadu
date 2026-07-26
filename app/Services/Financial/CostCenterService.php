<?php

namespace App\Services\Financial;

use App\Models\CostCenter;
use App\Models\CostCenterTransaction;

class CostCenterService
{
    public function createCostCenter(array $data): CostCenter
    {
        return CostCenter::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'department' => $data['department'],
            'head_id' => $data['head_id'] ?? null,
            'annual_budget_allocation' => $data['annual_budget_allocation'],
            'spent_amount' => 0,
        ]);
    }

    public function allocateBudget(CostCenter $costCenter, float $amount, string $description, int $userId): CostCenterTransaction
    {
        $transaction = $costCenter->transactions()->create([
            'transaction_type' => 'allocation',
            'amount' => $amount,
            'description' => $description,
            'recorded_by' => $userId,
        ]);

        $costCenter->increment('annual_budget_allocation', $amount);

        return $transaction;
    }

    public function recordExpense(CostCenter $costCenter, float $amount, string $description, int $userId, ?string $refType = null, ?int $refId = null): CostCenterTransaction
    {
        $transaction = $costCenter->transactions()->create([
            'transaction_type' => 'expense',
            'amount' => $amount,
            'description' => $description,
            'reference_type' => $refType,
            'reference_id' => $refId,
            'recorded_by' => $userId,
        ]);

        $costCenter->increment('spent_amount', $amount);

        return $transaction;
    }

    public function getUtilization(CostCenter $costCenter): array
    {
        $allocation = (float) $costCenter->annual_budget_allocation;
        $spent = (float) $costCenter->spent_amount;
        $remaining = $allocation - $spent;
        $rate = $allocation > 0 ? round(($spent / $allocation) * 100, 2) : 0;

        return [
            'cost_center_code' => $costCenter->code,
            'cost_center_name' => $costCenter->name,
            'department' => $costCenter->department,
            'allocation' => $allocation,
            'spent' => $spent,
            'remaining' => $remaining,
            'utilization_rate' => $rate,
            'status' => $rate > 90 ? 'warning' : ($rate > 100 ? 'exceeded' : 'normal'),
        ];
    }
}
