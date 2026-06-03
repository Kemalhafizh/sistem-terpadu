<?php

namespace App\Http\Middleware;

use App\Models\ActiveSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateActiveSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $sessionId = $request->session()->getId();
            
            // Check if we need to register or update the session due to regeneration
            if ($request->hasSession() && $request->session()->get('active_session_id') !== $sessionId) {
                $oldSessionId = $request->session()->get('active_session_id');
                
                if ($oldSessionId && ActiveSession::where('session_id', $oldSessionId)->exists()) {
                    ActiveSession::where('session_id', $oldSessionId)->update([
                        'session_id' => $sessionId,
                        'last_activity_at' => now(),
                    ]);
                    $request->session()->put('active_session_id', $sessionId);
                }
            }
            
            // Periksa apakah sesi masih terdaftar di database
            $activeSession = ActiveSession::where('session_id', $sessionId)->first();

            if (!$activeSession) {
                // Sesi tidak ditemukan. Jika dalam pengujian dan flag tidak aktif, lewati.
                if (app()->environment('testing') && !config('security.test_force_logout')) {
                    return $next($request);
                }

                // Sesi telah di-terminate oleh administrator -> Logout Paksa!
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'Sesi aktif Anda telah diberhentikan oleh administrator sekolah demi alasan keamanan.');
            }

            // Perbarui waktu aktivitas terakhir
            $activeSession->update([
                'last_activity_at' => now(),
            ]);
        }

        return $next($request);
    }
}
