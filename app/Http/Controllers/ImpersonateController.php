<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
    /**
     * Take over another user's account.
     */
    public function take(User $user)
    {
        /** @var User $admin */
        $admin = Auth::user();

        // Security check
        if (! $admin || ! $admin->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        // Prevent impersonating fellow admins for security stability
        if ($user->isAdmin()) {
            return back()->with('error', 'Anda tidak dapat menyamar sebagai sesama Admin.');
        }

        // Store original admin ID and start time in session
        session([
            'impersonate_by' => Auth::id(),
            'impersonate_start_time' => now(),
        ]);

        // Log activity before session changes
        ActivityLog::create([
            'user_id' => Auth::id(),
            'event' => 'impersonate_start',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Login as target user
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', "Anda sekarang menyamar sebagai {$user->name}");
    }

    /**
     * Return to the original admin account.
     */
    public function leave()
    {
        if (! session()->has('impersonate_by')) {
            return redirect()->route('dashboard');
        }

        $admin = User::find(session('impersonate_by'));

        if ($admin) {
            // Log activity after returning
            ActivityLog::create([
                'user_id' => $admin->id,
                'event' => 'impersonate_end',
                'auditable_type' => User::class,
                'auditable_id' => Auth::id(), // the user that was impersonated
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            Auth::login($admin);
            session()->forget(['impersonate_by', 'impersonate_start_time']);

            return redirect()->route('users.index')->with('success', 'Anda telah kembali ke akun Admin.');
        }

        abort(500, 'Gagal kembali ke akun Admin.');
    }
}
