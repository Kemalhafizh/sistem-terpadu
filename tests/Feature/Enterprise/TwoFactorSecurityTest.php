<?php

use App\Models\User;
use App\Services\TotpSecurityService;
use Tests\TestCase;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'admin']);
    $this->totpService = app(TotpSecurityService::class);
});

test('totp service generates secret, qr code url, and recovery codes', function () {
    $secret = $this->totpService->generateSecret();
    expect($secret)->toHaveLength(16);

    $qrUrl = $this->totpService->generateQrCodeUrl('test@school.sch.id', $secret);
    expect($qrUrl)->toContain('otpauth://totp/');
    expect($qrUrl)->toContain($secret);

    $recoveryCodes = $this->totpService->generateRecoveryCodes(8);
    expect($recoveryCodes)->toHaveCount(8);
});

test('totp service verifies emergency recovery code and consumes it', function () {
    $secret = $this->totpService->generateSecret();
    $recoveryCodes = ['CODE-1111', 'CODE-2222', 'CODE-3333'];

    $this->user->totp_secret = $secret;
    $this->user->totp_enabled_at = now();
    $this->user->totp_recovery_codes = $recoveryCodes;
    $this->user->save();

    expect($this->totpService->verifyUserTotpOrRecoveryCode($this->user, 'CODE-1111'))->toBeTrue();

    $this->user->refresh();
    expect($this->user->totp_recovery_codes)->not()->toContain('CODE-1111');
    expect($this->user->totp_recovery_codes)->toHaveCount(2);
});

test('user can view 2fa security control panel', function () {
    $response = $this->actingAs($this->user)->get(route('admin.security.2fa.index'));
    $response->assertStatus(200);
    $response->assertSee('TOTP 2FA');
});

test('session fingerprint middleware sets session fingerprint', function () {
    $response = $this->actingAs($this->user)->get(route('admin.security.2fa.index'));
    $response->assertStatus(200);

    expect(session('session_fingerprint'))->not()->toBeNull();
});

test('step up elevation form displays for re-authentication challenge', function () {
    $response = $this->actingAs($this->user)->get(route('admin.security.2fa.elevate_form'));
    $response->assertStatus(200);
    $response->assertSee('Step-Up Elevation');
});
