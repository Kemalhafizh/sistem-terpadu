<?php

/** @var TestCase|stdClass $this */

use App\Models\AcademicYear;
use App\Models\Billing;
use App\Models\PaymentCategory;
use App\Models\Student;
use App\Models\User;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);
});

test('admin can create a new billing for a student', function () {
    $student = Student::factory()->create();
    $category = PaymentCategory::factory()->create(['default_amount' => 1000000]);

    $response = $this->actingAs($this->admin)->post(route('finance.billings.store'), [
        'student_id' => $student->id,
        'payment_category_id' => $category->id,
        'amount' => 1000000,
        'due_date' => now()->addMonth()->format('Y-m-d'),
    ]);

    $response->assertRedirect(route('finance.billings.index'));
    $this->assertDatabaseHas('billings', [
        'student_id' => $student->id,
        'amount' => 1000000,
        'status' => 'unpaid',
    ]);
});

test('admin can record a payment and billing balance updates', function () {
    $billing = Billing::factory()->create([
        'amount' => 1000000,
        'remaining_balance' => 1000000,
        'status' => 'unpaid',
    ]);

    $response = $this->actingAs($this->admin)->post(route('finance.payments.store'), [
        'billing_id' => $billing->id,
        'amount_paid' => 400000,
        'payment_date' => now()->format('Y-m-d'),
        'payment_method' => 'cash',
    ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('payments', ['billing_id' => $billing->id, 'amount_paid' => 400000]);

    $billing->refresh();
    expect((int) $billing->remaining_balance)->toBe(600000);
    expect($billing->status)->toBe('partial');
});

test('billing status becomes paid when fully paid', function () {
    $billing = Billing::factory()->create([
        'amount' => 1000000,
        'remaining_balance' => 1000000,
        'status' => 'unpaid',
    ]);

    // Pay the full amount
    $this->actingAs($this->admin)->post(route('finance.payments.store'), [
        'billing_id' => $billing->id,
        'amount_paid' => 1000000,
        'payment_date' => now()->format('Y-m-d'),
        'payment_method' => 'transfer',
    ]);

    $billing->refresh();
    $this->assertDatabaseHas('payments', ['billing_id' => $billing->id, 'amount_paid' => 1000000]);
    expect((int) $billing->remaining_balance)->toBe(0);
    expect($billing->status)->toBe('paid');
});
