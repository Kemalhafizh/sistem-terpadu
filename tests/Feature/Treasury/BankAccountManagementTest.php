<?php

use App\Models\BankAccount;
use App\Models\BankStatement;
use App\Models\DailyCashPosition;
use App\Models\User;
use App\Models\VendorPayment;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'admin']);
    $this->account = BankAccount::create([
        'bank_name' => 'BSI', 'account_number' => '7001', 'account_name' => 'Operasional',
        'account_type' => 'giro', 'current_balance' => 5000000, 'is_primary' => true,
    ]);
});

function accountPayload(array $overrides = []): array
{
    return $overrides + [
        'bank_name' => 'BSI', 'account_number' => '7001', 'account_name' => 'Operasional Sekolah',
        'account_type' => 'giro', 'current_balance' => '5000000', 'is_active' => '1', 'is_primary' => '1',
    ];
}

test('the detail page shows the account with its statements, positions and vendor payments', function () {
    BankStatement::create([
        'bank_account_id' => $this->account->id, 'statement_date' => '2026-09-30', 'period_start' => '2026-09-01',
        'period_end' => '2026-09-30', 'file_path' => 'bank-statements/sep.csv', 'total_transactions' => 12,
        'matched_count' => 9, 'unmatched_count' => 3, 'status' => 'has_discrepancies', 'imported_by' => $this->user->id,
    ]);
    DailyCashPosition::create([
        'bank_account_id' => $this->account->id, 'position_date' => '2026-09-30',
        'opening_balance' => 5000000, 'total_inflow' => 1500000, 'total_outflow' => 250000, 'closing_balance' => 6250000,
    ]);
    VendorPayment::create([
        'vendor_name' => 'PLN', 'description' => 'Listrik', 'amount' => 2350000, 'due_date' => '2026-09-20',
        'payment_method' => 'transfer', 'status' => 'scheduled', 'bank_account_id' => $this->account->id, 'created_by' => $this->user->id,
    ]);

    $this->actingAs($this->user)->get(route('treasury.accounts.show', $this->account))
        ->assertOk()
        ->assertSee('BSI 7001')
        ->assertSee('01/09/2026 s.d. 30/09/2026')
        ->assertSee('9/12 cocok')
        ->assertSee('Rp 6.250.000')
        ->assertSee('PLN');
});

test('an account can be edited, including deactivating it', function () {
    $this->actingAs($this->user)->get(route('treasury.accounts.edit', $this->account))->assertOk()->assertSee('Operasional');

    $this->put(route('treasury.accounts.update', $this->account), accountPayload([
        'account_name' => 'Operasional Lama', 'is_active' => '0', 'is_primary' => '0',
    ]))->assertRedirect(route('treasury.accounts.show', $this->account));

    $this->account->refresh();
    expect($this->account->account_name)->toBe('Operasional Lama')
        ->and($this->account->is_active)->toBeFalse()
        ->and($this->account->is_primary)->toBeFalse();
});

test('only one account is primary, and a primary account must be active', function () {
    $this->actingAs($this->user)->post(route('treasury.accounts.store'), accountPayload([
        'bank_name' => 'BRI', 'account_number' => '0099', 'account_name' => 'Dana BOS',
    ]));

    expect(BankAccount::where('is_primary', true)->pluck('bank_name')->all())->toBe(['BRI']);

    $this->put(route('treasury.accounts.update', $this->account), accountPayload(['is_active' => '0', 'is_primary' => '1']))
        ->assertSessionHasErrors('is_primary');
});

test('the same account number cannot be registered twice for one bank', function () {
    $this->actingAs($this->user)->post(route('treasury.accounts.store'), accountPayload(['is_primary' => '0']))
        ->assertSessionHasErrors('account_number');

    expect(BankAccount::count())->toBe(1);
});

test('an unused account can be deleted', function () {
    $this->actingAs($this->user)->delete(route('treasury.accounts.destroy', $this->account))
        ->assertRedirect(route('treasury.accounts.index'));

    expect(BankAccount::count())->toBe(0);
});

test('an account with history is not deleted, so its statements and positions survive', function () {
    BankStatement::create([
        'bank_account_id' => $this->account->id, 'statement_date' => '2026-09-30', 'period_start' => '2026-09-01',
        'period_end' => '2026-09-30', 'file_path' => 'bank-statements/sep.csv', 'imported_by' => $this->user->id,
    ]);

    $this->actingAs($this->user)->from(route('treasury.accounts.edit', $this->account))
        ->delete(route('treasury.accounts.destroy', $this->account))
        ->assertRedirect(route('treasury.accounts.edit', $this->account))
        ->assertSessionHasErrors('account');

    expect(BankAccount::count())->toBe(1)->and(BankStatement::count())->toBe(1);
});
