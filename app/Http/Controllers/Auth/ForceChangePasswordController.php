<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ForceChangePasswordController extends Controller
{
    /**
     * Show the force change password form.
     */
    public function create()
    {
        return view('auth.force-change-password');
    }

    /**
     * Update the user's password.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        // The password will be automatically hashed by the 'hashed' cast in the User model.
        // We do not need to use bcrypt() manually here.
        $user->password = $validated['password'];
        $user->must_change_password = false;
        $user->save();

        return redirect()->route('dashboard')->with('success', 'Password berhasil diubah! Selamat datang.');
    }
}
