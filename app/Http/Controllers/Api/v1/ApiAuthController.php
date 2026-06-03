<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * @group Authentication v1
 *
 * API endpoint untuk mengelola autentikasi berbasis Sanctum token untuk aplikasi mobile.
 */
class ApiAuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * User Login
     *
     * Autentikasi kredensial pengguna dan dapatkan token.
     * Menerapkan rotasi token berbasis nama perangkat (device_name).
     *
     * @bodyParam email string required Email pengguna. Example: siswa@sekolah.com
     * @bodyParam password string required Kata sandi pengguna. Example: password123
     * @bodyParam device_name string required Nama perangkat penelusuran. Example: Android Phone
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required|string|max:255',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Kredensial tidak valid', 401);
        }

        // --- TOKEN ROTATION ---
        // Bersihkan token lama dengan nama perangkat yang sama sebelum membuat token baru
        $user->tokens()->where('name', $request->device_name)->delete();

        $token = $user->createToken($request->device_name)->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user' => [
                'uuid' => $user->uuid,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 'Login berhasil');
    }

    /**
     * User Logout
     *
     * Mencabut token aktif saat ini.
     *
     * @authenticated
     */
    public function logout(Request $request)
    {
        $currentToken = $request->user()->currentAccessToken();
        if ($currentToken) {
            $currentToken->delete();
            return $this->successResponse(null, 'Logout berhasil');
        }

        return $this->errorResponse('Token aktif tidak ditemukan', 400);
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
     * Get Current Profile (Me)
     *
     * Mengambil ringkasan profil lengkap pengguna saat ini.
     *
     * @authenticated
     */
    public function me(Request $request)
    {
        $user = $request->user();

        $studentData = null;
        if ($user->role === 'student' && $user->student) {
            $studentData = [
                'uuid' => $user->student->uuid,
                'nis' => $user->student->nis,
                'classroom' => $user->student->classroom->name ?? 'N/A',
            ];
        }

        return $this->successResponse([
            'uuid' => $user->uuid,
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'role' => $user->role,
            'student_data' => $studentData,
        ], 'Profil berhasil diambil');
    }
}
