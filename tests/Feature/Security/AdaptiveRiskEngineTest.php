<?php

use App\Models\User;
use App\Services\AdaptiveRiskEngine;
use Illuminate\Http\Request;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->riskEngine = app(AdaptiveRiskEngine::class);
});

test('adaptive risk engine calculates score and registers trusted devices', function () {
    $request1 = Request::create('/login', 'POST', [], [], [], [
        'REMOTE_ADDR' => '192.168.1.100',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
    ]);

    // First time login from new device/subnet -> score should be elevated (unfamiliar)
    $assessment1 = $this->riskEngine->calculateRiskScore($this->user, $request1);
    expect($assessment1['score'])->toBeGreaterThanOrEqual(30);

    // Register device as known
    $this->riskEngine->registerKnownDevice($this->user, $request1);

    // Second time login from registered device -> score should be low
    $assessment2 = $this->riskEngine->calculateRiskScore($this->user, $request1);
    expect($assessment2['score'])->toBeLessThan(30);
    expect($assessment2['requires_mfa'])->toBeFalse();
});
