<?php

use App\Models\BankAccount;
use App\Models\BankStatement;
use App\Models\BankTransaction;
use App\Models\Billing;
use App\Models\Payment;
use App\Models\User;
use App\Services\Treasury\TreasuryReconciliationService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'admin']);
    $this->account = BankAccount::create([
        'bank_name' => 'BSI', 'account_number' => '7123456789', 'account_name' => 'Operasional', 'account_type' => 'giro',
    ]);
    $this->service = app(TreasuryReconciliationService::class);
});

/** @param list<array<string, mixed>> $transactions */
function reconStatement(BankAccount $account, User $user, array $transactions): BankStatement
{
    $dates = array_column($transactions, 'transaction_date') ?: ['2026-09-01'];
    $statement = BankStatement::create([
        'bank_account_id' => $account->id,
        'statement_date' => max($dates),
        'period_start' => min($dates),
        'period_end' => max($dates),
        'file_path' => 'bank-statements/test.csv',
        'total_transactions' => count($transactions),
        'imported_by' => $user->id,
    ]);
    foreach ($transactions as $tx) {
        $statement->transactions()->create($tx + ['description' => 'Mutasi']);
    }

    return $statement;
}

function reconPayment(float $amount, string $date, string $method = 'transfer', ?string $receipt = null, ?Billing $billing = null): Payment
{
    return Payment::factory()->create(array_filter([
        'billing_id' => ($billing ?? Billing::factory()->create(['amount' => $amount]))->id,
        'amount_paid' => $amount,
        'payment_date' => $date,
        'payment_method' => $method,
        'receipt_number' => $receipt,
    ], fn ($v) => $v !== null));
}

test('auto reconcile matches credits to real payments and stores the real counts', function () {
    $spp = reconPayment(1250000, '2026-09-10');
    $uangGedung = reconPayment(800000, '2026-09-12');
    $statement = reconStatement($this->account, $this->user, [
        ['transaction_date' => '2026-09-11', 'credit' => 1250000],
        ['transaction_date' => '2026-09-12', 'credit' => 800000],
        ['transaction_date' => '2026-09-12', 'credit' => 300000],
        ['transaction_date' => '2026-09-12', 'debit' => 6500, 'description' => 'Biaya admin'],
    ]);

    $result = $this->service->autoReconcile($statement->id);

    expect($result)->toBe(['total' => 4, 'matched' => 2, 'unmatched' => 2])
        ->and($result)->not->toBe(['matched' => 5, 'unmatched' => 5]);

    $statement->refresh();
    expect($statement->matched_count)->toBe(2)
        ->and($statement->unmatched_count)->toBe(2)
        ->and($statement->total_transactions)->toBe(4)
        ->and($statement->status)->toBe('has_discrepancies');

    $txs = $statement->transactions()->orderBy('id')->get();
    expect($txs[0]->match_status)->toBe('auto_matched')
        ->and($txs[0]->matched_billing_id)->toBe($spp->billing_id)
        ->and($txs[0]->matched_payment_id)->toBe($spp->id)
        ->and($txs[1]->matched_payment_id)->toBe($uangGedung->id)
        ->and($txs[2]->match_status)->toBe('unmatched')
        ->and($txs[2]->match_notes)->toContain('Tidak ada pembayaran')
        ->and($txs[3]->match_status)->toBe('unmatched');
});

test('a statement whose every transaction matches is reconciled', function () {
    reconPayment(450000, '2026-09-03');
    $statement = reconStatement($this->account, $this->user, [
        ['transaction_date' => '2026-09-03', 'credit' => 450000],
    ]);

    expect($this->service->autoReconcile($statement->id))->toBe(['total' => 1, 'matched' => 1, 'unmatched' => 0])
        ->and($statement->fresh()->status)->toBe('reconciled');
});

test('an empty statement reports zero instead of a fixed figure', function () {
    $statement = reconStatement($this->account, $this->user, []);

    expect($this->service->autoReconcile($statement->id))->toBe(['total' => 0, 'matched' => 0, 'unmatched' => 0])
        ->and($statement->fresh()->status)->toBe('imported');
});

test('payments outside the date window, cash payments and different amounts are not matched', function () {
    reconPayment(500000, '2026-09-01');            // 4 days away, window is 3
    reconPayment(500000, '2026-09-05', 'cash');    // counter cash is not credited one by one
    reconPayment(499999, '2026-09-05');            // one rupiah off
    $statement = reconStatement($this->account, $this->user, [
        ['transaction_date' => '2026-09-05', 'credit' => 500000],
    ]);

    expect($this->service->autoReconcile($statement->id))->toBe(['total' => 1, 'matched' => 0, 'unmatched' => 1])
        ->and($statement->transactions()->first()->matched_billing_id)->toBeNull();
});

