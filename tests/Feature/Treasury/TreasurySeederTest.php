<?php

use App\Models\BankStatement;
use App\Models\BankTransaction;
use App\Models\Billing;
use App\Models\DailyCashPosition;
use App\Models\Payment;
use App\Models\PettyCashFund;
use App\Models\PettyCashTransaction;
use App\Models\User;
use Database\Seeders\TreasurySeeder;
use Illuminate\Support\Facades\Storage;

/*
 * TreasurySeeder used to insert a statement pointing at a non-existent
 * "dummy.csv" with "10 matched / 10 unmatched" typed in — the "matched" rows
 * matched no payment — and petty-cash balances that contradicted their own
 * approved transactions. Every derived figure must now come from the services.
 */
beforeEach(function () {
    Storage::fake('local');
    User::factory()->create(['role' => 'admin']);
    User::factory()->create(['role' => 'staf_tu']);

    // Transfer payments FinanceSeeder would have recorded, inside the 30-day window.
    // One student/year for all bills (AcademicYearFactory's unique years run out fast).
    $shared = Billing::factory()->create(['amount' => 1]);
    $bill = fn (float $amount) => Billing::factory()->create([
        'amount' => $amount, 'student_id' => $shared->student_id,
        'academic_year_id' => $shared->academic_year_id, 'payment_category_id' => $shared->payment_category_id,
    ]);
    $this->payments = collect([
        [650000, 3, 'KW-TEST-00001'],
        [650000, 6, 'KW-TEST-00002'],
        [475000, 9, 'KW-TEST-00003'],
        [812500, 12, 'KW-TEST-00004'],
    ])->map(fn ($p) => Payment::factory()->create([
        'billing_id' => $bill($p[0])->id,
        'amount_paid' => $p[0],
        'payment_date' => now()->subDays($p[1])->toDateString(),
        'payment_method' => 'transfer',
        'receipt_number' => $p[2],
    ]));
    // Cash is never on a bank statement.
    Payment::factory()->create(['billing_id' => $bill(300000)->id, 'payment_method' => 'cash', 'payment_date' => now()->subDays(2)->toDateString()]);

    $this->seed(TreasurySeeder::class);
    $this->statement = BankStatement::with('transactions')->sole();
});

test('rekening koran adalah file CSV nyata, bukan dummy.csv', function () {
    expect($this->statement->file_path)->not->toBe('dummy.csv');
    Storage::disk('local')->assertExists($this->statement->file_path);

    // 4 transfer payments + 1 paid vendor + admin fee + unidentified transfer.
    expect($this->statement->transactions)->toHaveCount(7);
});

test('jumlah cocok/belum cocok adalah hasil rekonsiliasi, bukan angka tulisan tangan', function () {
    $statuses = $this->statement->transactions->pluck('match_status');
    $matched = $statuses->filter(fn ($s) => in_array($s, ['auto_matched', 'manual_matched'], true))->count();

    expect($this->statement->total_transactions)->toBe(7)
        ->and($this->statement->matched_count)->toBe($matched)
        ->and($this->statement->unmatched_count)->toBe(7 - $matched)
        ->and($this->statement->matched_count)->not->toBe(10);

    // Every "matched" line really points at a payment of the same amount.
    foreach ($this->statement->transactions->where('match_status', 'auto_matched') as $tx) {
        $payment = Payment::findOrFail($tx->matched_payment_id);
        expect((float) $payment->amount_paid)->toEqual((float) $tx->credit);
    }

    // Lines naming a receipt are matched to exactly that payment.
    foreach ($this->payments->filter(fn ($p, $i) => $i % 2 === 0) as $payment) {
        expect(BankTransaction::where('matched_payment_id', $payment->id)->exists())->toBeTrue();
    }

    // The admin fee and the unidentified transfer have no payment behind them.
    foreach (['BIAYA ADMINISTRASI BULANAN', 'TRANSFER MASUK TANPA KETERANGAN'] as $description) {
        expect($this->statement->transactions->firstWhere('description', $description)->match_status)->toBe('unmatched');
    }
});

test('posisi kas harian dihitung dari mutasi, dan saldo akhir konsisten', function () {
    $account = $this->statement->account;
    $last = DailyCashPosition::where('bank_account_id', $account->id)->orderByDesc('position_date')->first();
    $net = $this->statement->transactions->sum(fn ($tx) => (float) $tx->credit - (float) $tx->debit);

    expect(DailyCashPosition::where('bank_account_id', $account->id)->count())
        ->toBe((int) $this->statement->period_start->diffInDays($this->statement->period_end) + 1)
        ->and((float) $last->closing_balance)->toEqual((float) $account->current_balance + $net);
});

test('saldo kas kecil = pengisian − pengeluaran yang disetujui, lewat persetujuan sungguhan', function () {
    foreach (PettyCashFund::all() as $fund) {
        $approved = PettyCashTransaction::where('fund_id', $fund->id)->where('approval_status', 'approved');
        $refills = (float) (clone $approved)->where('type', 'pengisian_ulang')->sum('amount');
        $spending = (float) (clone $approved)->where('type', 'pengeluaran')->sum('amount');

        expect((float) $fund->current_balance)->toEqual($refills - $spending)
            ->and((float) $fund->current_balance)->toBeLessThanOrEqual((float) $fund->imprest_amount);
    }

    // Nothing approved outside the service, and the approver is never the requester.
    expect(PettyCashTransaction::unappliedApproved()->count())->toBe(0);
    PettyCashTransaction::where('approval_status', 'approved')->get()
        ->each(fn ($tx) => expect($tx->approved_by)->not->toBe($tx->requested_by));
    expect(PettyCashTransaction::where('approval_status', 'rejected')->whereNull('rejection_reason')->count())->toBe(0);
});
