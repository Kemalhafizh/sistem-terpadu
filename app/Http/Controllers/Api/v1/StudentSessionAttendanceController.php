<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\v1\Concerns\RespondsForClassSessions;
use App\Http\Controllers\Controller;
use App\Services\Academic\ClassSessionService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Sesi Kelas — Siswa (FASE 117)
 *
 * Presensi mandiri per jam pelajaran dengan memindai QR dari layar guru.
 *
 * @authenticated
 */
class StudentSessionAttendanceController extends Controller
{
    use ApiResponseTrait, RespondsForClassSessions;

    public function __construct(private ClassSessionService $sessions) {}

    /**
     * Scan QR Sesi Kelas
     *
     * Penolakan membawa `error_code` (pesannya dalam bahasa pengguna): `qr_expired` (422),
     * `qr_invalid` (422), `not_enrolled` (403), `already_checked_in` (409, `data.checked_in_at`),
     * `session_ended` (409), `session_not_started` (409). Lihat docs/modules/class_sessions.md §4.
     * Scan setelah 15 menit dari jam mulai dicatat sebagai `telat`.
     *
     * @bodyParam qr_token string required Isi QR mentah. Example: 9b1f0c1e-4c5a-4d0f-9d7e-2d6c1b0a9e11.4f3c2b1a0e9d8c7b6a5f4e3d2c1b0a99
     */
    public function scanQr(Request $request): JsonResponse
    {
        return $this->handle(function () use ($request) {
            $data = $this->validateEnvelope($request, ['qr_token' => 'required|string|max:200']);
            $result = $this->sessions->scan($request->user(), $data['qr_token']);

            return $this->successResponse($result, $result['message']);
        });
    }

    /**
     * Sesi Kelas Aktif
     *
     * Sesi yang sedang berlangsung untuk kelas siswa (untuk banner "Tap untuk absen"), atau `data: null`.
     */
    public function activeSession(Request $request): JsonResponse
    {
        return $this->handle(function () use ($request) {
            $active = $this->sessions->activeSessionForStudent($request->user());

            return $this->successResponse($active, $active ? __('Ada sesi kelas yang sedang berlangsung') : __('Tidak ada sesi kelas aktif'));
        });
    }
}
