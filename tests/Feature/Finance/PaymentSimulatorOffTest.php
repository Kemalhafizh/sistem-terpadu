<?php

use App\Models\Billing;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;

/*
 * the pretend gateway used to be live everywhere. Without a working
 * Midtrans key, checkout handed out a page whose button marked the bill paid,
 * and a student or parent could POST the callback for their own bill and have
 * it settled with no money received. The simulator is now
 * off unless switched on for a local demo, and never on in production.
 */

beforeEach(function () {
    config(['services.midtrans.simulator' => false, 'services.midtrans.server_key' => null]);
    $this->parent = User::factory()->create(['role' => 'orang_tua']);
    $this->student = Student::factory()->create(['parent_id' => $this->parent->id]);
    $this->student->user->update(['role' => 'siswa']);
    $this->billing = Billing::factory()->create([
        'student_id' => $this->student->id, 'amount' => 500000, 'remaining_balance' => 500000, 'status' => 'unpaid',
    ]);
});

test('siswa dan orang tua tidak bisa melunasi tagihan lewat callback tiruan', function () {
    $this->actingAs($this->student->user)->post(route('finance.mock-callback', $this->billing->id))->assertNotFound();
    $this->actingAs($this->student->user)->get(route('finance.mock-gateway', $this->billing->id))->assertNotFound();
    $this->actingAs($this->parent)->post(route('parent-portal.billings.callback', $this->billing->id))->assertNotFound();
    $this->actingAs($this->parent)->get(route('parent-portal.billings.mock-gateway', $this->billing->id))->assertNotFound();

    expect(Payment::count())->toBe(0)
        ->and((float) $this->billing->fresh()->remaining_balance)->toBe(500000.0);
});

test('tanpa Midtrans, checkout mengatakan pembayaran online belum tersedia', function () {
    $this->actingAs($this->student->user)->get(route('finance.student.checkout', $this->billing->id))
        ->assertRedirect(route('finance.student.index'))
        ->assertSessionHas('error', 'Pembayaran online belum tersedia. Silakan bayar langsung di Tata Usaha.');

    $this->actingAs($this->parent)->get(route('parent-portal.billings.checkout', $this->billing->id))
        ->assertRedirect(route('parent-portal.billings', $this->student->id))
        ->assertSessionHas('error');

    expect($this->billing->fresh()->snap_token)->toBeNull();
});

test('token demo lama dibuang saat simulator mati', function () {
    $this->billing->update(['snap_token' => 'TKN-DEMO-LAMA', 'payment_url' => 'http://localhost/gateway/1/mock']);

    $this->actingAs($this->student->user)->get(route('finance.student.checkout', $this->billing->id))->assertRedirect();

    expect($this->billing->fresh())->snap_token->toBeNull()->payment_url->toBeNull();
});
