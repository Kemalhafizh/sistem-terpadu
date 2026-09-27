<?php

use App\Models\Setting;
use App\Services\WhatsappService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/*
 * Regression: WhatsappService read env() directly. After `config:cache` (run
 * by deploy.yml) env() returns only its default, so the .env token vanished,
 * and the sandbox check `setting === '1' || env(..., true)` was always true —
 * disabling sandbox mode in the admin panel never sent a real message.
 */

beforeEach(function () {
    Cache::forget('setting_whatsapp_sandbox_mode');
    Cache::forget('setting_whatsapp_api_token');
    Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['123']])]);
});

test('sandbox dimatikan admin → pesan benar-benar dikirim lewat Fonnte dengan token dari config', function () {
    Setting::set('whatsapp_sandbox_mode', '0');
    config(['services.whatsapp.token' => 'token-dari-config', 'services.whatsapp.sandbox' => true]);

    $log = app(WhatsappService::class)->sendMessage('081234567890', 'Tagihan SPP bulan ini');

    expect($log->status)->toBe('sent');
    Http::assertSent(fn ($request) => $request->url() === 'https://api.fonnte.com/send'
        && $request->header('Authorization') === ['token-dari-config']
        && $request['target'] === '6281234567890');
});

test('tanpa token di pengaturan maupun config, pengiriman live gagal dengan jelas', function () {
    Setting::set('whatsapp_sandbox_mode', '0');
    config(['services.whatsapp.token' => null]);

    $log = app(WhatsappService::class)->sendMessage('081234567890', 'Halo');

    expect($log->status)->toBe('failed')
        ->and($log->error_message)->toBe('API Token is missing in system settings.');
    Http::assertNothingSent();
});

test('tanpa pengaturan admin, mode sandbox mengikuti config', function (bool $sandbox) {
    config(['services.whatsapp.token' => 'token-dari-config', 'services.whatsapp.sandbox' => $sandbox]);

    app(WhatsappService::class)->sendMessage('081234567890', 'Halo');

    $sandbox ? Http::assertNothingSent() : Http::assertSentCount(1);
})->with(['sandbox aktif' => true, 'sandbox mati' => false]);

test('sandbox aktif di pengaturan admin tidak pernah memanggil Fonnte', function () {
    Setting::set('whatsapp_sandbox_mode', '1');
    config(['services.whatsapp.token' => 'token-dari-config', 'services.whatsapp.sandbox' => false]);

    expect(app(WhatsappService::class)->sendMessage('081234567890', 'Halo')->status)->toBe('sent');
    Http::assertNothingSent();
});

test('whitelist IP admin mengizinkan IP yang terdaftar di config', function () {
    Route::get('/admin/test-ip-allowed', fn () => 'ok')->middleware(['web', 'admin.ip']);
    app()->detectEnvironment(fn () => 'production');
    config(['app.admin_allowed_ips' => '10.0.0.5, 192.168.1.100']);

    $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.5'])->get('/admin/test-ip-allowed')->assertOk();
    $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.6'])->get('/admin/test-ip-allowed')->assertForbidden();
});
