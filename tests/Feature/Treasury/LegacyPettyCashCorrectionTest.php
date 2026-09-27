<?php

use App\Models\PettyCashFund;
use App\Models\PettyCashTransaction;
use App\Models\User;
use App\Services\Treasury\PettyCashService;
use Illuminate\Support\Facades\Artisan;

/*
 * Before PettyCashService, approving a petty-cash transaction never moved the
 * fund balance. Those rows (approved, balance_applied_at NULL) are corrected
 * once, after review, by treasury:apply-legacy-petty-cash.
 */
beforeEach(function () {
    $this->requester = User::factory()->create(['role' => 'staf_tu']);
    $this->approver = User::factory()->create(['role' => 'admin']);
    $this->service = app(PettyCashService::class);
});

function legacyFund(float $balance, float $imprest = 5000000, string $name = 'Kas TU'): PettyCashFund
{
    return PettyCashFund::create([
        'fund_name' => $name, 'custodian_id' => User::factory()->create()->id,
        'imprest_amount' => $imprest, 'current_balance' => $balance, 'is_active' => true,
    ]);
}

/** A row approved the old way: status set, balance never touched. */
function legacyApproved(PettyCashFund $fund, User $requester, User $approver, string $type, float $amount): PettyCashTransaction
{
    return PettyCashTransaction::create([
        'fund_id' => $fund->id, 'type' => $type, 'amount' => $amount, 'description' => 'lama',
        'transaction_date' => now()->subMonth()->toDateString(), 'category' => 'atk',
        'approval_status' => 'approved', 'requested_by' => $requester->id, 'approved_by' => $approver->id,
    ]);
}

test('persetujuan lewat service menandai transaksi sudah diterapkan ke saldo', function () {
    $fund = legacyFund(1000000);
    $tx = PettyCashTransaction::create([
        'fund_id' => $fund->id, 'type' => 'pengeluaran', 'amount' => 250000, 'description' => 'baru',
        'transaction_date' => now()->toDateString(), 'category' => 'atk', 'approval_status' => 'pending',
        'requested_by' => $this->requester->id,
    ]);

    $this->service->approve($tx, $this->approver->id);

    expect($tx->fresh()->balance_applied_at)->not->toBeNull()
        ->and((float) $fund->fresh()->current_balance)->toEqual(750000.0)
        ->and($this->service->legacyCorrectionPlan())->toBe([]);
});

test('rencana koreksi menghitung saldo dari transaksi lama saja', function () {
    $fund = legacyFund(5000000);
    legacyApproved($fund, $this->requester, $this->approver, 'pengeluaran', 400000);
    legacyApproved($fund, $this->requester, $this->approver, 'pengeluaran', 600000);
    legacyApproved($fund, $this->requester, $this->approver, 'pengisian_ulang', 300000);
    // Pending and rejected rows never count.
    PettyCashTransaction::create(['fund_id' => $fund->id, 'type' => 'pengeluaran', 'amount' => 999, 'description' => 'x',
        'transaction_date' => now()->toDateString(), 'category' => 'atk', 'approval_status' => 'pending', 'requested_by' => $this->requester->id]);

    [$plan] = $this->service->legacyCorrectionPlan();

    expect($plan['current'])->toBe('5000000.00')
        ->and($plan['spending'])->toBe('1000000.00')
        ->and($plan['replenishment'])->toBe('300000.00')
        ->and($plan['result'])->toBe('4300000.00')
        ->and($plan['transactions'])->toBe(3)
        ->and($plan['status'])->toBe('ok');
});

test('dry run tidak mengubah apa pun', function () {
    $fund = legacyFund(2000000);
    legacyApproved($fund, $this->requester, $this->approver, 'pengeluaran', 500000);

    $exit = Artisan::call('treasury:apply-legacy-petty-cash');

    expect($exit)->toBe(0)
        ->and(Artisan::output())->toContain('Dry run')->toContain('Rp 1.500.000')
        ->and((float) $fund->fresh()->current_balance)->toEqual(2000000.0)
        ->and(PettyCashTransaction::unappliedApproved()->count())->toBe(1);
});

test('--apply menerapkan sekali saja, dan menjalankan ulang tidak menghitung dua kali', function () {
    $fund = legacyFund(2000000);
    legacyApproved($fund, $this->requester, $this->approver, 'pengeluaran', 500000);
    legacyApproved($fund, $this->requester, $this->approver, 'pengeluaran', 250000);

    expect(Artisan::call('treasury:apply-legacy-petty-cash', ['--apply' => true]))->toBe(0);
    expect((float) $fund->fresh()->current_balance)->toEqual(1250000.0)
        ->and(PettyCashTransaction::unappliedApproved()->count())->toBe(0);

    Artisan::call('treasury:apply-legacy-petty-cash', ['--apply' => true]);
    expect((float) $fund->fresh()->current_balance)->toEqual(1250000.0)
        ->and(Artisan::output())->toContain('Tidak ada transaksi kas kecil lama');
});

test('dana yang hasilnya negatif atau melewati plafon dilewati, dana lain tetap diterapkan', function () {
    $short = legacyFund(100000, 5000000, 'Kas Kurang');
    legacyApproved($short, $this->requester, $this->approver, 'pengeluaran', 300000);
    $over = legacyFund(900000, 1000000, 'Kas Lebih');
    legacyApproved($over, $this->requester, $this->approver, 'pengisian_ulang', 500000);
    $fine = legacyFund(1000000, 1000000, 'Kas Normal');
    legacyApproved($fine, $this->requester, $this->approver, 'pengeluaran', 100000);

    $exit = Artisan::call('treasury:apply-legacy-petty-cash', ['--apply' => true]);
    $output = Artisan::output();

    expect($exit)->toBe(1)
        ->and($output)->toContain('SALDO NEGATIF')->toContain('MELEBIHI PLAFON')
        ->and((float) $short->fresh()->current_balance)->toEqual(100000.0)
        ->and((float) $over->fresh()->current_balance)->toEqual(900000.0)
        ->and((float) $fine->fresh()->current_balance)->toEqual(900000.0)
        // The skipped funds' rows stay pending review.
        ->and(PettyCashTransaction::unappliedApproved()->count())->toBe(2);
});

test('--fund membatasi koreksi ke satu dana', function () {
    $a = legacyFund(1000000, 1000000, 'Dana A');
    $b = legacyFund(1000000, 1000000, 'Dana B');
    legacyApproved($a, $this->requester, $this->approver, 'pengeluaran', 100000);
    legacyApproved($b, $this->requester, $this->approver, 'pengeluaran', 200000);

    Artisan::call('treasury:apply-legacy-petty-cash', ['--apply' => true, '--fund' => $a->id]);

    expect((float) $a->fresh()->current_balance)->toEqual(900000.0)
        ->and((float) $b->fresh()->current_balance)->toEqual(1000000.0);
});

test('halaman kas kecil memperingatkan saldo yang belum memperhitungkan transaksi lama', function () {
    $fund = legacyFund(1000000, 1000000, 'Kas Peringatan');
    legacyApproved($fund, $this->requester, $this->approver, 'pengeluaran', 100000);

    $this->actingAs($this->approver)->get(route('treasury.petty-cash.index'))
        ->assertOk()
        ->assertSee('Saldo belum memperhitungkan 1 transaksi lama')
        ->assertSee('treasury:apply-legacy-petty-cash');

    Artisan::call('treasury:apply-legacy-petty-cash', ['--apply' => true]);
    $this->actingAs($this->approver)->get(route('treasury.petty-cash.index'))
        ->assertOk()
        ->assertDontSee('Saldo belum memperhitungkan');
});
