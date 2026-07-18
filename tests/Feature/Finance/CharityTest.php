<?php

use App\Models\Student;
use App\Models\User;
use App\Models\WadiahAccount;
use App\Models\CharityCampaign;
use App\Models\CharityDonation;
use App\Services\CharityService;
use App\Services\WadiahService;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->parent = User::factory()->create(['role' => 'parent']);
    $this->student = Student::factory()->create(['parent_id' => $this->parent->id]);
    
    // Create wadiah account for student
    $wadiahService = app(WadiahService::class);
    $this->wadiahAccount = $wadiahService->createAccount($this->student);
    $wadiahService->deposit($this->wadiahAccount, 100000, $this->admin->id, 'Initial Deposit');

    $this->charityService = app(CharityService::class);
    
    $this->campaign = $this->charityService->createCampaign([
        'title' => 'Wakaf Masjid',
        'type' => 'wakaf',
        'description' => 'Pembangunan Masjid Kampus',
        'target_amount' => 50000000.00,
        'is_active' => true,
    ]);
});

test('admin can create campaign', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.finance.charity.store'), [
        'title' => 'Infaq Yatim',
        'type' => 'infaq',
        'description' => 'Peduli Anak Yatim',
        'target_amount' => 5000000,
    ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('charity_campaigns', ['title' => 'Infaq Yatim']);
});

test('user can submit a bank transfer donation which starts as pending', function () {
    $user = $this->parent;
    
    $response = $this->actingAs($user)->post(route('portal.charity.donate.store'), [
        'charity_campaign_id' => $this->campaign->id,
        'amount' => 50000,
        'payment_method' => 'transfer',
        'donor_name' => 'Budi',
        'is_anonymous' => false,
    ]);

    $response->assertStatus(302);
    
    $this->assertDatabaseHas('charity_donations', [
        'charity_campaign_id' => $this->campaign->id,
        'amount' => 50000,
        'payment_method' => 'transfer',
        'status' => 'pending',
    ]);
});

test('admin can verify a pending donation', function () {
    $donation = CharityDonation::create([
        'charity_campaign_id' => $this->campaign->id,
        'user_id' => $this->parent->id,
        'donor_name' => 'Wali Murid',
        'amount' => 50000,
        'payment_method' => 'transfer',
        'status' => 'pending',
        'receipt_number' => 'ZIS-TEST-123',
    ]);

    $response = $this->actingAs($this->admin)->post(route('admin.finance.charity.verify', $donation->id));

    $response->assertStatus(302);
    
    $donation->refresh();
    expect($donation->status)->toBe('verified');
    
    $this->campaign->refresh();
    expect($this->campaign->collected_amount)->toEqual(50000);
});

test('student can donate via wadiah wallet with auto-debit and instant verification', function () {
    $studentUser = $this->student->user; // get the student's User model
    
    $response = $this->actingAs($studentUser)->post(route('portal.charity.donate.store'), [
        'charity_campaign_id' => $this->campaign->id,
        'amount' => 10000, // exact limit
        'payment_method' => 'wadiah_wallet',
    ]);

    $response->assertStatus(302);

    $this->assertDatabaseHas('charity_donations', [
        'charity_campaign_id' => $this->campaign->id,
        'amount' => 10000,
        'payment_method' => 'wadiah_wallet',
        'status' => 'verified',
    ]);

    $this->wadiahAccount->refresh();
    // 100,000 initial - 10,000 donation = 90,000
    expect($this->wadiahAccount->balance)->toEqual(90000);
});

test('student cannot donate via wadiah wallet more than 10k', function () {
    $studentUser = $this->student->user;
    
    $response = $this->actingAs($studentUser)->post(route('portal.charity.donate.store'), [
        'charity_campaign_id' => $this->campaign->id,
        'amount' => 15000, // exceeds 10k limit
        'payment_method' => 'wadiah_wallet',
    ]);

    $response->assertStatus(302); // Redirect back on error
    $response->assertSessionHas('error', 'Batas donasi mandiri siswa via E-Wallet maksimal adalah Rp 10.000 per transaksi.');

    // Balance should remain unchanged
    $this->wadiahAccount->refresh();
    expect($this->wadiahAccount->balance)->toEqual(100000);
});

test('donor can download e-certificate for verified donation', function () {
    $donation = CharityDonation::create([
        'charity_campaign_id' => $this->campaign->id,
        'user_id' => $this->parent->id,
        'donor_name' => 'Wali Murid',
        'amount' => 50000,
        'payment_method' => 'transfer',
        'status' => 'verified',
        'receipt_number' => 'ZIS-CERT-123',
    ]);

    $response = $this->actingAs($this->parent)->get(route('portal.charity.certificate', $donation->id));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});
