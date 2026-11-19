<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\Student;
use App\Models\User;
use App\Services\Auth\UserIdentifierResolver;
use App\Support\Access\RoleLabel;
use App\Support\Access\StudentDataAccess;
use App\Support\Locale;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
     * @bodyParam email string required Email, NIS, NISN, No. HP, atau NIY/NUPTK. Example: siswa@sekolah.com
     * @bodyParam password string required Kata sandi pengguna.
     * @bodyParam device_name string required Nama perangkat penelusuran. Example: Android Phone
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|max:255',
            'password' => 'required',
            'device_name' => 'required|string|max:255',
        ]);

        $identifier = trim((string) $request->email);
        $deviceName = $request->input('device_name', 'Android Device');
        $user = app(UserIdentifierResolver::class)->findByIdentifier($identifier);

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->errorResponse(__('Kredensial tidak valid. Silakan periksa kembali identitas login dan password Anda.'), 401);
        }

        if (($user->account_status ?? 'aktif') === 'nonaktif') {
            return $this->errorResponse(__('Akun Anda telah dinonaktifkan. Hubungi administrator sekolah.'), 403);
        }

        // --- TOKEN ROTATION ---
        // Bersihkan token lama dengan nama perangkat yang sama sebelum membuat token baru
        $user->tokens()->where('name', $deviceName)->delete();

        $token = $user->createToken($deviceName)->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user' => [
                'uuid' => $user->uuid,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'phone_number' => $user->phone_number,
                'preferred_locale' => $user->preferred_locale,
                'must_change_password' => (bool) $user->must_change_password,
            ],
        ], __('Login berhasil'));
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

            return $this->successResponse(null, __('Logout berhasil'));
        }

        return $this->errorResponse(__('Token aktif tidak ditemukan'), 400);
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
            ], __('Token berhasil di-refresh'));
        }

        return $this->errorResponse(__('Token aktif tidak ditemukan'), 400);
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
        $role = (string) $user->role;
        $year = AcademicYear::current();

        // Fields the app already reads stay as they were; the rest sit beside them.
        // Anything the school has not filled in is null, never a placeholder.
        $studentData = null;
        if (in_array($role, StudentDataAccess::STUDENT_ROLES, true) && $user->student) {
            $student = $user->student;
            $studentData = [
                'uuid' => $student->uuid,
                'nis' => $student->nis,
                'nisn' => $student->nisn,
                'classroom' => $student->classroom?->name,
                'classroom_id' => $student->classroom_id,
            ];
        }

        $employeeData = null;
        if ($user->employee) {
            $employeeData = [
                'employee_number' => $user->employee->employee_number,
                'position' => $user->employee->position,
            ];
        }

        $children = null;
        if (in_array($role, StudentDataAccess::PARENT_ROLES, true)) {
            $children = $user->children()->with(['user:id,name', 'classroom:id,name'])->orderBy('id')->get()
                ->map(fn (Student $child) => [
                    'uuid' => $child->uuid,
                    'name' => $child->user?->name,
                    'classroom' => $child->classroom?->name,
                ])->values();
        }

        $homeroom = null;
        if (in_array($role, [...StudentDataAccess::TEACHER_ROLES, 'wali_kelas'], true)) {
            $homeroom = Classroom::where('teacher_id', $user->id)
                ->when($year, fn ($q) => $q->where('academic_year', $year->year))
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Classroom $classroom) => ['id' => $classroom->id, 'name' => $classroom->name])
                ->values();
        }

        return $this->successResponse([
            'uuid' => $user->uuid,
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'role' => $role,
            'role_label' => RoleLabel::of($role),
            // id/en/ar, saved by the web or the app (defaults to id).
            'preferred_locale' => Locale::isSupported($user->preferred_locale) ? $user->preferred_locale : config('app.fallback_locale'),
            'must_change_password' => (bool) $user->must_change_password,
            'student_data' => $studentData,
            'employee_data' => $employeeData,
            'children' => $children,
            'homeroom_classrooms' => $homeroom,
            'academic_year' => $year ? [
                'id' => $year->id,
                'year' => $year->year,
                'semester' => $year->semester,
            ] : null,
        ], __('Profil berhasil diambil'));
    }

    /**
     * Update Password
     *
     * Mengubah kata sandi akun aktif dan membersihkan flag wajib ganti sandi (`must_change_password`).
     *
     * @authenticated
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();
        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
        ])->save();

        $currentTokenId = $user->currentAccessToken()?->id;
        if ($currentTokenId !== null) {
            $user->tokens()->where('id', '!=', $currentTokenId)->delete();
        }

        return $this->successResponse([
            'must_change_password' => false,
        ], __('Kata sandi berhasil diperbarui.'));
    }

    /**
     * Set Language
     *
     * Saves the language for this account (id, en or ar). The web reads the
     * same column, so the choice follows the user to every client.
     *
     * @authenticated
     */
    public function updateLocale(Request $request)
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(Locale::SUPPORTED)],
        ]);

        $request->user()->update(['preferred_locale' => $validated['locale']]);
        app()->setLocale($validated['locale']);

        return $this->successResponse(['locale' => $validated['locale']], __('Bahasa berhasil diubah.'));
    }
}
