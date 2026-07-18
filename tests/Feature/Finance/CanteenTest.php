<?php

use App\Models\Student;
use App\Models\User;
use App\Models\WadiahAccount;
use App\Models\CanteenMerchant;
use App\Models\CanteenTransaction;
use App\Services\WadiahService;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->merchantOwner = User::factory()->create(['role' => 'staf_tu']);
    $this->parent = User::factory()->create(['role' => 'parent']);
    $this->student = Student::factory()->create(['parent_id' => $this->parent->id]);
    $this->wadiahService = app(WadiahService::class);
    
    // Create merchant
    $this->merchant = CanteenMerchant::create([
        'name' => 'Kantin Sehat',
        'owner_user_id' => $this->merchantOwner->id,
        'is_active' => true,
    ]);

    // Give merchantOwner role 'merchant'
    $this->merchantOwner->update(['role' => 'merchant']);
});

test('admin can register merchant', function () {
    $potentialOwner = User::factory()->create(['role' => 'staf_tu']);
    
    $response = $this->actingAs($this->admin)->post(route('admin.finance.canteen.merchants.store'), [
        'name' => 'Kantin Baru',
        'owner_user_id' => $potentialOwner->id,
    ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('canteen_merchants', ['name' => 'Kantin Baru']);
    
    $potentialOwner->refresh();
    expect($potentialOwner->role)->toBe('merchant');
});

test('cashless canteen POS transaction flow', function () {
    $account = $this->wadiahService->createAccount($this->student);
    
    // Deposit money
    $this->wadiahService->deposit($account, 50000, $this->admin->id, 'Deposit');

    // Make purchase
    $response = $this->actingAs($this->merchantOwner)->postJson(route('merchant.canteen.payment'), [
        'student_identifier' => $this->student->nis,
        'items' => [
            ['name' => 'Nasi Goreng', 'qty' => 1, 'price' => 12000],
            ['name' => 'Es Teh', 'qty' => 1, 'price' => 3000],
        ],
        'total_amount' => 15000,
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('success', true);

    $account->refresh();
    expect($account->balance)->toEqual(35000);

    $this->assertDatabaseHas('canteen_transactions', [
        'canteen_merchant_id' => $this->merchant->id,
        'total_amount' => 15000,
    ]);
});

test('cashless canteen POS transaction fails if insufficient balance', function () {
    $account = $this->wadiahService->createAccount($this->student);
    
    // Deposit small money
    $this->wadiahService->deposit($account, 5000, $this->admin->id, 'Deposit');

    // Make purchase for 15k
    $response = $this->actingAs($this->merchantOwner)->postJson(route('merchant.canteen.payment'), [
        'student_identifier' => $this->student->nis,
        'items' => [
            ['name' => 'Nasi Goreng', 'qty' => 1, 'price' => 12000],
            ['name' => 'Es Teh', 'qty' => 1, 'price' => 3000],
        ],
        'total_amount' => 15000,
    ]);

    $response->assertStatus(422);
    $response->assertJsonPath('success', false);
    $response->assertJsonFragment(['message' => 'Saldo tabungan wadiah tidak mencukupi.']);
});

test('cashless canteen POS transaction fails if daily spending limit exceeded', function () {
    $account = $this->wadiahService->createAccount($this->student);
    
    // Deposit 100k
    $this->wadiahService->deposit($account, 100000, $this->admin->id, 'Deposit');

    // Set daily limit to 20k
    $account->update(['daily_spending_limit' => 20000]);

    // Make purchase for 25k (exceeds limit)
    $response = $this->actingAs($this->merchantOwner)->postJson(route('merchant.canteen.payment'), [
        'student_identifier' => $this->student->nis,
        'items' => [
            ['name' => 'Nasi Goreng Premium', 'qty' => 1, 'price' => 25000],
        ],
        'total_amount' => 25000,
    ]);

    $response->assertStatus(422);
    $response->assertJsonPath('success', false);
    
    // Check if the message indicates limit exceeded
    $errorMessage = $response->json('message');
    expect($errorMessage)->toContain('Transaksi ditolak. Batasan belanja harian anak');
});
