<?php

use App\Models\Billing;
use App\Models\Student;
use App\Models\User;

/*
 * POST student/billings/{id}/pay used to answer 200 with a virtual account
 * number built from a bank prefix and the NIS. Nothing existed at any bank, so
 * a parent could transfer real money to a number that is not the school's.
 * Until the app is wired to the payment gateway, it says it is unavailable.
 */

beforeEach(function () {
    $this->student = Student::factory()->create([
        'nis' => '12345',
        'user_id' => User::factory()->create(['role' => 'siswa'])->id,
    ]);
    $this->billing = Billing::factory()->create(['student_id' => $this->student->id]);
});

test('membayar lewat aplikasi menjawab 501 tanpa nomor VA karangan', function () {
    $response = $this->actingAs($this->student->user)
        ->postJson("/api/v1/student/billings/{$this->billing->id}/pay", ['bank' => 'BCA']);

    $response->assertStatus(501)
        ->assertJsonPath('success', false)
        ->assertJsonPath('error_code', 'payment_unavailable')
        ->assertJsonPath('data', null)
        ->assertJsonPath('message', 'Pembayaran lewat aplikasi belum tersedia. Silakan bayar langsung ke Bendahara atau Tata Usaha sekolah.');

    expect($response->getContent())->not->toContain('va_number')
        ->not->toContain('12345');
    expect($this->billing->fresh()->status)->toBe($this->billing->status);
});

test('pesannya mengikuti bahasa pengguna', function () {
    $this->student->user->update(['preferred_locale' => 'en']);

    $this->actingAs($this->student->user)
        ->postJson("/api/v1/student/billings/{$this->billing->id}/pay")
        ->assertStatus(501)
        ->assertJsonPath('message', 'Paying in the app is not available yet. Please pay the school treasurer or administration office directly.');
});

test('tagihan siswa lain tetap 404', function () {
    $other = Billing::factory()->create(['student_id' => Student::factory()->create()->id]);

    $this->actingAs($this->student->user)
        ->postJson("/api/v1/student/billings/{$other->id}/pay")
        ->assertNotFound();
});

test('peran selain siswa tidak bisa masuk', function () {
    $this->actingAs(User::factory()->create(['role' => 'orang_tua']))
        ->postJson("/api/v1/student/billings/{$this->billing->id}/pay")
        ->assertForbidden();
});
