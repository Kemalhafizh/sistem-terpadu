<?php

use App\Models\BankAccount;
use App\Models\User;
use App\Models\VendorPayment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->travelTo(now()->setDate(2026, 9, 15)->startOfDay());
    $this->user = User::factory()->create(['role' => 'staf_tu']);
    $this->treasurer = User::factory()->create(['role' => 'admin']);
    $this->account = BankAccount::create(['bank_name' => 'BSI', 'account_number' => '7001', 'account_name' => 'Operasional', 'account_type' => 'giro']);
});

function vendorPayment(User $creator, array $attributes = []): VendorPayment
{
    return VendorPayment::create($attributes + [
        'vendor_name' => 'PLN', 'description' => 'Listrik September', 'amount' => 2350000,
        'due_date' => '2026-09-20', 'payment_method' => 'transfer', 'status' => 'scheduled', 'created_by' => $creator->id,
    ]);
}

test('vendor/calendar is served by the calendar action, not captured by vendor/{vendor}', function () {
    expect(Route::getRoutes()->match(request()->create('/treasury/vendor/calendar'))->getName())->toBe('treasury.vendor.calendar');

    $this->actingAs($this->user)->get('/treasury/vendor/calendar')
        ->assertOk()
        ->assertSee('Kalender Pembayaran')
        ->assertDontSee('Tampilan kalender');
});

test('the calendar places each payment on its due date and totals the month from real records', function () {
    vendorPayment($this->user, ['vendor_name' => 'PLN', 'amount' => 2350000, 'due_date' => '2026-09-20']);
    vendorPayment($this->user, ['vendor_name' => 'PDAM', 'amount' => 480000, 'due_date' => '2026-09-05']); // overdue on 15/09
    vendorPayment($this->user, ['vendor_name' => 'Telkom', 'amount' => 750000, 'due_date' => '2026-09-10', 'status' => 'paid', 'paid_date' => '2026-09-09']);
    vendorPayment($this->user, ['vendor_name' => 'Katering Oktober', 'amount' => 9000000, 'due_date' => '2026-10-02']);

    $response = $this->actingAs($this->user)->get(route('treasury.vendor.calendar', ['month' => '2026-09']))->assertOk();

    $response->assertSee('PLN')->assertSee('PDAM')->assertSee('Telkom')
        ->assertDontSee('Katering Oktober')
        ->assertSee('3 pembayaran jatuh tempo bulan ini')
        ->assertSee('belum dibayar Rp 2.830.000')
        ->assertSee('sudah dibayar Rp 750.000');

    $this->get(route('treasury.vendor.calendar', ['month' => '2026-10']))->assertOk()->assertSee('Katering Oktober');
});

test('overdue is derived from the due date and shown in the list', function () {
    vendorPayment($this->user, ['vendor_name' => 'PDAM', 'amount' => 480000, 'due_date' => '2026-09-05']);
    vendorPayment($this->user, ['vendor_name' => 'PLN', 'amount' => 2350000, 'due_date' => '2026-09-20']);

    expect(VendorPayment::where('vendor_name', 'PDAM')->sole()->effectiveStatus())->toBe('overdue')
        ->and(VendorPayment::where('vendor_name', 'PLN')->sole()->effectiveStatus())->toBe('scheduled');

    $this->actingAs($this->user)->get(route('treasury.vendor.index'))
        ->assertOk()
        ->assertSee('Terlambat')
        ->assertSee('Rp 480.000')      // overdue total
        ->assertSee('Rp 2.350.000');   // scheduled total
});

test('the detail page shows the payment instead of a blank page', function () {
    $payment = vendorPayment($this->user, ['invoice_number' => 'INV-PLN-0925']);

    $this->actingAs($this->user)->get(route('treasury.vendor.show', $payment))
        ->assertOk()
        ->assertSee('PLN')
        ->assertSee('INV-PLN-0925')
        ->assertSee('Rp 2.350.000')
        ->assertSee('Tandai Lunas');
});

test('store schedules the payment under the logged-in user and opens its detail page', function () {
    $response = $this->actingAs($this->user)->post(route('treasury.vendor.store'), [
        'vendor_name' => 'CV Sinar', 'invoice_number' => 'INV-77', 'amount' => 900000, 'due_date' => '2026-09-30',
        'description' => 'Servis AC', 'payment_method' => 'transfer', 'bank_account_id' => $this->account->id,
    ]);

    $payment = VendorPayment::sole();
    $response->assertRedirect(route('treasury.vendor.show', $payment));
    expect($payment->status)->toBe('scheduled')
        ->and($payment->created_by)->toBe($this->user->id)
        ->and($payment->bank_account_id)->toBe($this->account->id);
});

test('marking paid records the date, source account, proof and who paid it', function () {
    Storage::fake('local');
    $payment = vendorPayment($this->user);

    $this->actingAs($this->treasurer)->post(route('treasury.vendor.pay', $payment), [
        'paid_date' => '2026-09-14', 'payment_method' => 'transfer', 'bank_account_id' => $this->account->id,
        'proof' => UploadedFile::fake()->create('bukti.pdf', 50, 'application/pdf'),
    ])->assertRedirect(route('treasury.vendor.show', $payment));

    $payment->refresh();
    expect($payment->status)->toBe('paid')
        ->and($payment->paid_date->toDateString())->toBe('2026-09-14')
        ->and($payment->bank_account_id)->toBe($this->account->id)
        ->and($payment->approved_by)->toBe($this->treasurer->id);
    Storage::disk('local')->assertExists($payment->proof_file_path);
    $this->get(route('treasury.vendor.proof', $payment))->assertOk();
});

test('a non-cash payment needs a source account, and a future date is refused', function () {
    $payment = vendorPayment($this->user);

    $this->actingAs($this->treasurer)->from(route('treasury.vendor.show', $payment))
        ->post(route('treasury.vendor.pay', $payment), ['paid_date' => '2026-09-16', 'payment_method' => 'transfer'])
        ->assertSessionHasErrors(['paid_date', 'bank_account_id']);

    expect($payment->fresh()->status)->toBe('scheduled');
});

test('a paid or cancelled payment cannot be paid or cancelled again', function () {
    $paid = vendorPayment($this->user, ['status' => 'paid', 'paid_date' => '2026-09-10']);
    $cancelled = vendorPayment($this->user, ['status' => 'cancelled']);

    $this->actingAs($this->treasurer)->post(route('treasury.vendor.pay', $paid), ['paid_date' => '2026-09-14', 'payment_method' => 'cash'])
        ->assertSessionHasErrors('status');
    $this->post(route('treasury.vendor.cancel', $cancelled))->assertSessionHasErrors('status');

    $open = vendorPayment($this->user);
    $this->post(route('treasury.vendor.cancel', $open))->assertRedirect(route('treasury.vendor.show', $open));
    expect($paid->fresh()->paid_date->toDateString())->toBe('2026-09-10')
        ->and($open->fresh()->status)->toBe('cancelled');
});

test('unimplemented vendor and petty cash resource routes are not registered', function () {
    foreach (['treasury.vendor.edit', 'treasury.vendor.update', 'treasury.vendor.destroy',
              'treasury.petty-cash.edit', 'treasury.petty-cash.update', 'treasury.petty-cash.destroy'] as $name) {
        expect(Route::has($name))->toBeFalse();
    }
});
