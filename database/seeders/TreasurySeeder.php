<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\Payment;
use App\Models\PettyCashFund;
use App\Models\PettyCashTransaction;
use App\Models\User;
use App\Models\VendorPayment;
use App\Services\Treasury\PettyCashService;
use App\Services\Treasury\TreasuryReconciliationService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Demo treasury data where every derived figure is computed by the real
 * services — nothing is written by hand.
 *
 * The previous version inserted a statement pointing at a non-existent
 * "dummy.csv" with "10 matched / 10 unmatched" typed in (the "matched" rows
 * matched no payment at all), and petty-cash funds whose balances contradicted
 * their own approved transactions.
 *
 * Now:
 *  - the bank statement is a real CSV built from the transfer payments
 *    FinanceSeeder recorded, imported and reconciled by
 *    TreasuryReconciliationService (so matched/unmatched are its results);
 *  - daily cash positions are calculated by the same service;
 *  - petty-cash funds start at 0 and only move through PettyCashService
 *    approvals, so balance = replenishments − approved spending.
 * Account numbers and vendor names are marked DEMO so they can't be mistaken
 * for the school's.
 */
class TreasurySeeder extends Seeder
{
    public function run(): void
    {
        $treasurer = User::whereIn('role', ['admin', 'superadmin'])->orderBy('id')->first()
            ?? User::factory()->create(['role' => 'admin', 'name' => 'Admin Keuangan (Demo)']);
        // Petty cash can't be approved by the person who requested it.
        $requester = User::where('role', 'staf_tu')->orderBy('id')->first()
            ?? User::factory()->create(['role' => 'staf_tu', 'name' => 'Staf TU (Demo)']);

        $primary = BankAccount::create([
            'bank_name' => 'BSI', 'account_number' => 'DEMO-0001', 'account_name' => school_name(),
            'account_type' => 'giro', 'current_balance' => 150000000, 'is_primary' => true,
        ]);
        BankAccount::create([
            'bank_name' => 'BRI', 'account_number' => 'DEMO-0002', 'account_name' => school_name(),
            'account_type' => 'tabungan', 'current_balance' => 25000000,
        ]);

        // Vendor payments first: the one already paid from the primary account
        // must also appear as a debit on that account's statement.
        $this->seedVendorPayments($primary, $treasurer);
        $this->seedStatement($primary, $treasurer);
        $this->seedPettyCash($treasurer, $requester);
    }

    /**
     * A statement of the primary account for the last 30 days: one credit per
     * transfer payment actually recorded in that window (half naming the
     * receipt, half only the amount), a debit for each vendor payment made
     * from this account, a monthly admin fee and an unidentified transfer —
     * the last two are what a real statement has and must stay unmatched.
     */
    private function seedStatement(BankAccount $account, User $treasurer): void
    {
        $from = now()->subDays(30)->startOfDay();
        $payments = Payment::where('payment_method', 'transfer')
            ->whereDate('payment_date', '>=', $from->toDateString())
            ->whereDate('payment_date', '<=', now()->toDateString())
            ->orderBy('payment_date')->orderBy('id')
            ->limit(30)
            ->get();

        $rows = [];
        foreach ($payments as $i => $payment) {
            $withReference = $i % 2 === 0;
            $rows[] = [
                'date' => Carbon::parse($payment->payment_date),
                'description' => $withReference ? "TRF SPP {$payment->receipt_number}" : 'TRANSFER MASUK',
                'reference' => $withReference ? $payment->receipt_number : '',
                'debit' => 0,
                'credit' => (float) $payment->amount_paid,
            ];
        }
        $vendorPayments = VendorPayment::where('status', 'paid')->where('bank_account_id', $account->id)
            ->whereDate('paid_date', '>=', $from->toDateString())->get();
        foreach ($vendorPayments as $vendor) {
            $rows[] = [
                'date' => Carbon::parse($vendor->paid_date),
                'description' => "TRF KE {$vendor->vendor_name}",
                'reference' => '',
                'debit' => (float) $vendor->amount,
                'credit' => 0,
            ];
        }
        $rows[] = ['date' => $from->copy()->addDays(29), 'description' => 'BIAYA ADMINISTRASI BULANAN', 'reference' => '', 'debit' => 12500, 'credit' => 0];
        $rows[] = ['date' => $from->copy()->addDays(15), 'description' => 'TRANSFER MASUK TANPA KETERANGAN', 'reference' => '', 'debit' => 0, 'credit' => 173250];
        usort($rows, fn ($a, $b) => $a['date'] <=> $b['date']);

        $balance = (float) $account->current_balance;
        $csv = ['tanggal;keterangan;referensi;debit;kredit;saldo'];
        foreach ($rows as $row) {
            $balance += $row['credit'] - $row['debit'];
            $csv[] = implode(';', [
                $row['date']->format('Y-m-d'),
                $row['description'],
                $row['reference'],
                $this->idr($row['debit']),
                $this->idr($row['credit']),
                $this->idr($balance),
            ]);
        }

        $path = 'bank-statements/demo-'.$account->id.'-'.now()->format('Ymd').'.csv';
        Storage::disk('local')->put($path, implode("\n", $csv)."\n");

        $service = app(TreasuryReconciliationService::class);
        $statement = $service->importBankStatement($account->id, $path, $treasurer->id);
        $service->autoReconcile($statement->id);

        for ($day = $statement->period_start->copy(); $day->lte($statement->period_end); $day->addDay()) {
            $service->calculateTreasuryPosition($account->id, $day->toDateString());
        }
    }

