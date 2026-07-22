<?php

namespace App\Domains\Finance\Services;

class WadiahDomainService
{
    /**
     * Validate canteen transaction against student's Wadiah balance & daily spending limit.
     */
    public function validateCanteenPurchase(float $currentBalance, float $dailySpentToday, float $dailyLimit, float $purchaseAmount): array
    {
        if ($currentBalance < $purchaseAmount) {
            return [
                'allowed' => false,
                'reason' => 'Saldo Tabungan Wadiah tidak mencukupi (Saldo: Rp ' . number_format($currentBalance, 0, ',', '.') . ').',
            ];
        }

        if ($dailyLimit > 0 && ($dailySpentToday + $purchaseAmount) > $dailyLimit) {
            $remainingLimit = max(0, $dailyLimit - $dailySpentToday);
            return [
                'allowed' => false,
                'reason' => 'Transaksi melebihi limit jajan harian (Sisa limit hari ini: Rp ' . number_format($remainingLimit, 0, ',', '.') . ').',
            ];
        }

        return [
            'allowed' => true,
            'new_balance' => $currentBalance - $purchaseAmount,
            'new_daily_spent' => $dailySpentToday + $purchaseAmount,
            'reason' => 'Transaksi valid.',
        ];
    }
}
