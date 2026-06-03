<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    public function index()
    {
        return view('auth.two-factor');
    }

    public function store(Request $request)
    {
        $request->validate([
            'two_factor_code' => 'required|integer',
        ]);

        $user = auth()->user();
        $inputCode = $request->input('two_factor_code');

        // Master code bypass for local development
        if (app()->environment('local') && $inputCode == '000000') {
            $user->resetTwoFactorCode();

            return redirect()->route('dashboard');
        }

        if ($inputCode == $user->two_factor_code) {
            $user->resetTwoFactorCode();

            return redirect()->route('dashboard');
        }

        return redirect()->back()->withErrors(['two_factor_code' => 'Kode verifikasi yang Anda masukkan salah.']);
    }

    public function resend()
    {
        $user = auth()->user();
        $user->generateTwoFactorCode();
        $user->notify(new TwoFactorCodeNotification);

        return redirect()->back()->with('success', 'Kode verifikasi baru telah dikirim ke email Anda.');
    }
}
