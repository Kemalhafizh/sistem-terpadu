<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Auth\UserIdentifierResolver;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * @group Authentication
 *
 * API endpoint untuk manajemen otentikasi pengguna aplikasi mobile (Siswa/Orang Tua).
 */
class AuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * User Login
     *
     * Authenticate user dan kembalikan Bearer token.
     *
     * @bodyParam email string required Email, NIS, NISN, atau No. HP. Example: siswa@sekolah.com
     * @bodyParam password string required Password akun.
     * @bodyParam device_name string required Nama device untuk token. Example: iPhone 13
     *
     * @unauthenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Login berhasil",
     *   "data": {
     *     "token": "1|abcdef...",
     *     "user": { "name": "Budi", "email": "budi@sekolah.com", "role": "student" }
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Kredensial tidak valid"
     * }
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|max:255',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = app(UserIdentifierResolver::class)->findByIdentifier((string) $request->email);

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Kredensial tidak valid', 401);
        }

        if (($user->account_status ?? 'aktif') === 'nonaktif') {
            return $this->errorResponse('Akun Anda telah dinonaktifkan. Hubungi administrator sekolah.', 403);
        }

        // --- SANCTUM TOKEN ROTATION ---
        // Bersihkan token lama dengan nama perangkat yang sama sebelum membuat token baru
        $user->tokens()->where('name', $request->device_name)->delete();

        $token = $user->createToken($request->device_name)->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'must_change_password' => (bool) $user->must_change_password,
            ],
        ], 'Login berhasil');
    }

    /**
     * Get Profile
     *
     * Mendapatkan profil pengguna yang sedang login beserta data siswa (jika role = student).
     *
     * @authenticated
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        return $this->successResponse([
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'student_data' => $user->student ? [
                'uuid' => $user->student->uuid,
                'nis' => $user->student->nis,
            ] : null,
        ]);
    }

    /**
     * Refresh Token
     *
     * Menghapus token akses aktif saat ini dan menghasilkan token baru dengan nama perangkat yang sama.
     *
     * @authenticated
     */
    public function refreshToken(Request $request)
    {
        $user = $request->user();
        $currentToken = $user->currentAccessToken();

        if ($currentToken) {
            $deviceName = $currentToken->name;

            // Revoke current token
            $currentToken->delete();

            // Create new fresh token
            $newToken = $user->createToken($deviceName)->plainTextToken;

            return $this->successResponse([
                'token' => $newToken,
            ], 'Token berhasil di-refresh');
        }

        return $this->errorResponse('Token aktif tidak ditemukan', 400);
    }

    /**
     * User Logout
     *
     * Menghapus token akses saat ini (revoke).
     *
     * @authenticated
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logout berhasil');
    }
}
