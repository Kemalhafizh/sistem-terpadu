<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleSsoController extends Controller
{
    /**
     * Redirect user ke Google OAuth.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle callback dari Google OAuth.
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Gagal melakukan otentikasi dengan Google. Silakan coba lagi.',
            ]);
        }

        $email = $googleUser->getEmail();

        // 1. Validasi Domain Sekolah (GOOGLE_SSO_DOMAIN → config/services.php).
        // Must go through config(): env() is null after `config:cache`, which
        // silently switched this restriction off in production.
        $allowedDomain = config('services.google.sso_domain');
        if ($allowedDomain) {
            $emailDomain = Str::after($email, '@');
            if (strcasecmp($emailDomain, $allowedDomain) !== 0) {
                return redirect()->route('login')->withErrors([
                    'email' => "Akses ditolak. Anda hanya diperbolehkan masuk menggunakan akun email dengan domain @{$allowedDomain}.",
                ]);
            }
        }

        // 2. Cari user berdasarkan email Google
        $user = User::where('email', $email)->first();

        if (! $user) {
            return redirect()->route('login')->withErrors([
                'email' => 'Email Google Anda tidak terdaftar dalam Sistem Terpadu.',
            ]);
        }

        if (($user->account_status ?? 'aktif') === 'nonaktif') {
            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda telah dinonaktifkan. Hubungi administrator sekolah.',
            ]);
        }

        // 3. Update google_id & google_token
        $user->update([
            'google_id' => $googleUser->getId(),
            'google_token' => $googleUser->token,
        ]);

        // 4. Lakukan login ke aplikasi
        Auth::login($user);

        // 5. Bypass 2FA OTP (Google SSO bertindak sebagai verifikator keamanan tepercaya)
        session(['two_factor_verified' => true]);

        return redirect()->intended('/dashboard')->with('success', 'Login via Google SSO berhasil.');
    }
}
