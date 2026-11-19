<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\WhatsappPasswordResetService;
use Illuminate\Http\Request;

class WhatsappPasswordResetController extends Controller
{
    public function __construct(
        protected WhatsappPasswordResetService $resetService
    ) {}

    public function showForm()
    {
        return view('auth.forgot-password-whatsapp', [
            'whatsappAvailable' => $this->resetService->isAvailable(),
        ]);
    }

    public function requestOtp(Request $request)
    {
        $wantsJson = $request->expectsJson() || $request->is('api/*');
        $validated = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
        ]);

        try {
            $result = $this->resetService->requestOtp($validated['identifier']);
        } catch (\DomainException|\RuntimeException $e) {
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withInput()->withErrors(['identifier' => $e->getMessage()]);
        }

        if ($wantsJson) {
            return response()->json([
                'success' => true,
                'message' => __('Kode OTP telah dikirim ke nomor WhatsApp terdaftar.'),
                'data' => $result,
            ]);
        }

        return back()
            ->withInput(['identifier' => $validated['identifier']])
            ->with('otp_sent', $result)
            ->with('status', __('Kode OTP dikirim ke WhatsApp :phone (berlaku :min menit).', [
                'phone' => $result['phone_masked'],
                'min' => $result['expires_in_minutes'],
            ]));
    }

    public function resetWithOtp(Request $request)
    {
        $wantsJson = $request->expectsJson() || $request->is('api/*');
        $validated = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
            'otp' => ['required', 'string', 'min:4', 'max:10'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $this->resetService->resetWithOtp(
                $validated['identifier'],
                $validated['otp'],
                $validated['password']
            );
        } catch (\DomainException $e) {
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withInput()->withErrors(['otp' => $e->getMessage()]);
        }

        if ($wantsJson) {
            return response()->json([
                'success' => true,
                'message' => __('Kata sandi berhasil direset melalui OTP WhatsApp. Silakan login kembali.'),
            ]);
        }

        return redirect()->route('login')->with('status', __('Kata sandi berhasil diperbarui. Silakan masuk dengan kata sandi baru Anda.'));
    }
}
