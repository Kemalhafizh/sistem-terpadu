<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Database\Seeder;

class FinancialAuditSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Fetch or create accounts
        $kas = Account::firstOrCreate(['code' => '101'], [
            'name' => 'Kas Utama Sekolah',
            'type' => 'asset',
            'normal_balance' => 'debit',
        ]);

        $bank = Account::firstOrCreate(['code' => '102'], [
            'name' => 'Bank Central Syariah',
            'type' => 'asset',
            'normal_balance' => 'debit',
        ]);

        $sppIncome = Account::firstOrCreate(['code' => '401'], [
            'name' => 'Pendapatan SPP Siswa',
            'type' => 'revenue',
            'normal_balance' => 'credit',
        ]);

        $sarprasExpense = Account::firstOrCreate(['code' => '501'], [
            'name' => 'Beban Pemeliharaan Sarpras',
            'type' => 'expense',
            'normal_balance' => 'debit',
        ]);

        $utilityExpense = Account::firstOrCreate(['code' => '502'], [
            'name' => 'Beban Listrik & Air',
            'type' => 'expense',
            'normal_balance' => 'debit',
        ]);

        // 2. Create balanced journal entries
        for ($i = 1; $i <= 5; $i++) {
            $amount = $i * 500000;
            $entry = JournalEntry::create([
                'entry_date' => now()->subDays($i)->format('Y-m-d'),
                'description' => "Penerimaan SPP Siswa Batch #{$i}",
                'reference' => "REF-SPP-2026-0{$i}",
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $kas->id,
                'debit' => $amount,
                'credit' => 0,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $sppIncome->id,
                'debit' => 0,
                'credit' => $amount,
            ]);
        }

        // 3. Create unbalanced journal entries (for simulation)
        $unbalancedEntry = JournalEntry::create([
            'entry_date' => now()->subDays(2)->format('Y-m-d'),
            'description' => 'Pembelian Alat Kebersihan & Cat Kelas (Simulasi Selisih)',
            'reference' => 'REF-SARPRAS-999',
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $unbalancedEntry->id,
            'account_id' => $sarprasExpense->id,
            'debit' => 750000,
            'credit' => 0,
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $unbalancedEntry->id,
            'account_id' => $kas->id,
            'debit' => 0,
            'credit' => 700000, // Selisih 50.000!
        ]);

        // 4. Create unpaired cash transaction (orphan expense)
        $orphanEntry = JournalEntry::create([
            'entry_date' => now()->subDay()->format('Y-m-d'),
            'description' => 'Beban Pemeliharaan AC (Tanpa Akun Kas)',
            'reference' => 'REF-ORPHAN-001',
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $orphanEntry->id,
            'account_id' => $sarprasExpense->id,
            'debit' => 300000,
            'credit' => 0,
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $orphanEntry->id,
            'account_id' => $utilityExpense->id, // Menggunakan beban alih-alih kas/bank
            'debit' => 0,
            'credit' => 300000,
        ]);
    }
}
