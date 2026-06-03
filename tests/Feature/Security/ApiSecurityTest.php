<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    //
});

it('melakukan rotasi token dengan menghapus token lama pada device yang sama saat login kembali', function () {
    $user = User::factory()->create([
        'email' => 'siswa@sekolah.com',
        'password' => Hash::make('password123'),
        'role' => 'student',
    ]);

    // 1. Buat token pertama untuk device "Android Phone"
    $token1 = $user->createToken('Android Phone')->plainTextToken;
    expect($user->tokens()->where('name', 'Android Phone')->count())->toBe(1);

    // 2. Lakukan login ulang dengan device yang sama
    $response = $this->postJson('/api/v1/login', [
        'email' => 'siswa@sekolah.com',
        'password' => 'password123',
        'device_name' => 'Android Phone',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data' => ['token', 'user'],
    ]);

    // 3. Verifikasi token lama sudah dihapus dan hanya tersisa 1 token baru untuk device tersebut
    expect($user->tokens()->where('name', 'Android Phone')->count())->toBe(1);

    $newToken = $response->json('data.token');
    expect($newToken)->not->toBe($token1);
});

it('dapat melakukan refresh token dengan membatalkan token saat ini dan menghasilkan token baru', function () {
    $user = User::factory()->create([
        'email' => 'wali@sekolah.com',
        'role' => 'parent',
    ]);

    $tokenResult = $user->createToken('Wali iPad');
    $token = $tokenResult->plainTextToken;

    // Panggil endpoint refresh token dengan mengirim Bearer Token lama
    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$token,
    ])->postJson('/api/v1/refresh-token');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data' => ['token'],
    ]);

    $newToken = $response->json('data.token');
    expect($newToken)->not->toBe($token);

    // Verifikasi token lama sudah di-revoke (dihapus dari DB)
    expect($user->tokens()->where('id', $tokenResult->accessToken->id)->count())->toBe(0);
    // Verifikasi token baru terbit untuk device "Wali iPad"
    expect($user->tokens()->where('name', 'Wali iPad')->count())->toBe(1);
});

it('menerapkan rate limiter ketat pada endpoint profil api', function () {
    $user = User::factory()->create([
        'email' => 'test-limiter@sekolah.com',
        'role' => 'student',
    ]);

    $token = $user->createToken('Test Device')->plainTextToken;

    // Bersihkan rate limiter state sebelum tes dijalankan
    RateLimiter::clear('throttle:30,1');

    // Lakukan request sebanyak 30 kali (sukses)
    for ($i = 0; $i < 30; $i++) {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/v1/profile');

        $response->assertStatus(200);
    }

    // Request ke-31 harus ditolak dengan HTTP Status 429 (Too Many Requests)
    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$token,
    ])->getJson('/api/v1/profile');

    $response->assertStatus(429);
});
