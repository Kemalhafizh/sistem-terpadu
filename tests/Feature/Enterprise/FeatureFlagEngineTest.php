<?php

use App\Models\FeatureFlag;
use App\Models\User;
use App\Services\FeatureFlagService;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->student = User::factory()->create(['role' => 'student']);
    $this->flagService = new FeatureFlagService;

    $this->flag = FeatureFlag::updateOrCreate(
        ['key' => 'test_feature_flag'],
        [
            'name' => 'Test Feature Flag',
            'description' => 'Flag for unit testing',
            'is_active' => true,
            'rollout_percentage' => 100,
            'target_roles' => ['admin'],
        ]
    );
});

test('feature flag service evaluates active status and target roles', function () {
    expect($this->flagService->isEnabled('test_feature_flag', $this->admin))->toBeTrue();
    expect($this->flagService->isEnabled('test_feature_flag', $this->student))->toBeFalse(); // Student not in target_roles

    // Disable flag
    $this->flagService->toggle('test_feature_flag', false);
    expect($this->flagService->isEnabled('test_feature_flag', $this->admin))->toBeFalse();
});

test('feature flag service toggles active status', function () {
    $initialState = $this->flag->is_active;
    $newState = $this->flagService->toggle('test_feature_flag');

    expect($newState)->toBe(! $initialState);
});

test('admin can view feature flag engine dashboard', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.system.feature-flags.index'));
    $response->assertStatus(200);
    $response->assertSee('Zero-Downtime Feature Flag Engine');
});

test('admin can toggle feature flag status via controller', function () {
    $response = $this->actingAs($this->admin)
        ->from(route('admin.system.feature-flags.index'))
        ->post(route('admin.system.feature-flags.toggle', $this->flag));

    $response->assertStatus(302);
    $response->assertRedirect(route('admin.system.feature-flags.index'));
});

test('admin can update rollout percentage and target roles via controller', function () {
    $response = $this->actingAs($this->admin)
        ->from(route('admin.system.feature-flags.index'))
        ->post(route('admin.system.feature-flags.update', $this->flag), [
            'rollout_percentage' => 50,
            'target_roles' => ['admin', 'guru'],
        ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('admin.system.feature-flags.index'));

    $this->flag->refresh();
    expect($this->flag->rollout_percentage)->toBe(50);
    expect($this->flag->target_roles)->toContain('guru');
});
