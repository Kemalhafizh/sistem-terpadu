<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Billing;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    /**
     * Catat jurnal piutang otomatis saat tagihan dirilis oleh sekolah.
     * Debit: Piutang Tagihan Siswa (1020)
     * Kredit: Pendapatan sesuai kategori (4010 / 4020 / 4030 / 4040)
     */
    public static function recordBillingRelease(Billing $billing): ?JournalEntry
    {
        return DB::transaction(function () use ($billing) {
            // Hindari double-journal untuk billing yang sama
            $existing = JournalEntry::where('reference', 'BILL-'.$billing->id)->first();
            if ($existing) {
                return $existing;
            }

            // Ambil Akun Piutang
            $piutangAccount = Account::where('code', '1020')->first();
            if (! $piutangAccount) {
                return null;
            }

            // Tentukan Akun Pendapatan
            $revenueCode = '4040'; // Default Lain-lain
            $catName = strtolower($billing->category->name ?? '');

            if (str_contains($catName, 'spp')) {
                $revenueCode = '4010';
            } elseif (str_contains($catName, 'gedung') || str_contains($catName, 'pembangunan')) {
                $revenueCode = '4020';
            } elseif (str_contains($catName, 'seragam') || str_contains($catName, 'buku')) {
                $revenueCode = '4030';
            }

            $revenueAccount = Account::where('code', $revenueCode)->first();
            if (! $revenueAccount) {
                return null;
            }

            // 1. Buat Entri Jurnal Utama
            $entry = JournalEntry::create([
                'entry_date' => now()->toDateString(),
                'description' => 'Pencatatan Piutang Tagihan '.($billing->category->name ?? 'Siswa').' - Bulan: '.($billing->month ?? '-').' (Siswa: '.($billing->student->user->name ?? $billing->student_id).')',
                'reference' => 'BILL-'.$billing->id,
            ]);

            // 2. Buat Baris Debit (Piutang bertambah)
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $piutangAccount->id,
                'debit' => $billing->amount,
                'credit' => 0,
            ]);

            // 3. Buat Baris Kredit (Pendapatan bertambah)
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $revenueAccount->id,
                'debit' => 0,
                'credit' => $billing->amount,
            ]);

            return $entry;
        });
    }

    /**
     * Catat jurnal penerimaan kas saat pembayaran tagihan berhasil dilakukan.
     * Debit: Kas & Setara Kas (1010)
     * Kredit: Piutang Tagihan Siswa (1020)
     */
    public static function recordBillingPayment(Payment $payment): ?JournalEntry
    {
        return DB::transaction(function () use ($payment) {
            // Hindari double-journal untuk payment yang sama
            $existing = JournalEntry::where('reference', 'PAY-'.$payment->id)->first();
            if ($existing) {
                return $existing;
            }

            // Ambil Akun Kas dan Piutang
            $kasAccount = Account::where('code', '1010')->first();
            $piutangAccount = Account::where('code', '1020')->first();

            if (! $kasAccount || ! $piutangAccount) {
                return null;
            }

            // 1. Buat Entri Jurnal Utama
            $billing = $payment->billing;
            $entry = JournalEntry::create([
                'entry_date' => $payment->payment_date ?? now()->toDateString(),
                'description' => 'Penerimaan Kas Pembayaran '.($billing->category->name ?? 'Tagihan').' - Kuitansi: '.$payment->receipt_number.' (Siswa: '.($billing->student->user->name ?? $billing->student_id).')',
                'reference' => 'PAY-'.$payment->id,
            ]);

            // 2. Buat Baris Debit (Kas bertambah)
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $kasAccount->id,
                'debit' => $payment->amount_paid,
                'credit' => 0,
            ]);

            // 3. Buat Baris Kredit (Piutang berkurang)
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $piutangAccount->id,
                'debit' => 0,
                'credit' => $payment->amount_paid,
            ]);

            return $entry;
        });
    }
}
