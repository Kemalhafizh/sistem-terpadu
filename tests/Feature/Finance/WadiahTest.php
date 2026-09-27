<?php

use App\Models\Student;
use App\Models\User;
use App\Services\WadiahService;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->tu = User::factory()->create(['role' => 'staf_tu']);
    $this->parent = User::factory()->create(['role' => 'parent']);
    $this->student = Student::factory()->create(['parent_id' => $this->parent->id]);
    $this->wadiahService = app(WadiahService::class);
});

test('wadiah service can create account', function () {
    $account = $this->wadiahService->createAccount($this->student);

    expect($account)->not->toBeNull();
    expect($account->student_id)->toEqual($this->student->id);
    expect($account->balance)->toEqual(0);
    expect($account->daily_spending_limit)->toEqual(50000);
});

test('admin or tu can deposit and withdraw cash', function () {
    $account = $this->wadiahService->createAccount($this->student);

    // Deposit
    $deposit = $this->wadiahService->deposit($account, 100000, $this->tu->id, 'Top up');
    expect($deposit->type)->toBe('deposit');
    expect($deposit->amount)->toEqual(100000);
    expect($deposit->current_balance)->toEqual(100000);

    $account->refresh();
    expect($account->balance)->toEqual(100000);

    // Withdraw
    $withdrawal = $this->wadiahService->withdraw($account, 40000, $this->tu->id, 'Withdraw');
    expect($withdrawal->type)->toBe('withdrawal');
    expect($withdrawal->amount)->toEqual(40000);
    expect($withdrawal->current_balance)->toEqual(60000);

    $account->refresh();
    expect($account->balance)->toEqual(60000);
});

test('parent can update child spending limit', function () {
    $account = $this->wadiahService->createAccount($this->student);

    $response = $this->actingAs($this->parent)
        ->post(route('parent-portal.wallet.limit', $this->student), [
            'daily_spending_limit' => 25000,
        ]);

    $response->assertStatus(302);
    $account->refresh();
    expect($account->daily_spending_limit)->toEqual(25000);
});
