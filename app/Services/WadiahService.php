<?php

namespace App\Services;

use App\Models\Student;
use App\Models\WadiahAccount;
use App\Models\WadiahTransaction;
use App\Models\CanteenMerchant;
use App\Models\CanteenTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class WadiahService
{
    /**
     * Create a new Wadiah savings account for a student.
     */
    public function createAccount(Student $student): WadiahAccount
    {
        return DB::transaction(function () use ($student) {
            // Check if account already exists
            $existing = WadiahAccount::where('student_id', $student->id)->first();
            if ($existing) {
                return $existing;
            }

            // Generate account number: WDH-YYYYMM-NNNN
            $prefix = 'WDH-' . date('Ym') . '-';
            $latest = WadiahAccount::where('account_number', 'like', $prefix . '%')
                ->orderBy('account_number', 'desc')
                ->first();

            $nextNumber = 1;
            if ($latest) {
                $parts = explode('-', $latest->account_number);
                $nextNumber = (int) end($parts) + 1;
            }

            $accountNumber = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            return WadiahAccount::create([
                'student_id' => $student->id,
                'account_number' => $accountNumber,
                'balance' => 0.00,
                'daily_spending_limit' => 50000.00, // Default Rp 50.000
                'is_active' => true,
            ]);
        });
    }

    /**
     * Perform a deposit transaction.
     */
    public function deposit(WadiahAccount $account, float $amount, int $processedBy, string $desc = ''): WadiahTransaction
    {
        if ($amount <= 0) {
            throw new Exception('Jumlah setoran harus lebih besar dari nol.');
        }

        return DB::transaction(function () use ($account, $amount, $processedBy, $desc) {
            $account = WadiahAccount::lockForUpdate()->find($account->id);

            if (! $account->is_active) {
                throw new Exception('Rekening tabungan wadiah tidak aktif.');
            }

            $prevBalance = (float) $account->balance;
            $currBalance = $prevBalance + $amount;

            $account->update([
                'balance' => $currBalance,
            ]);

            // Generate reference: DEP-YYYYMMDD-RAND
            $refNumber = 'DEP-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            return WadiahTransaction::create([
                'wadiah_account_id' => $account->id,
                'type' => 'deposit',
                'amount' => $amount,
                'previous_balance' => $prevBalance,
                'current_balance' => $currBalance,
                'reference_number' => $refNumber,
                'description' => $desc ?: 'Setoran tunai tabungan Wadiah',
                'processed_by' => $processedBy,
            ]);
        });
    }

    /**
     * Perform a withdrawal transaction.
     */
    public function withdraw(WadiahAccount $account, float $amount, int $processedBy, string $desc = ''): WadiahTransaction
    {
        if ($amount <= 0) {
            throw new Exception('Jumlah penarikan harus lebih besar dari nol.');
        }

        return DB::transaction(function () use ($account, $amount, $processedBy, $desc) {
            $account = WadiahAccount::lockForUpdate()->find($account->id);

            if (! $account->is_active) {
                throw new Exception('Rekening tabungan wadiah tidak aktif.');
            }

            if ($account->balance < $amount) {
                throw new Exception('Saldo tidak mencukupi untuk melakukan penarikan.');
            }

            $prevBalance = (float) $account->balance;
            $currBalance = $prevBalance - $amount;

            $account->update([
                'balance' => $currBalance,
            ]);

            $refNumber = 'WTH-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            return WadiahTransaction::create([
                'wadiah_account_id' => $account->id,
                'type' => 'withdrawal',
                'amount' => $amount,
                'previous_balance' => $prevBalance,
                'current_balance' => $currBalance,
                'reference_number' => $refNumber,
                'description' => $desc ?: 'Penarikan tunai tabungan Wadiah',
                'processed_by' => $processedBy,
            ]);
        });
    }

    /**
     * Perform a cashless purchase transaction at the canteen.
     */
    public function purchase(WadiahAccount $account, float $amount, int $merchantUserId, array $items): WadiahTransaction
    {
        if ($amount <= 0) {
            throw new Exception('Jumlah pembelian harus lebih besar dari nol.');
        }

        return DB::transaction(function () use ($account, $amount, $merchantUserId, $items) {
            // Find merchant
            $merchant = CanteenMerchant::where('owner_user_id', $merchantUserId)
                ->where('is_active', true)
                ->first();

            if (! $merchant) {
                throw new Exception('Merchant kantin tidak ditemukan atau tidak aktif.');
            }

            $account = WadiahAccount::lockForUpdate()->find($account->id);

            if (! $account->is_active) {
                throw new Exception('Rekening tabungan wadiah tidak aktif.');
            }

            // Check balance
            if ($account->balance < $amount) {
                throw new Exception('Saldo tabungan wadiah tidak mencukupi.');
            }

            // Check daily spending limit
            $todaySpent = (float) $account->transactions()
                ->where('type', 'purchase')
                ->whereDate('created_at', Carbon::today())
                ->sum('amount');

            if (($todaySpent + $amount) > $account->daily_spending_limit) {
                $limitFormatted = number_format($account->daily_spending_limit, 0, ',', '.');
                $remainingFormatted = number_format(max(0, $account->daily_spending_limit - $todaySpent), 0, ',', '.');
                throw new Exception("Transaksi ditolak. Batasan belanja harian anak adalah Rp {$limitFormatted} (Sisa limit hari ini: Rp {$remainingFormatted}).");
            }

            $prevBalance = (float) $account->balance;
            $currBalance = $prevBalance - $amount;

            $account->update([
                'balance' => $currBalance,
            ]);

            $refNumber = 'PUR-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            $wadiahTx = WadiahTransaction::create([
                'wadiah_account_id' => $account->id,
                'type' => 'purchase',
                'amount' => $amount,
                'previous_balance' => $prevBalance,
                'current_balance' => $currBalance,
                'reference_number' => $refNumber,
                'description' => 'Pembayaran cashless di ' . $merchant->name,
                'processed_by' => $merchantUserId,
            ]);

            CanteenTransaction::create([
                'canteen_merchant_id' => $merchant->id,
                'wadiah_transaction_id' => $wadiahTx->id,
                'items_ordered' => $items,
                'total_amount' => $amount,
            ]);

            return $wadiahTx;
        });
    }

    /**
     * Refund a purchase transaction.
     */
    public function refund(WadiahTransaction $purchaseTx, int $processedBy, string $desc = ''): WadiahTransaction
    {
        if ($purchaseTx->type !== 'purchase') {
            throw new Exception('Hanya transaksi pembelian yang dapat di-refund.');
        }

        // Check if already refunded
        $alreadyRefunded = WadiahTransaction::where('type', 'refund')
            ->where('description', 'like', '%' . $purchaseTx->reference_number . '%')
            ->exists();

        if ($alreadyRefunded) {
            throw new Exception('Transaksi ini sudah pernah di-refund sebelumnya.');
        }

        return DB::transaction(function () use ($purchaseTx, $processedBy, $desc) {
            $account = WadiahAccount::lockForUpdate()->find($purchaseTx->wadiah_account_id);

            $amount = (float) $purchaseTx->amount;
            $prevBalance = (float) $account->balance;
            $currBalance = $prevBalance + $amount;

            $account->update([
                'balance' => $currBalance,
            ]);

            $refNumber = 'RFD-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            return WadiahTransaction::create([
                'wadiah_account_id' => $account->id,
                'type' => 'refund',
                'amount' => $amount,
                'previous_balance' => $prevBalance,
                'current_balance' => $currBalance,
                'reference_number' => $refNumber,
                'description' => $desc ?: "Pengembalian dana (Refund) atas transaksi {$purchaseTx->reference_number}",
                'processed_by' => $processedBy,
            ]);
        });
    }
}
