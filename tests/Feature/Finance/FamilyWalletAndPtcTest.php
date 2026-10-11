<?php

use App\Models\CharityCampaign;
use App\Models\CharityDonation;
use App\Models\PtcSession;
use App\Models\PtcSlot;
use App\Models\Student;
use App\Models\User;
use App\Models\WadiahAccount;
use App\Services\Growth\PtcSchedulerService;

/*
 * 119.9: donations and parent–teacher conferences belong to a family.
 * Paying a donation from wadiah savings used to debit Student::first() (a
 * stranger's savings) for anyone who was not a student or parent, and a
 * parent always paid from their first child. The PTC routes carried no role:
 * anyone could open sessions, and a parent could book for any student and saw
 * every student account in the picker.
 */

function walletFor(Student $student, float $balance): WadiahAccount
{
    return WadiahAccount::create(['student_id' => $student->id, 'account_number' => 'WDH-'.$student->id, 'balance' => $balance, 'daily_spending_limit' => 50000, 'is_active' => true]);
}

beforeEach(function () {
    $this->campaign = CharityCampaign::create(['title' => 'Infaq Masjid', 'type' => 'infaq', 'description' => 'Renovasi', 'target_amount' => 10000000, 'collected_amount' => 0, 'is_active' => true]);
    $this->parent = User::factory()->create(['role' => 'orang_tua']);
    $this->first = Student::factory()->create(['parent_id' => $this->parent->id]);
    $this->second = Student::factory()->create(['parent_id' => $this->parent->id]);
    $this->stranger = Student::factory()->create();
    walletFor($this->first, 50000);
    walletFor($this->second, 80000);
    walletFor($this->stranger, 999000);
});

test('orang tua membayar infaq dari tabungan anak yang dipilihnya', function () {
    $this->actingAs($this->parent)->post(route('portal.charity.donate.store'), [
        'charity_campaign_id' => $this->campaign->id, 'amount' => 20000, 'payment_method' => 'wadiah_wallet', 'student_id' => $this->second->id,
    ])->assertRedirect(route('portal.charity.history'));

    expect((float) $this->second->wadiahAccount()->first()->balance)->toBe(60000.0)
        ->and((float) WadiahAccount::where('student_id', $this->first->id)->value('balance'))->toBe(50000.0);
});

test('tabungan siswa lain tidak bisa dipakai, dan orang tua dengan dua anak harus memilih', function () {
    // Another family's child.
    $this->actingAs($this->parent)->post(route('portal.charity.donate.store'), [
        'charity_campaign_id' => $this->campaign->id, 'amount' => 20000, 'payment_method' => 'wadiah_wallet', 'student_id' => $this->stranger->id,
    ])->assertSessionHas('error');

    // Two children and none chosen: no silent pick of the first one.
    $this->actingAs($this->parent)->post(route('portal.charity.donate.store'), [
        'charity_campaign_id' => $this->campaign->id, 'amount' => 20000, 'payment_method' => 'wadiah_wallet',
    ])->assertSessionHas('error');

    expect(CharityDonation::count())->toBe(0)
        ->and((float) WadiahAccount::where('student_id', $this->stranger->id)->value('balance'))->toBe(999000.0);
});

test('guru dan admin berinfaq lewat transfer, bukan dari tabungan siswa', function (string $role) {
    $donor = User::factory()->create(['role' => $role]);

    // Used to debit Student::first() — whoever that was.
    $this->actingAs($donor)->post(route('portal.charity.donate.store'), [
        'charity_campaign_id' => $this->campaign->id, 'amount' => 20000, 'payment_method' => 'wadiah_wallet',
    ])->assertSessionHas('error');
    expect((float) WadiahAccount::where('student_id', $this->first->id)->value('balance'))->toBe(50000.0);

    $this->actingAs($donor)->post(route('portal.charity.donate.store'), [
        'charity_campaign_id' => $this->campaign->id, 'amount' => 20000, 'payment_method' => 'cash',
    ])->assertRedirect(route('portal.charity.history'));
    expect(CharityDonation::where('user_id', $donor->id)->sole()->status)->toBe('pending');
})->with(['guru', 'admin']);

