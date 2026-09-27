<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\AcademicYear;
use App\Models\BankAccount;
use App\Models\Billing;
use App\Models\FinancialHealthMetric;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\PettyCashTransaction;
use App\Models\VendorPayment;
use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Financial health snapshot built only from recorded transactions.
 *
 * This used to store fixed figures (cash reserve Rp 1,25 M, burn rate
 * Rp 185 jt, projected revenue Rp 210 jt, a fixed 55/20/12/8/5 expense split)
 * and, with no billing data, invented Rp 500 jt billed / Rp 470 jt paid so the
 * collection rate read 94%. Every metric now comes from real records; one with
 * no source is null and `details` says why.
 */
class FinancialScoreboardService
{
    /** Complete months averaged for the burn rate and expense split. */
    public const BURN_RATE_MONTHS = 3;

    public const EXPENSE_LABELS = [
        'gaji_honor' => 'Gaji & honor (payroll dibayar)',
        'pembayaran_vendor' => 'Pembayaran vendor',
        'kas_kecil' => 'Pengeluaran kas kecil',
    ];

    public function computeFinancialHealth(?CarbonInterface $today = null): FinancialHealthMetric
    {
        $today = Carbon::instance($today ?? now())->startOfDay();

        $cash = $this->cashReserve();
        $expenses = $this->expenses($today);
        $projected = $this->projectedRevenue($today);
        $collection = $this->collectionRate();

        $burnRate = $expenses['total'] > 0
            ? round($expenses['total'] / self::BURN_RATE_MONTHS, 2)
            : null;

        // whereDate, not updateOrCreate: report_date is cast to a datetime
        // string, so an exact match on 'Y-m-d' can miss today's row and add a
        // duplicate snapshot on every page view.
        $metric = FinancialHealthMetric::whereDate('report_date', $today->toDateString())->first()
            ?? new FinancialHealthMetric(['report_date' => $today->toDateString()]);

        $metric->fill([
            'cash_reserve' => $cash['amount'],
            'monthly_burn_rate' => $burnRate,
            'projected_revenue' => $projected['amount'],
            'fee_collection_rate' => $collection['rate'],
            'expense_distribution' => $expenses['distribution'],
            'details' => [
                'cash_reserve' => $cash['details'],
                'burn_rate' => [
                    'period_start' => $expenses['period_start'],
                    'period_end' => $expenses['period_end'],
                    'months' => self::BURN_RATE_MONTHS,
                    'totals' => $expenses['totals'],
                ],
                'projected_revenue' => $projected['details'],
                'fee_collection' => $collection['details'],
                'collected_this_month' => (float) $this->inDateRange(
                    Payment::query(),
                    'payment_date',
                    $today->copy()->startOfMonth()->toDateString(),
                    $today->toDateString()
                )->sum('amount_paid'),
                // Months of spending the registered balance covers.
                'runway_months' => ($cash['amount'] !== null && $burnRate)
                    ? round($cash['amount'] / $burnRate, 1)
                    : null,
            ],
        ])->save();

        return $metric;
    }

    /**
     * Sum of balances on the active bank accounts the treasurer registered.
     * These balances are entered by hand (nothing posts to them
     * automatically), so the most recent update time is reported with them.
     */
    private function cashReserve(): array
    {
        $accounts = BankAccount::where('is_active', true)->get(['current_balance', 'updated_at']);

        if ($accounts->isEmpty()) {
            return ['amount' => null, 'details' => ['accounts' => 0, 'as_of' => null]];
        }

        return [
            'amount' => round((float) $accounts->sum('current_balance'), 2),
            'details' => [
                'accounts' => $accounts->count(),
                'as_of' => $accounts->max('updated_at')?->toDateString(),
            ],
        ];
    }

    /** Money actually paid out over the last complete months, by source. */
    private function expenses(CarbonInterface $today): array
    {
        $start = $today->copy()->startOfMonth()->subMonths(self::BURN_RATE_MONTHS)->toDateString();
        $end = $today->copy()->startOfMonth()->subDay()->toDateString();

        $totals = [
            'gaji_honor' => (float) $this->inDateRange(Payroll::where('status', 'paid'), 'payment_date', $start, $end)
                ->sum('net_salary'),
            'pembayaran_vendor' => (float) $this->inDateRange(VendorPayment::where('status', 'paid'), 'paid_date', $start, $end)
                ->sum('amount'),
            'kas_kecil' => (float) $this->inDateRange(
                PettyCashTransaction::where('type', 'pengeluaran')->where('approval_status', 'approved'),
                'transaction_date', $start, $end
            )
                ->sum('amount'),
        ];
        $total = array_sum($totals);

        $distribution = [];
        if ($total > 0) {
            foreach ($totals as $key => $amount) {
                $distribution[$key] = round($amount / $total * 100, 1);
            }
        }

        return [
            'total' => $total,
            'totals' => $totals,
            'distribution' => $distribution,
            'period_start' => $start,
            'period_end' => $end,
        ];
    }

    /** Unpaid amounts on bills already issued with a due date next month. */
    private function projectedRevenue(CarbonInterface $today): array
    {
        $start = $today->copy()->startOfMonth()->addMonth();
        $end = $start->copy()->endOfMonth();

        $billings = $this->inDateRange(Billing::query(), 'due_date', $start->toDateString(), $end->toDateString());
        $count = (clone $billings)->count();

        return [
            // No bills issued for next month yet → unknown, not "Rp 0".
            'amount' => $count > 0
                ? round((float) (clone $billings)->where('status', '!=', 'paid')->sum('remaining_balance'), 2)
                : null,
            'details' => [
                'month' => $start->format('Y-m'),
                'billings_due' => $count,
            ],
        ];
    }

    /**
     * Share of billed SPP already paid, for the active academic year when it
     * has bills, otherwise across all bills.
     */
    private function collectionRate(): array
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $query = Billing::query();
        $scope = 'all';

        if ($activeYear && Billing::where('academic_year_id', $activeYear->id)->exists()) {
            $query->where('academic_year_id', $activeYear->id);
            $scope = 'active_year';
        }

        $billed = (float) (clone $query)->sum('amount');
        if ($billed <= 0) {
            return ['rate' => null, 'details' => ['scope' => $scope, 'billed' => 0.0, 'collected' => 0.0]];
        }

        $collected = $billed - (float) (clone $query)->sum('remaining_balance');

        return [
            'rate' => round($collected / $billed * 100, 2),
            'details' => [
                'scope' => $scope,
                'academic_year' => $scope === 'active_year' ? $activeYear->year : null,
                'billed' => round($billed, 2),
                'collected' => round($collected, 2),
            ],
        ];
    }

    /**
     * Inclusive date range that also matches values stored with a time part
     * (whereBetween on '...-30' would skip '...-30 00:00:00' in SQLite).
     */
    private function inDateRange($query, string $column, string $start, string $end)
    {
        return $query->whereDate($column, '>=', $start)->whereDate($column, '<=', $end);
    }
}
