<?php

use App\Models\AcademicYear;
use App\Models\Billing;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\PaymentCategory;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\AccountingSeeder;

beforeEach(function () {
    // Jalankan seeder akun akuntansi
    $this->seed(AccountingSeeder::class);

    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $this->student = Student::factory()->create();

    // Kategori SPP Bulanan (akan dipetakan ke Pendapatan SPP - 4010)
    $this->sppCategory = PaymentCategory::factory()->create(['name' => 'SPP Bulanan']);

    // Kategori Seragam (akan dipetakan ke Pendapatan Seragam & Buku - 4030)
    $this->seragamCategory = PaymentCategory::factory()->create(['name' => 'Uang Seragam & Buku']);
});

test('creating billing triggers automatic Jurnal Piutang', function () {
    // 1. Buat tagihan baru (menyebabkan model event created memicu recordBillingRelease)
    $billing = Billing::create([
        'student_id' => $this->student->id,
        'payment_category_id' => $this->sppCategory->id,
        'academic_year_id' => $this->academicYear->id,
        'month' => 'Januari',
        'amount' => 600000,
        'remaining_balance' => 600000,
        'due_date' => now()->addDays(7)->toDateString(),
        'status' => 'unpaid',
    ]);

    // 2. Verifikasi JournalEntry dibuat di database
    $entry = JournalEntry::where('reference', 'BILL-'.$billing->id)->first();
    expect($entry)->not->toBeNull();
    expect($entry->description)->toContain('Pencatatan Piutang Tagihan SPP Bulanan');

    // 3. Verifikasi detail baris jurnal (Debit: 1020, Kredit: 4010)
    $lines = $entry->lines()->with('account')->get();
    expect($lines)->toHaveCount(2);

    $debitLine = $lines->where('debit', '>', 0)->first();
    $creditLine = $lines->where('credit', '>', 0)->first();

    expect($debitLine->account->code)->toBe('1020'); // Piutang Tagihan Siswa
    expect($debitLine->debit)->toEqual(600000);
    expect($debitLine->credit)->toEqual(0);

    expect($creditLine->account->code)->toBe('4010'); // Pendapatan SPP (karena nama mengandung SPP)
    expect($creditLine->debit)->toEqual(0);
    expect($creditLine->credit)->toEqual(600000);
});

test('creating payment triggers automatic Jurnal Penerimaan Kas', function () {
    // 1. Buat tagihan awal
    $billing = Billing::create([
        'student_id' => $this->student->id,
        'payment_category_id' => $this->seragamCategory->id,
        'academic_year_id' => $this->academicYear->id,
        'month' => 'Januari',
        'amount' => 450000,
        'remaining_balance' => 450000,
        'due_date' => now()->addDays(7)->toDateString(),
        'status' => 'unpaid',
    ]);

    // 2. Buat pembayaran (memicu recordBillingPayment via model event created)
    $payment = Payment::create([
        'billing_id' => $billing->id,
        'created_by' => $this->admin->id,
        'amount_paid' => 450000,
        'payment_date' => now()->toDateString(),
        'payment_method' => 'cash',
        'receipt_number' => 'AUTO-RCIPT-123',
        'note' => 'Pembayaran Buku Sekolah',
    ]);

    // 3. Verifikasi JournalEntry untuk Kas dibuat di database
    $entry = JournalEntry::where('reference', 'PAY-'.$payment->id)->first();
    expect($entry)->not->toBeNull();
    expect($entry->description)->toContain('Penerimaan Kas Pembayaran Uang Seragam & Buku');

    // 4. Verifikasi detail baris jurnal Kas (Debit: 1010, Kredit: 1020)
    $lines = $entry->lines()->with('account')->get();
    expect($lines)->toHaveCount(2);

    $debitLine = $lines->where('debit', '>', 0)->first();
    $creditLine = $lines->where('credit', '>', 0)->first();

    expect($debitLine->account->code)->toBe('1010'); // Kas & Setara Kas
    expect($debitLine->debit)->toEqual(450000);
    expect($debitLine->credit)->toEqual(0);

    expect($creditLine->account->code)->toBe('1020'); // Piutang Tagihan Siswa
    expect($creditLine->debit)->toEqual(0);
    expect($creditLine->credit)->toEqual(450000);
});

test('executive can view balanced financial reports', function () {
    // 1. Buat tagihan dan lakukan pembayaran parsial
    $billing = Billing::create([
        'student_id' => $this->student->id,
        'payment_category_id' => $this->sppCategory->id,
        'academic_year_id' => $this->academicYear->id,
        'month' => 'Februari',
        'amount' => 1000000,
        'remaining_balance' => 1000000,
        'due_date' => now()->addDays(7)->toDateString(),
        'status' => 'unpaid',
    ]);

    $payment = Payment::create([
        'billing_id' => $billing->id,
        'created_by' => $this->admin->id,
        'amount_paid' => 400000,
        'payment_date' => now()->toDateString(),
        'payment_method' => 'cash',
        'receipt_number' => 'AUTO-RCIPT-456',
        'note' => 'Cicilan SPP',
    ]);

    // 2. Akses halaman Laba Rugi
    $responseIncome = $this->actingAs($this->admin)->get(route('finance.reports.income'));
    $responseIncome->assertStatus(200);
    $responseIncome->assertViewHas('totalRevenue', 1000000); // 1M total revenue dirilis
    $responseIncome->assertViewHas('totalExpense', 0);
    $responseIncome->assertViewHas('netIncome', 1000000);

    // 3. Akses halaman Neraca Keuangan
    $responseBalance = $this->actingAs($this->admin)->get(route('finance.reports.balance'));
    $responseBalance->assertStatus(200);

    // Total Aset: Kas (400rb) + Piutang (600rb) = 1.000.000
    $responseBalance->assertViewHas('totalAssets', 1000000);
    // Total Pasiva: Kewajiban (0) + Ekuitas & Laba Rugi (1.000.000) = 1.000.000
    $responseBalance->assertViewHas('totalLiabilitiesAndEquity', 1000000);

    // Verifikasi Neraca seimbang sempurna
    $difference = abs($responseBalance->viewData('totalAssets') - $responseBalance->viewData('totalLiabilitiesAndEquity'));
    expect($difference)->toEqual(0);
});
