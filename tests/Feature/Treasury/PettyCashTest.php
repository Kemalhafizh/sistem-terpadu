<?php

use App\Models\PettyCashFund;
use App\Models\PettyCashTransaction;
use App\Models\User;

beforeEach(function () {
    $this->clerk = User::factory()->create(['role' => 'staf_tu']);
    $this->treasurer = User::factory()->create(['role' => 'admin']);
    $this->fund = PettyCashFund::create([
        'fund_name' => 'Kas Kecil TU', 'custodian_id' => $this->clerk->id,
        'imprest_amount' => 2000000, 'current_balance' => 1500000,
    ]);
});

function pettyRequest(PettyCashFund $fund, User $by, string $type, float $amount, string $category = 'atk'): PettyCashTransaction
{
    return PettyCashTransaction::create([
        'fund_id' => $fund->id, 'type' => $type, 'amount' => $amount, 'description' => 'Uji',
        'transaction_date' => '2026-09-10', 'category' => $category, 'requested_by' => $by->id,
    ]);
}

test('recording a transaction leaves the balance alone until it is approved', function () {
    $this->actingAs($this->clerk)->post(route('treasury.petty-cash.store'), [
        'fund_id' => $this->fund->id, 'type' => 'pengeluaran', 'amount' => 125000, 'description' => 'Kertas HVS',
        'transaction_date' => today()->toDateString(), 'category' => 'atk',
    ])->assertRedirect(route('treasury.petty-cash.show', $this->fund->id));

    expect(PettyCashTransaction::sole()->approval_status)->toBe('pending')
        ->and((float) $this->fund->fresh()->current_balance)->toBe(1500000.0);
});

test('approving spending lowers the fund balance', function () {
    $tx = pettyRequest($this->fund, $this->clerk, 'pengeluaran', 125000);

    $this->actingAs($this->treasurer)->post(route('treasury.petty-cash.approve', $tx->id))
        ->assertSessionHas('success', 'Transaksi disetujui. Saldo Kas Kecil TU kini Rp 1.375.000.');

    expect((float) $this->fund->fresh()->current_balance)->toBe(1375000.0)
        ->and($tx->fresh()->approval_status)->toBe('approved')
        ->and($tx->fresh()->approved_by)->toBe($this->treasurer->id);
});

test('approving a replenishment raises the balance, but never above the imprest', function () {
    $ok = pettyRequest($this->fund, $this->clerk, 'pengisian_ulang', 500000);
    $tooMuch = pettyRequest($this->fund, $this->clerk, 'pengisian_ulang', 1);

    $this->actingAs($this->treasurer)->post(route('treasury.petty-cash.approve', $ok->id));
    expect((float) $this->fund->fresh()->current_balance)->toBe(2000000.0);

    $this->post(route('treasury.petty-cash.approve', $tooMuch->id))->assertSessionHasErrors('approval');
    expect((float) $this->fund->fresh()->current_balance)->toBe(2000000.0)
        ->and($tooMuch->fresh()->approval_status)->toBe('pending');
});

test('spending more than the balance cannot be approved', function () {
    $tx = pettyRequest($this->fund, $this->clerk, 'pengeluaran', 1500001);

    $this->actingAs($this->treasurer)->post(route('treasury.petty-cash.approve', $tx->id))
        ->assertSessionHasErrors(['approval' => 'Saldo Kas Kecil TU tidak cukup: saldo Rp 1.500.000, pengeluaran Rp 1.500.001.']);

    expect((float) $this->fund->fresh()->current_balance)->toBe(1500000.0);
});

test('a transaction is applied once: approving twice does not deduct twice', function () {
    $tx = pettyRequest($this->fund, $this->clerk, 'pengeluaran', 100000);

    $this->actingAs($this->treasurer)->post(route('treasury.petty-cash.approve', $tx->id));
    $this->post(route('treasury.petty-cash.approve', $tx->id))->assertSessionHasErrors('approval');

    expect((float) $this->fund->fresh()->current_balance)->toBe(1400000.0);
});

test('the requester cannot approve their own request', function () {
    $tx = pettyRequest($this->fund, $this->clerk, 'pengeluaran', 100000);

    $this->actingAs($this->clerk)->post(route('treasury.petty-cash.approve', $tx->id))->assertSessionHasErrors('approval');

    expect($tx->fresh()->approval_status)->toBe('pending')
        ->and((float) $this->fund->fresh()->current_balance)->toBe(1500000.0);
});

