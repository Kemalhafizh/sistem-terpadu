<?php

use App\Models\AcademicYear;
use App\Models\Account;
use App\Models\Billing;
use App\Models\Classroom;
use App\Models\Payment;
use App\Models\PaymentCategory;
use App\Models\Student;
use App\Models\User;

beforeEach(function () {
    // 1. Setup roles
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->stafTu = User::factory()->create(['role' => 'staf_tu']);
    $this->kepsek = User::factory()->create(['role' => 'kepala_sekolah']);
    $this->studentUser = User::factory()->create(['role' => 'student']);
    
    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
    ]);

    // 2. Setup Academic & Classroom
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true, 'year' => '2026/2027']);
    $this->classroom = Classroom::factory()->create(['academic_year' => $this->academicYear->year]);
    $this->student->update(['classroom_id' => $this->classroom->id]);

    // 3. Setup Payment Category & Billings
    $this->category = PaymentCategory::factory()->create([
        'name' => 'SPP Bulanan',
        'default_amount' => 750000,
        'is_monthly' => true
    ]);

    $this->billing = Billing::factory()->create([
        'student_id' => $this->student->id,
        'payment_category_id' => $this->category->id,
        'academic_year_id' => $this->academicYear->id,
        'month' => 'Januari',
        'amount' => 750000,
        'remaining_balance' => 750000,
        'due_date' => '2026-01-31',
        'status' => 'unpaid',
    ]);
});

// =========================================================
// 🔒 AUTHORIZATION TESTS
// =========================================================

test('authorized users can view arrears report', function () {
    $response = $this->actingAs($this->stafTu)
        ->get(route('finance.reports.arrears'));

    $response->assertStatus(200);
    $response->assertViewHas('classrooms');
});

test('students cannot view arrears report', function () {
    $response = $this->actingAs($this->studentUser)
        ->get(route('finance.reports.arrears'));

    $response->assertStatus(403);
});

test('authorized users can view revenue projection', function () {
    $response = $this->actingAs($this->kepsek)
        ->get(route('finance.reports.projection'));

    $response->assertStatus(200);
    $response->assertViewHas('collectionRate');
});

test('students cannot view revenue projection', function () {
    $response = $this->actingAs($this->studentUser)
        ->get(route('finance.reports.projection'));

    $response->assertStatus(403);
});

test('authorized users can view monthly summary report', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('finance.reports.monthly'));

    $response->assertStatus(200);
    $response->assertViewHas('netIncome');
});

// =========================================================
// 📄 INVOICE & EXPORT TESTS
// =========================================================

test('authorized users can download billing PDF invoice', function () {
    $response = $this->actingAs($this->stafTu)
        ->get(route('finance.billings.invoice', $this->billing));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('authorized users can export arrears report to Excel', function () {
    $response = $this->actingAs($this->stafTu)
        ->get(route('finance.reports.export', [
            'type' => 'arrears',
            'format' => 'excel',
            'year_id' => $this->academicYear->id
        ]));

    $response->assertStatus(200);
    $response->assertHeader('content-disposition', 'attachment; filename=laporan_tunggakan_kelas_' . date('Ymd') . '.xlsx');
});

test('authorized users can export income statement to PDF', function () {
    // Setup revenue and expense accounts for double entry bookkeeping pdf export
    Account::create(['code' => '4010', 'name' => 'Pendapatan SPP', 'type' => 'revenue', 'normal_balance' => 'credit']);
    Account::create(['code' => '5010', 'name' => 'Beban Gaji', 'type' => 'expense', 'normal_balance' => 'debit']);

    $response = $this->actingAs($this->kepsek)
        ->get(route('finance.reports.export', [
            'type' => 'income_statement',
            'format' => 'pdf',
        ]));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});