test('one payment never explains two bank credits, even across runs', function () {
    reconPayment(750000, '2026-09-08');
    $statement = reconStatement($this->account, $this->user, [
        ['transaction_date' => '2026-09-08', 'credit' => 750000],
        ['transaction_date' => '2026-09-08', 'credit' => 750000],
    ]);

    expect($this->service->autoReconcile($statement->id))->toBe(['total' => 2, 'matched' => 1, 'unmatched' => 1])
        ->and($this->service->autoReconcile($statement->id))->toBe(['total' => 2, 'matched' => 1, 'unmatched' => 1]);
});

test('equally close candidates are left for manual matching instead of guessed', function () {
    reconPayment(600000, '2026-09-09');
    reconPayment(600000, '2026-09-11');
    $statement = reconStatement($this->account, $this->user, [
        ['transaction_date' => '2026-09-10', 'credit' => 600000],
    ]);

    $this->service->autoReconcile($statement->id);

    $tx = $statement->transactions()->first();
    expect($tx->match_status)->toBe('unmatched')
        ->and($tx->match_notes)->toContain('2 pembayaran');
});

test('the closest payment wins when it is the only one that close', function () {
    reconPayment(600000, '2026-09-07');
    $closest = reconPayment(600000, '2026-09-10');
    $statement = reconStatement($this->account, $this->user, [
        ['transaction_date' => '2026-09-10', 'credit' => 600000],
    ]);

    $this->service->autoReconcile($statement->id);

    expect($statement->transactions()->first()->matched_payment_id)->toBe($closest->id);
});

test('a receipt number on the bank line matches even outside the date window', function () {
    $payment = reconPayment(350000, '2026-08-15', 'cash', 'RCP-AB12CD34');
    $statement = reconStatement($this->account, $this->user, [
        ['transaction_date' => '2026-09-10', 'credit' => 350000, 'description' => 'SETOR TUNAI RCP-AB12CD34'],
    ]);

    expect($this->service->autoReconcile($statement->id)['matched'])->toBe(1)
        ->and($statement->transactions()->first()->matched_payment_id)->toBe($payment->id);
});

test('a referenced receipt with a different amount is flagged as disputed', function () {
    reconPayment(350000, '2026-09-10', 'transfer', 'RCP-ZZ998877');
    $statement = reconStatement($this->account, $this->user, [
        ['transaction_date' => '2026-09-10', 'credit' => 305000, 'reference_number' => 'RCP-ZZ998877'],
    ]);

    expect($this->service->autoReconcile($statement->id))->toBe(['total' => 1, 'matched' => 0, 'unmatched' => 1]);
    $tx = $statement->transactions()->first();
    expect($tx->match_status)->toBe('disputed')
        ->and($tx->match_notes)->toContain('nominal berbeda')
        ->and($this->service->getDiscrepancies($statement->id))->toHaveCount(1);
});

test('a Midtrans order id on the bank line matches the payment of that billing', function () {
    $billing = Billing::factory()->create(['amount' => 1500000]);
    $payment = reconPayment(1500000, '2026-08-20', 'transfer', null, $billing);
    reconPayment(1500000, '2026-09-10'); // same amount and date, but a different billing
    $statement = reconStatement($this->account, $this->user, [
        ['transaction_date' => '2026-09-10', 'credit' => 1500000, 'reference_number' => "BILL-{$billing->id}-1757480000"],
    ]);

    $this->service->autoReconcile($statement->id);

    $tx = $statement->transactions()->first();
    expect($tx->matched_billing_id)->toBe($billing->id)
        ->and($tx->matched_payment_id)->toBe($payment->id);
});

test('manual matches are kept and counted, and manual matching refreshes the counts', function () {
    $billing = Billing::factory()->create();
    $statement = reconStatement($this->account, $this->user, [
        ['transaction_date' => '2026-09-10', 'credit' => 100000, 'match_status' => 'manual_matched', 'matched_billing_id' => $billing->id],
        ['transaction_date' => '2026-09-10', 'credit' => 222222],
    ]);

    expect($this->service->autoReconcile($statement->id))->toBe(['total' => 2, 'matched' => 1, 'unmatched' => 1]);

    $open = $statement->transactions()->where('match_status', 'unmatched')->first();
    $this->service->manualMatch($open->id, $billing->id, $this->user->id);

    $statement->refresh();
    expect($statement->matched_count)->toBe(2)
        ->and($statement->unmatched_count)->toBe(0)
        ->and($statement->status)->toBe('reconciled')
        ->and($open->fresh()->matched_by)->toBe($this->user->id);
});

