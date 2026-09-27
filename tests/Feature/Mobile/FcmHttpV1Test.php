<?php

use App\Jobs\SendFcmNotificationJob;
use App\Models\User;
use App\Models\UserDeviceToken;
use App\Services\Mobile\FcmHttpV1Client;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

/*
 * SendFcmNotificationJob used to be a mock (log only) and read the
 * device_token column, while the app registers its token (POST mobile/devices
 * → PushNotificationService) into fcm_token — so no real phone was ever reached.
 */

const FCM_SEND_URL = 'https://fcm.googleapis.com/v1/projects/sista-test/messages:send';

beforeEach(function () {
    Cache::forget('fcm:http_v1:access_token');

    $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
    openssl_pkey_export($key, $privateKey);
    $this->publicKey = openssl_pkey_get_details($key)['key'];
    $this->configureFcm = fn () => config(['services.fcm.credentials' => json_encode([
        'type' => 'service_account',
        'project_id' => 'sista-test',
        'client_email' => 'push@sista-test.iam.gserviceaccount.com',
        'private_key' => $privateKey,
        'token_uri' => 'https://oauth2.googleapis.com/token',
    ])]);

    $this->user = User::factory()->create(['role' => 'student']);
});

function fakeFcm(array $sendResponses = []): void
{
    Http::fake([
        'oauth2.googleapis.com/*' => Http::response(['access_token' => 'ya29.test-token', 'expires_in' => 3599]),
        'fcm.googleapis.com/*' => $sendResponses
            ? Http::sequence($sendResponses)
            : Http::response(['name' => 'projects/sista-test/messages/1']),
    ]);
}

test('token yang didaftarkan aplikasi lewat POST mobile/devices benar-benar dikirimi push', function () {
    ($this->configureFcm)();
    fakeFcm();

    $this->actingAs($this->user)->postJson('/api/v1/mobile/devices', [
        'device_id' => 'pixel-7',
        'fcm_token' => 'fcm-token-dari-hp',
        'platform' => 'android',
    ])->assertOk();

    SendFcmNotificationJob::dispatchSync([$this->user->id], 'Sesi Matematika dimulai!', 'Segera scan QR.', [
        'type' => 'class_session_started',
        'session_id' => 45,
    ], 'attendance_alerts');

    // The service-account assertion is a valid RS256 JWT with the claims Google requires.
    Http::assertSent(function (Request $r) {
        if ($r->url() !== 'https://oauth2.googleapis.com/token') {
            return false;
        }
        $claims = JWT::decode($r['assertion'], new Key($this->publicKey, 'RS256'));

        return $r['grant_type'] === 'urn:ietf:params:oauth:grant-type:jwt-bearer'
            && $claims->iss === 'push@sista-test.iam.gserviceaccount.com'
            && $claims->scope === 'https://www.googleapis.com/auth/firebase.messaging'
            && $claims->aud === 'https://oauth2.googleapis.com/token'
            && $claims->exp - $claims->iat === 3600;
    });

    Http::assertSent(function (Request $r) {
        if ($r->url() !== FCM_SEND_URL) {
            return false;
        }
        $m = $r['message'];

        return $r->hasHeader('Authorization', 'Bearer ya29.test-token')
            && $m['token'] === 'fcm-token-dari-hp'
            && $m['notification'] === ['title' => 'Sesi Matematika dimulai!', 'body' => 'Segera scan QR.']
            && $m['android']['notification']['channel_id'] === 'attendance_alerts'
            // Keys the app's SulaoneFirebaseMessagingService reads; all values strings.
            && $m['data']['channel'] === 'attendance_alerts'
            && $m['data']['title'] === 'Sesi Matematika dimulai!'
            && $m['data']['session_id'] === '45'
            && $m['data']['type'] === 'class_session_started';
    });

    expect(UserDeviceToken::sole()->last_used_at)->not->toBeNull();
});

test('tanpa kredensial FCM job hanya mencatat log dan tidak memanggil Google', function () {
    fakeFcm();
    UserDeviceToken::create(['user_id' => $this->user->id, 'device_id' => 'a', 'fcm_token' => 't', 'is_active' => true]);

    SendFcmNotificationJob::dispatchSync([$this->user->id], 'Judul', 'Isi');

    Http::assertNothingSent();
});

test('token yang sudah tidak terdaftar dinonaktifkan', function () {
    ($this->configureFcm)();
    fakeFcm([Http::response(['error' => [
        'code' => 404,
        'status' => 'NOT_FOUND',
        'details' => [['@type' => 'type.googleapis.com/google.firebase.fcm.v1.FcmError', 'errorCode' => 'UNREGISTERED']],
    ]], 404)]);
    $device = UserDeviceToken::create(['user_id' => $this->user->id, 'device_id' => 'a', 'fcm_token' => 'mati', 'is_active' => true]);

    SendFcmNotificationJob::dispatchSync([$this->user->id], 'Judul', 'Isi');

    expect($device->fresh()->is_active)->toBeFalse();
});

test('error sementara hanya mengulang perangkat yang gagal', function () {
    ($this->configureFcm)();
    fakeFcm([
        Http::response(['name' => 'ok'], 200),
        Http::response(['error' => ['code' => 503, 'status' => 'UNAVAILABLE']], 503),
    ]);
    Queue::fake();
    $ok = UserDeviceToken::create(['user_id' => $this->user->id, 'device_id' => 'a', 'fcm_token' => 'ok', 'is_active' => true]);
    $busy = UserDeviceToken::create(['user_id' => $this->user->id, 'device_id' => 'b', 'fcm_token' => 'busy', 'is_active' => true]);

    (new SendFcmNotificationJob([$this->user->id], 'Judul', 'Isi'))->handle(app(FcmHttpV1Client::class));

    Queue::assertPushed(SendFcmNotificationJob::class, fn ($job) => $job->deviceIds === [$busy->id] && $job->attempt === 2);
    expect($busy->fresh()->is_active)->toBeTrue();
});

test('percobaan terakhir tidak dijadwalkan ulang', function () {
    ($this->configureFcm)();
    fakeFcm([Http::response([], 503)]);
    Queue::fake();
    $device = UserDeviceToken::create(['user_id' => $this->user->id, 'device_id' => 'a', 'fcm_token' => 'busy', 'is_active' => true]);

    (new SendFcmNotificationJob([$this->user->id], 'Judul', 'Isi', deviceIds: [$device->id], attempt: SendFcmNotificationJob::MAX_ATTEMPTS))
        ->handle(app(FcmHttpV1Client::class));

    Queue::assertNothingPushed();
});

test('access token OAuth di-cache antar pengiriman', function () {
    ($this->configureFcm)();
    fakeFcm();
    UserDeviceToken::create(['user_id' => $this->user->id, 'device_id' => 'a', 'fcm_token' => 't', 'is_active' => true]);

    SendFcmNotificationJob::dispatchSync([$this->user->id], 'Satu', 'Isi');
    SendFcmNotificationJob::dispatchSync([$this->user->id], 'Dua', 'Isi');

    Http::assertSentCount(3); // 1 OAuth exchange + 2 messages
});

test('nama channel lama dipetakan ke channel yang dibuat aplikasi Android', function (?string $in, string $out) {
    expect(SendFcmNotificationJob::androidChannel($in))->toBe($out);
})->with([
    ['attendance_alerts', 'attendance_alerts'],
    ['channel_announcement', 'general_info'],
    ['channel_class_session', 'attendance_alerts'],
    ['tidak_dikenal', 'general_info'],
    [null, 'general_info'],
]);
