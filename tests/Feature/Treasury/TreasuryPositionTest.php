<?php

use App\Models\BankAccount;
use App\Models\BankStatement;
use App\Models\DailyCashPosition;
use App\Models\PettyCashFund;
use App\Models\PettyCashTransaction;
use App\Models\User;
use App\Models\VendorPayment;
use App\Services\Treasury\TreasuryReconciliationService;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'admin']);
    $this->account = BankAccount::create([
        'bank_name' => 'BSI', 'account_number' => '7123456789', 'account_name' => 'Operasional',
        'account_type' => 'giro', 'current_balance' => 10000000,
    ]);
    $this->other = BankAccount::create([
        'bank_name' => 'BRI', 'account_number' => '0011223344', 'account_name' => 'Dana BOS', 'account_type' => 'giro',
    ]);
    $this->service = app(TreasuryReconciliationService::class);

    $this->addTransactions = function (BankAccount $account, array $transactions): void {
        $dates = array_column($transactions, 'transaction_date');
        $statement = BankStatement::create([
            'bank_account_id' => $account->id, 'statement_date' => max($dates),
            'period_start' => min($dates), 'period_end' => max($dates),
            'file_path' => 'bank-statements/test.csv', 'imported_by' => $this->user->id,
        ]);
        foreach ($transactions as $tx) {
            $statement->transactions()->create($tx + ['description' => 'Mutasi']);
        }
    };

    ($this->addTransactions)($this->account, [
        ['transaction_date' => '2026-09-10', 'credit' => 2500000],
        ['transaction_date' => '2026-09-10', 'credit' => 500000],
        ['transaction_date' => '2026-09-10', 'debit' => 1200000],
        ['transaction_date' => '2026-09-11', 'credit' => 400000],
        ['transaction_date' => '2026-09-11', 'debit' => 150000],
        ['transaction_date' => '2026-09-12', 'debit' => 50000],
    ]);
    ($this->addTransactions)($this->other, [
        ['transaction_date' => '2026-09-10', 'credit' => 7000000],
    ]);
});

function positionValues(DailyCashPosition $p): array
{
    return array_map('floatval', [
        $p->opening_balance, $p->total_inflow, $p->total_outflow, $p->closing_balance,
    ]);
}

test('position sums only that account\'s transactions of that date', function () {
    $position = $this->service->calculateTreasuryPosition($this->account->id, '2026-09-10');

    // opening = recorded balance, inflow = 2.5 jt + 0.5 jt, outflow = 1.2 jt; the other account's 7 jt is excluded
    expect(positionValues($position))->toBe([10000000.0, 3000000.0, 1200000.0, 11800000.0])
        ->and($position->position_date->toDateString())->toBe('2026-09-10');
});

test('opening is the previous day\'s closing', function () {
    $this->service->calculateTreasuryPosition($this->account->id, '2026-09-10');
    $position = $this->service->calculateTreasuryPosition($this->account->id, '2026-09-11');

    expect(positionValues($position))->toBe([11800000.0, 400000.0, 150000.0, 12050000.0]);
});

test('opening carries the flows of days without a stored position', function () {
    $this->service->calculateTreasuryPosition($this->account->id, '2026-09-10');
    $position = $this->service->calculateTreasuryPosition($this->account->id, '2026-09-13');

    // 11.8 jt + (0.4 jt - 0.15 jt on 11/09) - 0.05 jt on 12/09; nothing on 13/09
    expect(positionValues($position))->toBe([12000000.0, 0.0, 0.0, 12000000.0]);
});

test('without an earlier position, opening is the recorded balance plus all earlier transactions', function () {
    $position = $this->service->calculateTreasuryPosition($this->account->id, '2026-09-11');

    // 10 jt recorded + net 1.8 jt on 10/09
    expect(positionValues($position))->toBe([11800000.0, 400000.0, 150000.0, 12050000.0]);
});

test('recalculating replaces a stored all-zero position with the computed one', function () {
    DailyCashPosition::create([
        'bank_account_id' => $this->account->id, 'position_date' => '2026-09-10',
        'opening_balance' => 0, 'total_inflow' => 0, 'total_outflow' => 0, 'closing_balance' => 0,
    ]);

    $position = $this->service->calculateTreasuryPosition($this->account->id, '2026-09-10');

    expect(DailyCashPosition::where('bank_account_id', $this->account->id)->count())->toBe(1)
        ->and(positionValues($position->fresh()))->toBe([10000000.0, 3000000.0, 1200000.0, 11800000.0]);
});

test('the position dashboard shows the computed figures for the chosen date', function () {
    $this->actingAs($this->user)
        ->get(route('treasury.position.dashboard', ['date' => '2026-09-10']))
        ->assertOk()
        ->assertSee('Rp 11.800.000')   // BSI closing
        ->assertSee('Rp 3.000.000')    // BSI inflow
        ->assertSee('Rp 7.000.000');   // BRI inflow and closing (recorded balance 0)

    expect(DailyCashPosition::count())->toBe(2);

    $this->get(route('treasury.position.trend'))->assertOk();
});

test('the accounts page shows the latest computed closing balance', function () {
    $this->service->calculateTreasuryPosition($this->account->id, '2026-09-11');

    $this->actingAs($this->user)
        ->get(route('treasury.accounts.index'))
        ->assertOk()
        ->assertSee('Rp 12.050.000')
        ->assertSee('Saldo akhir posisi kas 11/09/2026');
});

test('a new account can be given its opening balance', function () {
    $response = $this->actingAs($this->user)->post(route('treasury.accounts.store'), [
        'bank_name' => 'Mandiri', 'account_number' => '1370000', 'account_name' => 'Tabungan', 'account_type' => 'tabungan',
        'current_balance' => '2500000', 'is_active' => '1',
    ]);

    $account = BankAccount::where('bank_name', 'Mandiri')->sole();
    $response->assertRedirect(route('treasury.accounts.show', $account));
    expect((float) $account->current_balance)->toBe(2500000.0);
});

test('petty cash and vendor payments are recorded under the logged-in user, not user 1', function () {
    $clerk = User::factory()->create();
    expect($clerk->id)->not->toBe(1);
    $fund = PettyCashFund::create(['fund_name' => 'Kas TU', 'custodian_id' => $this->user->id, 'imprest_amount' => 2000000]);

    $this->actingAs($clerk)->post(route('treasury.petty-cash.store'), [
        'fund_id' => $fund->id, 'type' => 'pengeluaran', 'amount' => 45000, 'description' => 'Fotokopi',
        'transaction_date' => '2026-09-10', 'category' => 'fotocopy',
    ])->assertRedirect();
    $this->actingAs($clerk)->post(route('treasury.vendor.store'), [
        'vendor_name' => 'CV Sinar', 'amount' => 900000, 'due_date' => '2026-09-30',
        'description' => 'Servis AC', 'payment_method' => 'transfer',
    ])->assertRedirect();

    expect(PettyCashTransaction::sole()->requested_by)->toBe($clerk->id)
        ->and(VendorPayment::sole()->created_by)->toBe($clerk->id);
});
