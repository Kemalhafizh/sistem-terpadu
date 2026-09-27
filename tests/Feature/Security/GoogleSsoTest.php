<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;

beforeEach(function () {
    // Bersihkan database / environment jika diperlukan
});

it('mengalihkan pengguna ke google auth screen', function () {
    $response = $this->get(route('auth.google'));

    $response->assertRedirect();
    $location = $response->headers->get('Location');
    expect($location)->toContain('accounts.google.com');
});

it('berhasil login via google sso jika email sudah terdaftar di sistem', function () {
    $user = User::factory()->create([
        'email' => 'guru@smaisultanagung1.sch.id',
    ]);

    // Mocking user google
    $googleUser = Mockery::mock('Laravel\Socialite\Two\User');
    $googleUser->token = 'google-access-token-xyz';
    $googleUser->shouldReceive('getId')->andReturn('google-id-12345');
    $googleUser->shouldReceive('getEmail')->andReturn('guru@smaisultanagung1.sch.id');

    // Mocking socialite driver
    $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
    $provider->shouldReceive('user')->andReturn($googleUser);

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $response = $this->get('/auth/google/callback');

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);

    $user->refresh();
    expect($user->google_id)->toBe('google-id-12345');
    expect($user->google_token)->toBe('google-access-token-xyz');
    expect(session('two_factor_verified'))->toBeTrue();
});

it('gagal login via google sso jika email tidak terdaftar di sistem', function () {
    // Mocking user google dengan email yang tidak ada di DB
    $googleUser = Mockery::mock('Laravel\Socialite\Two\User');
    $googleUser->token = 'google-access-token-abc';
    $googleUser->shouldReceive('getId')->andReturn('google-id-67890');
    $googleUser->shouldReceive('getEmail')->andReturn('orangasing@gmail.com');

    $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
    $provider->shouldReceive('user')->andReturn($googleUser);

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $response = $this->get('/auth/google/callback');

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('menolak login google sso jika email tidak cocok dengan domain sekolah yang diwajibkan', function () {
    // Set domain wajib ke smaisultanagung1.sch.id (lewat config, bukan env:
    // env() kosong di produksi setelah `config:cache`)
    config(['services.google.sso_domain' => 'smaisultanagung1.sch.id']);

    // The account exists, so only the domain rule can stop this login.
    User::factory()->create(['email' => 'siswa.nakal@gmail.com']);

    // Mocking user google dengan domain personal gmail.com
    $googleUser = Mockery::mock('Laravel\Socialite\Two\User');
    $googleUser->token = 'google-access-token-def';
    $googleUser->shouldReceive('getId')->andReturn('google-id-112233');
    $googleUser->shouldReceive('getEmail')->andReturn('siswa.nakal@gmail.com');

    $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
    $provider->shouldReceive('user')->andReturn($googleUser);

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $response = $this->get('/auth/google/callback');

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors('email');
    $this->assertGuest();

});
