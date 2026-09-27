<?php

namespace App\Services\Student;

use App\Models\MiniBankTransaction;
use App\Models\StudentWallet;
use Illuminate\Support\Str;

class MiniBankingService
{
    public function processDeposit($walletId, $amount, $description)
    {
        $wallet = StudentWallet::findOrFail($walletId);
        $wallet->balance += $amount;
        $wallet->save();

        return MiniBankTransaction::create([
            'wallet_id' => $wallet->id,
            'transaction_type' => 'deposit',
            'amount' => $amount,
            'reference_code' => 'DEP'.Str::upper(Str::random(8)),
            'description' => $description,
        ]);
    }

    public function processPayment($walletId, $amount, $description)
    {
        $wallet = StudentWallet::findOrFail($walletId);
        if ($wallet->balance < $amount) {
            throw new \Exception('Saldo tidak mencukupi');
        }
        $wallet->balance -= $amount;
        $wallet->save();

        return MiniBankTransaction::create([
            'wallet_id' => $wallet->id,
            'transaction_type' => 'payment',
            'amount' => $amount,
            'reference_code' => 'PAY'.Str::upper(Str::random(8)),
            'description' => $description,
        ]);
    }

    public function transferFunds($fromWalletId, $toWalletId, $amount, $description)
    {
        $this->processPayment($fromWalletId, $amount, 'Transfer out: '.$description);
        $this->processDeposit($toWalletId, $amount, 'Transfer in: '.$description);

        return true;
    }

    public function generateStatement($walletId)
    {
        return MiniBankTransaction::where('wallet_id', $walletId)->orderBy('created_at', 'desc')->get();
    }
}