test('portal infaq menampilkan tabungan tiap anak, dan "belum punya tabungan" bukan Rp 0', function () {
    $third = Student::factory()->create(['parent_id' => $this->parent->id]);

    $this->actingAs($this->parent)->get(route('portal.charity.portal'))
        ->assertOk()
        ->assertSee($this->first->user->name)->assertSee('Rp 50.000')
        ->assertSee($this->second->user->name)->assertSee('Rp 80.000')
        ->assertSee($third->user->name)->assertSee('Belum punya tabungan')
        ->assertDontSee($this->stranger->user->name);

    // siswa-spelled accounts are no longer locked out.
    $this->actingAs(User::factory()->create(['role' => 'siswa']))->get(route('portal.charity.portal'))->assertOk();
});

test('PTC: staf membuka sesi, orang tua memesan untuk anaknya sendiri', function () {
    $teacher = User::factory()->create(['role' => 'guru']);
    $waka = User::factory()->create(['role' => 'waka_kesiswaan']);

    $this->actingAs($this->parent)->post(route('growth.ptc.store_session'), ['title' => 'x', 'academic_year' => '2026/2027', 'date' => now()->addDay()->toDateString(), 'start_time' => '08:00', 'end_time' => '09:00'])->assertForbidden();
    $this->actingAs($waka)->post(route('growth.ptc.store_session'), [
        'title' => 'Konsultasi Rapor', 'academic_year' => '2026/2027', 'date' => now()->addDay()->toDateString(), 'start_time' => '08:00', 'end_time' => '09:00', 'slot_duration_minutes' => 30,
    ])->assertRedirect();

    $session = PtcSession::sole();
    expect($session->location)->toBeNull();
    $slot = PtcSlot::where('teacher_id', $teacher->id)->orderBy('start_time')->firstOrFail();

    // The picker lists only the parent's own children.
    $this->actingAs($this->parent)->get(route('growth.ptc.available_slots', $session->id))
        ->assertOk()
        ->assertSee($this->first->user->name)
        ->assertDontSee($this->stranger->user->name);

    // Booking for another family's child is refused.
    $this->actingAs($this->parent)->post(route('growth.ptc.book_slot', $slot->id), ['student_id' => $this->stranger->user_id])->assertForbidden();
    expect($slot->fresh()->status)->toBe('available');

    $this->actingAs($this->parent)->post(route('growth.ptc.book_slot', $slot->id), ['student_id' => $this->first->user_id, 'topic' => 'Nilai'])->assertSessionHas('success');
    expect($slot->fresh())->status->toBe('booked')->parent_id->toBe($this->parent->id)->student_id->toBe($this->first->user_id);

    // Another parent sees "Terisi", not who booked.
    $other = User::factory()->create(['role' => 'parent']);
    Student::factory()->create(['parent_id' => $other->id]);
    $this->actingAs($other)->get(route('growth.ptc.available_slots', $session->id))
        ->assertSee('Terisi')->assertDontSee($this->first->user->name);
    $this->actingAs($other)->post(route('growth.ptc.book_slot', $slot->id), ['student_id' => Student::where('parent_id', $other->id)->value('user_id')])
        ->assertSessionHas('error');

    $this->actingAs($teacher)->get(route('growth.ptc.teacher_schedule'))->assertOk()->assertSee($this->first->user->name);

    // The parent can cancel their own booking.
    $this->actingAs($this->parent)->post(route('growth.ptc.cancel_slot', $slot->id))->assertSessionHas('success');
    expect($slot->fresh()->status)->toBe('available');
});

test('PTC: pemesanan bersamaan hanya berhasil sekali', function () {
    $teacher = User::factory()->create(['role' => 'guru']);
    $session = app(PtcSchedulerService::class)->createSession(['title' => 'K', 'academic_year' => '2026/2027', 'date' => now()->addDay()->toDateString(), 'start_time' => '08:00', 'end_time' => '08:15'], User::factory()->create(['role' => 'admin'])->id);
    $slot = PtcSlot::where('ptc_session_id', $session->id)->where('teacher_id', $teacher->id)->sole();
    $stale = $slot->replicate()->forceFill(['id' => $slot->id]);

    expect(app(PtcSchedulerService::class)->bookSlot($slot, $this->parent->id, $this->first->user_id))->toBeTrue()
        ->and(app(PtcSchedulerService::class)->bookSlot($stale, $this->parent->id, $this->second->user_id))->toBeFalse()
        ->and($slot->fresh()->student_id)->toBe($this->first->user_id);
});

test('PTC: siswa dan akun di luar sekolah tidak bisa membuka jadwal', function (string $role) {
    $this->actingAs(User::factory()->create(['role' => $role]))->get(route('growth.ptc.sessions'))->assertForbidden();
})->with(['siswa', 'merchant', 'alumni']);
