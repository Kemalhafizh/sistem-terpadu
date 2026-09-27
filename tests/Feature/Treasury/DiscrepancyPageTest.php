<?php

use App\Models\BankAccount;
use App\Models\BankStatement;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'admin']);
    $this->bsi = BankAccount::create(['bank_name' => 'BSI', 'account_number' => '7001', 'account_name' => 'Operasional', 'account_type' => 'giro']);
    $this->bri = BankAccount::create(['bank_name' => 'BRI', 'account_number' => '0099', 'account_name' => 'Dana BOS', 'account_type' => 'giro']);

    foreach ([[$this->bsi, [
        ['description' => 'TRF TIDAK DIKENAL', 'credit' => 99000, 'match_status' => 'unmatched'],
        ['description' => 'SETORAN SELISIH', 'credit' => 305000, 'match_status' => 'disputed', 'match_notes' => 'nominal berbeda'],
        ['description' => 'SPP COCOK', 'credit' => 450000, 'match_status' => 'auto_matched'],
    ]], [$this->bri, [
        ['description' => 'BIAYA ADMIN BRI', 'debit' => 6500, 'match_status' => 'unmatched'],
    ]]] as [$account, $rows]) {
        $statement = BankStatement::create([
            'bank_account_id' => $account->id, 'statement_date' => '2026-09-30', 'period_start' => '2026-09-01',
            'period_end' => '2026-09-30', 'file_path' => 'x.csv', 'imported_by' => $this->user->id,
        ]);
        foreach ($rows as $row) {
            $statement->transactions()->create($row + ['transaction_date' => '2026-09-10']);
        }
    }
});

test('the discrepancy page lists unmatched and disputed transactions of every account', function () {
    $this->actingAs($this->user)->get(route('treasury.reconciliation.discrepancies'))
        ->assertOk()
        ->assertSee('TRF TIDAK DIKENAL')
        ->assertSee('SETORAN SELISIH')
        ->assertSee('BIAYA ADMIN BRI')
        ->assertDontSee('SPP COCOK')
        ->assertSee('Rp 404.000')        // unmatched credits 99.000 + 305.000
        ->assertSee('Rp 6.500')          // unmatched debits
        ->assertSee('1 mutasi')          // disputed
        ->assertDontSee('Daftar selisih transaksi');
});

test('the discrepancy page can be filtered by account', function () {
    $this->actingAs($this->user)->get(route('treasury.reconciliation.discrepancies', ['bank_account_id' => $this->bri->id]))
        ->assertOk()
        ->assertSee('BIAYA ADMIN BRI')
        ->assertDontSee('TRF TIDAK DIKENAL');
});
