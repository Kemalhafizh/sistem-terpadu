<?php

use App\Models\AcademicYear;
use App\Models\Billing;
use App\Models\PaymentCategory;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    // Buat User dengan role student dan hubungkan ke model Student
    $this->user = User::factory()->create(['role' => 'student']);
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $this->student = Student::factory()->create([
        'user_id' => $this->user->id,
    ]);
    $this->category = PaymentCategory::factory()->create(['name' => 'SPP Bulanan']);
});

test('student can checkout and get snap token', function () {
    $billing = Billing::factory()->create([
        'student_id' => $this->student->id,
        'payment_category_id' => $this->category->id,
        'academic_year_id' => $this->academicYear->id,
        'amount' => 500000,
        'remaining_balance' => 500000,
        'status' => 'unpaid',
    ]);

    // Set server key dummy dan mock request ke Midtrans
    Config::set('services.midtrans.server_key', 'SB-Mid-server-DUMMYKEY');

    Http::fake([
        'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
            'token' => 'TKN-MIDTRANS-REAL-12345',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/TKN-MIDTRANS-REAL-12345',
        ], 200),
    ]);

    $response = $this->actingAs($this->user)->get(route('finance.student.checkout', $billing->id));

    $response->assertStatus(200);
    $response->assertViewHas('billing');

    $billing->refresh();
    expect($billing->snap_token)->toBe('TKN-MIDTRANS-REAL-12345');
    expect($billing->payment_url)->toBe('https://app.sandbox.midtrans.com/snap/v2/vtweb/TKN-MIDTRANS-REAL-12345');
});

test('checkout fallback works when server key is empty', function () {
    $billing = Billing::factory()->create([
        'student_id' => $this->student->id,
        'payment_category_id' => $this->category->id,
        'academic_year_id' => $this->academicYear->id,
        'amount' => 500000,
        'remaining_balance' => 500000,
        'status' => 'unpaid',
    ]);

    // Kosongkan server key untuk memaksa fallback
    Config::set('services.midtrans.server_key', '');

    $response = $this->actingAs($this->user)->get(route('finance.student.checkout', $billing->id));

    $response->assertStatus(200);

    $billing->refresh();
    expect($billing->snap_token)->toStartWith('TKN-DEMO-');
    expect($billing->payment_url)->toBe(route('finance.mock-gateway', $billing->id));
});

test('webhook simulator can confirm payment successfully', function () {
    $billing = Billing::factory()->create([
        'student_id' => $this->student->id,
        'payment_category_id' => $this->category->id,
        'academic_year_id' => $this->academicYear->id,
        'amount' => 500000,
        'remaining_balance' => 500000,
        'status' => 'unpaid',
        'snap_token' => 'TKN-DEMO-1234567890',
    ]);

    $payload = [
        'is_simulation' => true,
        'order_id' => 'BILL-'.$billing->id.'-SIMULATE',
        'status_code' => '200',
        'gross_amount' => '500000.00',
        'transaction_status' => 'settlement',
        'payment_type' => 'qris',
        'signature_key' => 'simulated-signature-bypass',
    ];

    $response = $this->postJson(route('api.midtrans.webhook'), $payload);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'processed' => true,
        ]);

    $billing->refresh();
    expect($billing->status)->toBe('paid');
    expect($billing->remaining_balance)->toEqual(0);

    $this->assertDatabaseHas('payments', [
        'billing_id' => $billing->id,
        'amount_paid' => 500000,
        'payment_method' => 'transfer',
    ]);
});

test('webhook simulator handles expire status correctly', function () {
    $billing = Billing::factory()->create([
        'student_id' => $this->student->id,
        'payment_category_id' => $this->category->id,
        'academic_year_id' => $this->academicYear->id,
        'amount' => 500000,
        'remaining_balance' => 500000,
        'status' => 'unpaid',
        'snap_token' => 'TKN-DEMO-1234567890',
        'payment_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/TKN-DEMO-1234567890',
    ]);

    $payload = [
        'is_simulation' => true,
        'order_id' => 'BILL-'.$billing->id.'-SIMULATE',
        'status_code' => '407',
        'gross_amount' => '500000.00',
        'transaction_status' => 'expire',
        'payment_type' => 'qris',
        'signature_key' => 'simulated-signature-bypass',
    ];

    $response = $this->postJson(route('api.midtrans.webhook'), $payload);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'processed' => true,
        ]);

    $billing->refresh();
    expect($billing->snap_token)->toBeNull();
    expect($billing->payment_url)->toBeNull();
    expect($billing->status)->toBe('unpaid');
});

test('webhook verification rejects invalid signatures when non-simulation', function () {
    $billing = Billing::factory()->create([
        'student_id' => $this->student->id,
        'payment_category_id' => $this->category->id,
        'academic_year_id' => $this->academicYear->id,
        'amount' => 500000,
        'remaining_balance' => 500000,
        'status' => 'unpaid',
    ]);

    Config::set('services.midtrans.server_key', 'SB-Mid-server-DUMMYKEY');

    $payload = [
        'order_id' => 'BILL-'.$billing->id.'-RANDOM',
        'status_code' => '200',
        'gross_amount' => '500000.00',
        'transaction_status' => 'settlement',
        'payment_type' => 'qris',
        'signature_key' => 'wrong-signature-key-value',
    ];

    $response = $this->postJson(route('api.midtrans.webhook'), $payload);

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'Invalid Signature',
        ]);
});

test('webhook verification accepts valid signature and confirms payment when non-simulation', function () {
    $billing = Billing::factory()->create([
        'student_id' => $this->student->id,
        'payment_category_id' => $this->category->id,
        'academic_year_id' => $this->academicYear->id,
        'amount' => 500000,
        'remaining_balance' => 500000,
        'status' => 'unpaid',
    ]);

    $serverKey = 'SB-Mid-server-DUMMYKEY';
    Config::set('services.midtrans.server_key', $serverKey);

    $orderId = 'BILL-'.$billing->id.'-RANDOM';
    $statusCode = '200';
    $grossAmount = '500000.00';

    // Correct signature key calculation
    $validSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

    $payload = [
        'order_id' => $orderId,
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'transaction_status' => 'settlement',
        'payment_type' => 'qris',
        'signature_key' => $validSignature,
    ];

    $response = $this->postJson(route('api.midtrans.webhook'), $payload);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'processed' => true,
        ]);

    $billing->refresh();
    expect($billing->status)->toBe('paid');
    expect($billing->remaining_balance)->toEqual(0);
});
