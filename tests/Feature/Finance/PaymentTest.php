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
    $this->student = Student::factory()->create();
    $this->category = PaymentCategory::factory()->create();
});

test('admin can store a valid payment', function () {
    $billing = Billing::factory()->create([
        'student_id' => $this->student->id,
        'payment_category_id' => $this->category->id,
        'academic_year_id' => $this->academicYear->id,
        'amount' => 1000000,
        'remaining_balance' => 1000000,
        'status' => 'unpaid',
    ]);

    $response = $this->actingAs($this->admin)->post(route('finance.payments.store'), [
        'billing_id' => $billing->id,
        'amount_paid' => 500000,
        'payment_date' => now()->format('Y-m-d'),
        'payment_method' => 'cash',
    ]);

    $response->assertStatus(302);

    $this->assertDatabaseHas('payments', [
        'billing_id' => $billing->id,
        'amount_paid' => 500000,
    ]);

    $billing->refresh();
    expect($billing->remaining_balance)->toEqual(500000);
    expect($billing->status)->toBe('partial');
});

test('admin cannot pay more than remaining balance', function () {
    $billing = Billing::factory()->create([
        'amount' => 1000000,
        'remaining_balance' => 1000000,
        'status' => 'unpaid',
    ]);

    $response = $this->actingAs($this->admin)->post(route('finance.payments.store'), [
        'billing_id' => $billing->id,
        'amount_paid' => 1500000, // More than 1M
        'payment_date' => now()->format('Y-m-d'),
        'payment_method' => 'cash',
    ]);

    $response->assertStatus(422);

    $this->assertDatabaseCount('payments', 0);

    $billing->refresh();
    expect($billing->remaining_balance)->toEqual(1000000);
});
