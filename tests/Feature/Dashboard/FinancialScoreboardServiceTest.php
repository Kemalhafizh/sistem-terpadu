<?php

use App\Models\AcademicYear;
use App\Models\BankAccount;
use App\Models\Billing;
use App\Models\Employee;
use App\Models\FinancialHealthMetric;
use App\Models\Payment;
use App\Models\PaymentCategory;
use App\Models\Payroll;
use App\Models\PettyCashFund;
use App\Models\PettyCashTransaction;
use App\Models\Student;
use App\Models\User;
use App\Models\VendorPayment;
use App\Services\Dashboard\FinancialScoreboardService;
use Carbon\Carbon;

/*
 * FinancialScoreboardService used to store fixed figures (cash Rp 1,25 M,
 * burn Rp 185 jt, projected Rp 210 jt, a fixed expense split) and, without
 * billing data, invent Rp 500 jt / 470 jt so collection read 94%.
 * "Today" is pinned so the periods are exact:
 *   burn-rate window = 2026-06-01 … 2026-08-31, next month = 2026-10.
 */
beforeEach(function () {
    $this->today = Carbon::parse('2026-09-26');
    $this->service = app(FinancialScoreboardService::class);
    $this->admin = User::factory()->create(['role' => 'admin']);
});

function scoreboardEmployee(string $number): Employee
{
    return Employee::create([
        'user_id' => User::factory()->create(['role' => 'guru'])->id,
        'employee_number' => $number,
        'position' => 'Guru',
        'employment_status' => 'gty',
        'hire_date' => '2020-01-01',
        'base_salary' => 4000000,
    ]);
}

function scoreboardPayroll(string $employeeNumber, string $status, ?string $paymentDate, float $net, string $month): void
{
    Payroll::create([
        'employee_id' => scoreboardEmployee($employeeNumber)->id,
        'month' => $month,
        'year' => '2026',
        'base_salary' => $net,
        'net_salary' => $net,
        'status' => $status,
        'payment_date' => $paymentDate,
    ]);
}

it('tidak mengarang angka ketika belum ada data keuangan', function () {
    $metric = $this->service->computeFinancialHealth($this->today);

    expect($metric->cash_reserve)->toBeNull()
        ->and($metric->monthly_burn_rate)->toBeNull()
        ->and($metric->projected_revenue)->toBeNull()
        // Was 94% from invented Rp 470 jt / Rp 500 jt.
        ->and($metric->fee_collection_rate)->toBeNull()
        ->and($metric->expense_distribution)->toBe([])
        ->and($metric->details['runway_months'])->toBeNull()
        ->and($metric->details['collected_this_month'])->toEqual(0); // JSON round-trip: 0.0 comes back as 0
});