    private function seedPettyCash(User $treasurer, User $requester): void
    {
        $service = app(PettyCashService::class);

        $funds = [
            ['Kas Kecil TU (Demo)', 5000000, [
                ['atk', 'Kertas HVS & tinta printer', 425000, 'approved'],
                ['konsumsi', 'Konsumsi rapat wali kelas', 780000, 'approved'],
                ['fotocopy', 'Fotokopi soal ulangan', 215000, 'approved'],
                ['transportasi', 'Ongkos kirim berkas ke dinas', 60000, 'pending'],
                ['lainnya', 'Pembelian tanpa nota', 350000, 'rejected'],
            ]],
            ['Kas Kecil Laboratorium (Demo)', 2000000, [
                ['peralatan_kecil', 'Gelas ukur & pipet', 310000, 'approved'],
                ['kebersihan', 'Sabun & lap laboratorium', 95000, 'approved'],
                ['obat_p3k', 'Isi ulang kotak P3K', 140000, 'pending'],
            ]],
        ];

        foreach ($funds as [$name, $imprest, $spending]) {
            $fund = PettyCashFund::create([
                'fund_name' => $name, 'custodian_id' => $requester->id,
                'imprest_amount' => $imprest, 'current_balance' => 0, 'is_active' => true,
            ]);

            // Opening float: a replenishment up to the imprest, approved like any other.
            $service->approve($this->pettyTransaction($fund, $requester, 'pengisian_ulang', 'lainnya', 'Pengisian awal dana kas kecil', $imprest, 20), $treasurer->id);

            foreach ($spending as $i => [$category, $description, $amount, $outcome]) {
                $tx = $this->pettyTransaction($fund, $requester, 'pengeluaran', $category, $description, $amount, 15 - $i);
                if ($outcome === 'approved') {
                    $service->approve($tx, $treasurer->id);
                } elseif ($outcome === 'rejected') {
                    $service->reject($tx, $treasurer->id, 'Tidak ada nota pembelian.');
                }
            }
        }
    }

    private function pettyTransaction(PettyCashFund $fund, User $requester, string $type, string $category, string $description, float $amount, int $daysAgo): PettyCashTransaction
    {
        return PettyCashTransaction::create([
            'fund_id' => $fund->id, 'type' => $type, 'category' => $category, 'description' => $description,
            'amount' => $amount, 'transaction_date' => now()->subDays($daysAgo)->toDateString(),
            'approval_status' => 'pending', 'requested_by' => $requester->id,
        ]);
    }

    private function seedVendorPayments(BankAccount $account, User $treasurer): void
    {
        $scheduled = [
            ['PLN (Demo)', 'Tagihan listrik bulan berjalan', 8750000, 5],
            ['PDAM (Demo)', 'Tagihan air bulan berjalan', 1250000, 7],
            ['Penyedia Katering (Demo)', 'Katering kegiatan PHBI', 4500000, 12],
        ];
        foreach ($scheduled as [$vendor, $description, $amount, $dueInDays]) {
            VendorPayment::create([
                'vendor_name' => $vendor, 'description' => $description, 'amount' => $amount,
                'due_date' => now()->addDays($dueInDays)->toDateString(), 'payment_method' => 'transfer',
                'status' => 'scheduled', 'created_by' => $treasurer->id,
            ]);
        }

        VendorPayment::create([
            'vendor_name' => 'Jasa Kebersihan (Demo)', 'description' => 'Jasa kebersihan bulan lalu', 'amount' => 6000000,
            'due_date' => now()->subDays(10)->toDateString(), 'paid_date' => now()->subDays(12)->toDateString(),
            'payment_method' => 'transfer', 'status' => 'paid', 'bank_account_id' => $account->id,
            'created_by' => $treasurer->id, 'approved_by' => $treasurer->id,
        ]);
    }

    /** "1.250.000,00" — the format Indonesian bank statements use. */
    private function idr(float $amount): string
    {
        return number_format($amount, 2, ',', '.');
    }
}