test('import reads the CSV, takes the period from its dates and records the logged-in user', function () {
    Storage::fake('local');
    Storage::disk('local')->put('bank-statements/agustus.csv', implode("\n", [
        'Tanggal;Keterangan;Referensi;Debit;Kredit;Saldo',
        '05/08/2026;TRF SPP;;;1.250.000,00;11.250.000,00',
        '03/08/2026;SETORAN;;;Rp 500.000;10.000.000,00',
        '04/08/2026;BIAYA ADM;;6.500,00;;9.993.500,00',
    ]));
    $this->actingAs($this->user);

    $statement = $this->service->importBankStatement($this->account->id, 'bank-statements/agustus.csv');

    expect($statement->imported_by)->toBe($this->user->id)
        ->and($statement->period_start->toDateString())->toBe('2026-08-03')
        ->and($statement->period_end->toDateString())->toBe('2026-08-05')
        ->and($statement->statement_date->toDateString())->toBe('2026-08-05')
        ->and($statement->total_transactions)->toBe(3);

    $txs = $statement->transactions()->orderBy('transaction_date')->get();
    expect((float) $txs[0]->credit)->toBe(500000.0)
        ->and((float) $txs[1]->debit)->toBe(6500.0)
        ->and((float) $txs[2]->credit)->toBe(1250000.0)
        ->and((float) $txs[2]->balance)->toBe(11250000.0);
});

test('import without a logged-in user or explicit importer fails instead of using user 1', function () {
    Storage::fake('local');
    Storage::disk('local')->put('bank-statements/a.csv', "tanggal,debit,kredit\n2026-08-01,,100000");
    expect(User::find(1))->not->toBeNull();

    expect(fn () => $this->service->importBankStatement($this->account->id, 'bank-statements/a.csv'))
        ->toThrow(AuthenticationException::class);
    expect(BankStatement::count())->toBe(0);
});

test('import accepts an explicit importer when nobody is logged in', function () {
    Storage::fake('local');
    Storage::disk('local')->put('bank-statements/a.csv', "tanggal,debit,kredit\n2026-08-01,,100000");
    $importer = User::factory()->create();

    $statement = $this->service->importBankStatement($this->account->id, 'bank-statements/a.csv', $importer->id);

    expect($statement->imported_by)->toBe($importer->id);
});

test('import refuses a statement overlapping one already imported', function () {
    Storage::fake('local');
    Storage::disk('local')->put('bank-statements/a.csv', "tanggal,debit,kredit\n2026-08-01,,100000\n2026-08-10,,5000");
    Storage::disk('local')->put('bank-statements/b.csv', "tanggal,debit,kredit\n2026-08-10,,5000");
    $this->service->importBankStatement($this->account->id, 'bank-statements/a.csv', $this->user->id);

    expect(fn () => $this->service->importBankStatement($this->account->id, 'bank-statements/b.csv', $this->user->id))
        ->toThrow(InvalidArgumentException::class, 'tumpang tindih');
});

test('uploading a statement through the form imports it and reconciles it', function () {
    Storage::fake('local');
    $payment = reconPayment(1250000, '2026-09-10');
    $csv = "tanggal,keterangan,referensi,debit,kredit,saldo\n"
        ."2026-09-10,TRF SPP,,,1250000,1250000\n"
        ."2026-09-10,TRF TIDAK DIKENAL,,,99000,1349000\n";

    $response = $this->actingAs($this->user)->post(route('treasury.reconciliation.processImport'), [
        'bank_account_id' => $this->account->id,
        'file' => UploadedFile::fake()->createWithContent('mutasi.csv', $csv),
    ]);

    $statement = BankStatement::sole();
    $response->assertRedirect(route('treasury.reconciliation.review', $statement->id));
    expect($statement->imported_by)->toBe($this->user->id)
        ->and($statement->file_path)->not->toBe('dummy/path.csv')
        ->and($statement->matched_count)->toBe(1)
        ->and($statement->unmatched_count)->toBe(1);
    Storage::disk('local')->assertExists($statement->file_path);
    expect(BankTransaction::where('matched_payment_id', $payment->id)->count())->toBe(1);

    $this->get(route('treasury.reconciliation.review', $statement->id))
        ->assertOk()
        ->assertSee('Cocok (Otomatis)')
        ->assertSee('Belum Cocok')
        ->assertSee('2 transaksi, 1 cocok, 1 belum cocok');
    $this->get(route('treasury.reconciliation.index'))->assertOk()->assertSee('Ada Selisih');
});

test('an unreadable CSV is rejected with a message', function () {
    Storage::fake('local');

    $this->actingAs($this->user)
        ->from(route('treasury.reconciliation.import'))
        ->post(route('treasury.reconciliation.processImport'), [
            'bank_account_id' => $this->account->id,
            'file' => UploadedFile::fake()->createWithContent('mutasi.csv', "nama,jumlah\nA,1000\n"),
        ])
        ->assertRedirect(route('treasury.reconciliation.import'))
        ->assertSessionHasErrors('file');

    expect(BankStatement::count())->toBe(0);
});