it('menghitung setiap metrik dari transaksi nyata', function () {
    // Cash: only active registered accounts.
    BankAccount::create(['bank_name' => 'BSI', 'account_number' => '1', 'account_name' => 'YBWSA', 'account_type' => 'giro', 'current_balance' => 100000000]);
    BankAccount::create(['bank_name' => 'BSI', 'account_number' => '2', 'account_name' => 'YBWSA', 'account_type' => 'tabungan', 'current_balance' => 50000000]);
    BankAccount::create(['bank_name' => 'BRI', 'account_number' => '3', 'account_name' => 'Lama', 'account_type' => 'giro', 'current_balance' => 999000000, 'is_active' => false]);

    // Payroll: only paid inside the window counts.
    scoreboardPayroll('P-1', 'paid', '2026-07-25', 30000000, '07');
    scoreboardPayroll('P-2', 'paid', '2026-09-02', 99000000, '09');   // current month
    scoreboardPayroll('P-3', 'approved', '2026-07-25', 50000000, '07'); // not paid out

    // Vendor: paid on the last day of the window (edge) counts; scheduled doesn't.
    VendorPayment::create(['vendor_name' => 'CV Listrik', 'description' => 'Instalasi', 'amount' => 15000000, 'due_date' => '2026-08-31', 'paid_date' => '2026-08-31', 'payment_method' => 'transfer', 'status' => 'paid', 'created_by' => $this->admin->id]);
    VendorPayment::create(['vendor_name' => 'CV Atap', 'description' => 'Renovasi', 'amount' => 70000000, 'due_date' => '2026-08-15', 'payment_method' => 'transfer', 'status' => 'scheduled', 'created_by' => $this->admin->id]);

    // Petty cash: approved spending on the first day (edge) counts; pending and top-ups don't.
    $fund = PettyCashFund::create(['fund_name' => 'Kas TU', 'custodian_id' => $this->admin->id, 'imprest_amount' => 5000000]);
    $petty = fn (array $attrs) => PettyCashTransaction::create(array_merge([
        'fund_id' => $fund->id, 'description' => 'uji', 'category' => 'atk', 'requested_by' => $this->admin->id,
    ], $attrs));
    $petty(['type' => 'pengeluaran', 'amount' => 3000000, 'transaction_date' => '2026-06-01', 'approval_status' => 'approved']);
    $petty(['type' => 'pengeluaran', 'amount' => 1000000, 'transaction_date' => '2026-07-01', 'approval_status' => 'pending']);
    $petty(['type' => 'pengisian_ulang', 'amount' => 10000000, 'transaction_date' => '2026-07-01', 'approval_status' => 'approved']);

    // Billing for the active year.
    $year = AcademicYear::factory()->create(['year' => '2026/2027', 'is_active' => true]);
    $category = PaymentCategory::factory()->create(['name' => 'SPP Bulanan']);
    $student = Student::factory()->create();
    $bill = fn (float $amount, string $due) => Billing::create([
        'student_id' => $student->id, 'payment_category_id' => $category->id, 'academic_year_id' => $year->id,
        'month' => 'x', 'amount' => $amount, 'remaining_balance' => $amount, 'due_date' => $due, 'status' => 'unpaid',
    ]);
    $partial = $bill(1000000, '2026-10-10');
    $paid = $bill(500000, '2026-10-15');
    $bill(2000000, '2026-09-10');
    $pay = function (Billing $billing, float $amount, string $date) {
        Payment::create(['billing_id' => $billing->id, 'created_by' => $this->admin->id, 'amount_paid' => $amount, 'payment_date' => $date, 'payment_method' => 'cash', 'receipt_number' => uniqid('RCP-')]);
        $billing->refresh()->updateStatus();
    };
    $pay($partial, 400000, '2026-09-05');
    $pay($paid, 500000, '2026-08-20');

    $metric = $this->service->computeFinancialHealth($this->today);
    $details = $metric->details;

    expect((float) $metric->cash_reserve)->toBe(150000000.0)
        ->and($details['cash_reserve']['accounts'])->toBe(2);

    // (30 jt + 15 jt + 3 jt) / 3 months
    expect((float) $metric->monthly_burn_rate)->toBe(16000000.0)
        ->and($details['burn_rate']['period_start'])->toBe('2026-06-01')
        ->and($details['burn_rate']['period_end'])->toBe('2026-08-31')
        ->and($details['burn_rate']['totals'])->toEqual(['gaji_honor' => 30000000.0, 'pembayaran_vendor' => 15000000.0, 'kas_kecil' => 3000000.0])
        ->and($metric->expense_distribution)->toBe(['gaji_honor' => 62.5, 'pembayaran_vendor' => 31.3, 'kas_kecil' => 6.3])
        ->and($details['runway_months'])->toBe(9.4);

    // October bills: 600 rb still owed on the partial one; the paid one adds nothing.
    expect((float) $metric->projected_revenue)->toBe(600000.0)
        ->and($details['projected_revenue'])->toBe(['month' => '2026-10', 'billings_due' => 2]);

    // Collected 900 rb of 3,5 jt billed this academic year.
    expect((float) $metric->fee_collection_rate)->toBe(25.71)
        ->and($details['fee_collection']['scope'])->toBe('active_year')
        ->and($details['fee_collection']['billed'])->toEqual(3500000)
        ->and($details['fee_collection']['collected'])->toEqual(900000)
        ->and($details['collected_this_month'])->toEqual(400000);
});

it('memperbarui satu snapshot per hari, bukan menambah baris setiap kali dibuka', function () {
    $this->service->computeFinancialHealth($this->today);
    BankAccount::create(['bank_name' => 'BSI', 'account_number' => '1', 'account_name' => 'YBWSA', 'account_type' => 'giro', 'current_balance' => 10000000]);
    $second = $this->service->computeFinancialHealth($this->today);

    expect(FinancialHealthMetric::count())->toBe(1)
        ->and((float) $second->cash_reserve)->toBe(10000000.0);
});

it('halaman scoreboard tidak lagi menampilkan vonis tetap', function () {
    $response = $this->actingAs($this->admin)->get(route('dashboards.financial-scoreboard.index'));

    $response->assertOk()
        ->assertSee('Belum ada data')
        ->assertDontSee('Kondisi Likuiditas Kuat')
        ->assertDontSee('Surplus Operasional Terkendali')
        ->assertDontSee('Target &gt; 90% Tercapai', false)
        ->assertDontSee('1.250.000.000');
});