test('rejecting needs a reason and does not touch the balance', function () {
    $tx = pettyRequest($this->fund, $this->clerk, 'pengeluaran', 100000);

    $this->actingAs($this->treasurer)->post(route('treasury.petty-cash.reject', $tx->id))->assertSessionHasErrors('reason');
    $this->post(route('treasury.petty-cash.reject', $tx->id), ['reason' => 'Nota tidak ada']);

    expect($tx->fresh()->approval_status)->toBe('rejected')
        ->and($tx->fresh()->rejection_reason)->toBe('Nota tidak ada')
        ->and((float) $this->fund->fresh()->current_balance)->toBe(1500000.0);
    $this->post(route('treasury.petty-cash.approve', $tx->id))->assertSessionHasErrors('approval');
});

test('the fund page and approval queue list real transactions', function () {
    $pending = pettyRequest($this->fund, $this->clerk, 'pengeluaran', 45000, 'fotocopy');

    $this->actingAs($this->treasurer)->get(route('treasury.petty-cash.show', $this->fund->id))
        ->assertOk()->assertSee('Kas Kecil TU')->assertSee('Rp 1.500.000')->assertSee('Fotokopi')->assertSee('Setujui');

    $this->get(route('treasury.petty-cash.approvals'))
        ->assertOk()->assertSee('Kas Kecil TU')->assertSee('Rp 45.000')->assertDontSee('Daftar persetujuan');

    // The requester sees the item but not the approve button.
    $this->actingAs($this->clerk)->get(route('treasury.petty-cash.approvals'))
        ->assertOk()->assertSee('Menunggu persetujuan pejabat lain')->assertDontSee('>Setujui<', false);
    expect($pending->fresh()->approval_status)->toBe('pending');
});

test('the report totals approved spending per category for the chosen month', function () {
    $service = app(\App\Services\Treasury\PettyCashService::class);
    foreach ([[100000, 'atk'], [50000, 'atk'], [30000, 'konsumsi']] as [$amount, $category]) {
        $service->approve(pettyRequest($this->fund, $this->clerk, 'pengeluaran', $amount, $category), $this->treasurer->id);
    }
    pettyRequest($this->fund, $this->clerk, 'pengeluaran', 999000, 'lainnya'); // pending: not reported
    PettyCashTransaction::create([
        'fund_id' => $this->fund->id, 'type' => 'pengeluaran', 'amount' => 77000, 'description' => 'Agustus',
        'transaction_date' => '2026-08-20', 'category' => 'atk', 'requested_by' => $this->clerk->id,
        'approval_status' => 'approved', 'approved_by' => $this->treasurer->id,
    ]);

    $this->actingAs($this->treasurer)->get(route('treasury.petty-cash.report', ['month' => '2026-09']))
        ->assertOk()
        ->assertSee('Rp 180.000')        // total spent in September
        ->assertSee('Rp 150.000')        // ATK
        ->assertSee('83,3%')             // ATK share
        ->assertSee('Rp 30.000')         // Konsumsi
        ->assertDontSee('Rp 999.000')
        ->assertDontSee('Rp 77.000')
        ->assertDontSee('Laporan kategori');
});

test('a new fund starts empty and is filled by an approved replenishment', function () {
    $this->actingAs($this->clerk)->get(route('treasury.petty-cash.funds.create'))->assertOk();
    $this->post(route('treasury.petty-cash.funds.store'), [
        'fund_name' => 'Kas Kecil Lab', 'custodian_id' => $this->clerk->id, 'imprest_amount' => 1000000,
    ]);

    $fund = PettyCashFund::where('fund_name', 'Kas Kecil Lab')->sole();
    expect((float) $fund->current_balance)->toBe(0.0);

    $refill = pettyRequest($fund, $this->clerk, 'pengisian_ulang', 1000000);
    app(\App\Services\Treasury\PettyCashService::class)->approve($refill, $this->treasurer->id);
    expect((float) $fund->fresh()->current_balance)->toBe(1000000.0);
});

test('a student cannot be made custodian of a fund', function () {
    $student = User::factory()->create(['role' => 'student']);

    $this->actingAs($this->clerk)->post(route('treasury.petty-cash.funds.store'), [
        'fund_name' => 'Kas OSIS', 'custodian_id' => $student->id, 'imprest_amount' => 500000,
    ])->assertSessionHasErrors('custodian_id');
});
