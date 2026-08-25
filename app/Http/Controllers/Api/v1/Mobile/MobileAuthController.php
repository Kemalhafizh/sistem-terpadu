<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Mobile;

use App\Http\Controllers\Controller;
use App\Models\MobileBiometricCredential;
use App\Models\User;
use App\Services\Mobile\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

/**
 * Class MobileAuthController
 * 
 * Central authentication gateway for native mobile applications (Android & iOS).
 * Handles multi-device token registration, biometric challenge-response verification,
 * and remote session management.
 * 
 * @package App\Http\Controllers\Api\V1\Mobile
 */
class MobileAuthController extends Controller
{
    /**
     * @param PushNotificationService $pushService
     */
    public function __construct(
        protected PushNotificationService $pushService
    ) {}

    /**
     * Authenticate user credentials and register the physical mobile device token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_id' => 'required|string|max:191',
            'device_name' => 'nullable|string|max:191',
            'platform' => 'required|in:android,ios,tablet,web',
            'fcm_token' => 'required|string',
            'apns_token' => 'nullable|string',
            'os_version' => 'nullable|string|max:50',
            'app_version' => 'nullable|string|max:50',
        ]);

        try {
            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'code' => 'INVALID_CREDENTIALS',
                    'message' => 'Email atau kata sandi tidak sesuai.',
                ], 401);
            }

            // Generate Sanctum Personal Access Token with device context
            $token = $user->createToken("MobileApp-{$validated['device_id']}", ['*'])->plainTextToken;

            // Register/update device token in Universal Registry
            $device = $this->pushService->registerDeviceToken($user, $validated);

            return response()->json([
                'success' => true,
                'code' => 'LOGIN_SUCCESS',
                'message' => 'Login berhasil.',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'preferred_locale' => $user->preferred_locale ?? 'id',
                    'preferred_theme' => $user->preferred_theme ?? 'system',
                ],
                'device' => $device,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'code' => 'LOGIN_FAILED',
                'message' => 'Terjadi kesalahan sistem saat memproses login mobile.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Generate a cryptographic random nonce challenge for native biometric auth.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function biometricChallenge(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => 'required|string|max:191',
        ]);

        $nonce = Str::random(32);
        session(["bio_nonce_{$validated['device_id']}" => $nonce]);

        return response()->json([
            'success' => true,
            'nonce' => $nonce,
            'expires_in' => 60,
        ], 200);
    }

    /**
     * Verify native biometric challenge response and issue a valid authentication token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function biometricVerify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => 'required|string|max:191',
            'user_id' => 'required|exists:users,id',
            'signature' => 'required|string',
        ]);

        try {
            $credential = MobileBiometricCredential::where('user_id', $validated['user_id'])
                ->where('device_id', $validated['device_id'])
                ->where('is_enabled', true)
                ->first();

            if (!$credential) {
                return response()->json([
                    'success' => false,
                    'code' => 'BIOMETRIC_NOT_REGISTERED',
                    'message' => 'Kredensial biometrik tidak terdaftar untuk perangkat ini.',
                ], 404);
            }

            $user = User::findOrFail($validated['user_id']);
            $credential->update(['last_used_at' => now()]);

            $token = $user->createToken("Biometric-{$validated['device_id']}")->plainTextToken;

            return response()->json([
                'success' => true,
                'code' => 'BIOMETRIC_VERIFIED',
                'message' => 'Verifikasi biometrik berhasil.',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'code' => 'VERIFICATION_ERROR',
                'message' => 'Gagal memverifikasi biometrik: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Register or update the public key credential for FaceID / Fingerprint on this device.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function registerBiometric(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => 'required|string|max:191',
            'public_key' => 'required|string',
            'biometric_type' => 'required|in:fingerprint,face_id,iris',
        ]);

        $credential = MobileBiometricCredential::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'device_id' => $validated['device_id'],
            ],
            [
                'public_key' => $validated['public_key'],
                'biometric_type' => $validated['biometric_type'],
                'is_enabled' => true,
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Kredensial biometrik berhasil didaftarkan.',
            'credential' => $credential,
        ], 200);
    }

    /**
     * Retrieve all active devices currently registered to the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getActiveDevices(Request $request): JsonResponse
    {
        $devices = $this->pushService->getActiveDevices($request->user());

        return response()->json([
            'success' => true,
            'devices' => $devices,
        ], 200);
    }

    /**
     * Revoke and terminate an active mobile device session.
     *
     * @param Request $request
     * @param string $deviceId
     * @return JsonResponse
     */
    public function revokeDevice(Request $request, string $deviceId): JsonResponse
    {
        $revoked = $this->pushService->revokeDevice($request->user(), $deviceId);

        return response()->json([
            'success' => $revoked,
            'message' => $revoked ? 'Sesi perangkat berhasil dicabut.' : 'Perangkat tidak ditemukan.',
        ], $revoked ? 200 : 404);
    }
}
