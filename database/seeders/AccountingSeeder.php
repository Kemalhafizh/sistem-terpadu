<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            [
                'code' => '1010',
                'name' => 'Kas & Setara Kas',
                'type' => 'asset',
                'normal_balance' => 'debit',
            ],
            [
                'code' => '1020',
                'name' => 'Piutang Tagihan Siswa',
                'type' => 'asset',
                'normal_balance' => 'debit',
            ],
            [
                'code' => '4010',
                'name' => 'Pendapatan SPP',
                'type' => 'revenue',
                'normal_balance' => 'credit',
            ],
            [
                'code' => '4020',
                'name' => 'Pendapatan Uang Gedung',
                'type' => 'revenue',
                'normal_balance' => 'credit',
            ],
            [
                'code' => '4030',
                'name' => 'Pendapatan Seragam & Buku',
                'type' => 'revenue',
                'normal_balance' => 'credit',
            ],
            [
                'code' => '4040',
                'name' => 'Pendapatan Lain-lain',
                'type' => 'revenue',
                'normal_balance' => 'credit',
            ],
        ];

        foreach ($accounts as $account) {
            Account::updateOrCreate(['code' => $account['code']], $account);
        }
    }
}
