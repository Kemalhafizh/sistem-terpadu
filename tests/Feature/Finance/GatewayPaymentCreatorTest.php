<?php

use App\Models\Billing;
use App\Models\Classroom;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;

/*
 * Payments confirmed by the payment gateway used to be attributed to user #1.
 * They now have no creator, and the receipt says the payment was made online.
 */

beforeEach(function () {
    $this->firstUser = User::factory()->create(['role' => 'superadmin']);
    expect($this->firstUser->id)->toBe(1);
});

test('the Midtrans webhook records the payment without attributing it to user 1', function () {
    $billing = Billing::factory()->create(['amount' => 500000, 'remaining_balance' => 500000]);

    $this->postJson(route('api.midtrans.webhook'), [
        'is_simulation' => true,
        'order_id' => 'BILL-'.$billing->id.'-SIMULATE',
        'status_code' => '200',
        'gross_amount' => '500000.00',
        'transaction_status' => 'settlement',
        'payment_type' => 'qris',
        'signature_key' => 'simulated-signature-bypass',
    ])->assertOk();

    $payment = Payment::where('billing_id', $billing->id)->sole();
    expect($payment->created_by)->toBeNull()
        ->and($payment->note)->toContain('Midtrans')
        ->and($billing->fresh()->status)->toBe('paid');
});

test('the parent portal checkout callback does not attribute the payment to user 1', function () {
    $parent = User::factory()->create(['role' => 'parent']);
    $student = Student::factory()->create(['parent_id' => $parent->id, 'classroom_id' => Classroom::factory()->create()->id]);
    $billing = Billing::factory()->create(['student_id' => $student->id, 'amount' => 300000, 'remaining_balance' => 300000]);

    $this->actingAs($parent)->post(route('parent-portal.billings.callback', $billing->id))->assertRedirect();

    expect(Payment::where('billing_id', $billing->id)->sole()->created_by)->toBeNull();
});

test('the student checkout callback does not attribute the payment to user 1', function () {
    $studentUser = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create(['user_id' => $studentUser->id]);
    $billing = Billing::factory()->create(['student_id' => $student->id, 'amount' => 300000, 'remaining_balance' => 300000]);

    $this->actingAs($studentUser)->post(route('finance.mock-callback', $billing->id))->assertRedirect();

    expect(Payment::where('billing_id', $billing->id)->sole()->created_by)->toBeNull();
});

test('the receipt of an online payment names no officer', function () {
    $billing = Billing::factory()->create(['amount' => 500000]);
    $online = Payment::create([
        'billing_id' => $billing->id, 'created_by' => null, 'amount_paid' => 500000, 'payment_date' => '2026-09-10',
        'payment_method' => 'transfer', 'receipt_number' => 'MID-ABCD1234',
    ]);
    $counter = Payment::create([
        'billing_id' => $billing->id, 'created_by' => $this->firstUser->id, 'amount_paid' => 1000, 'payment_date' => '2026-09-10',
        'payment_method' => 'cash', 'receipt_number' => 'RCP-ABCD1234',
    ]);

    $html = view('finance.receipt-pdf', ['payment' => $online->load('billing.student.user', 'billing.category', 'creator')])->render();
    expect($html)->toContain('Dibayar online (dikonfirmasi payment gateway)')
        ->not->toContain('Admin Keuangan')
        ->not->toContain($this->firstUser->name);

    $html = view('finance.receipt-pdf', ['payment' => $counter->load('billing.student.user', 'billing.category', 'creator')])->render();
    expect($html)->toContain($this->firstUser->name);
});
