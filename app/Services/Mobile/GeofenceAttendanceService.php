<?php

declare(strict_types=1);

namespace App\Services\Mobile;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Class GeofenceAttendanceService
 * 
 * Provides high-precision GPS geofencing verification, anti-mock GPS detection,
 * and rotating TOTP QR token generation for school gate and classroom attendance.
 * 
 * @package App\Services\Mobile
 */
class GeofenceAttendanceService
{
    // SMA Islam Sultan Agung 1 Semarang Coordinates (Jl. Mataram No.657, Wonodri, Semarang)
    public const CAMPUS_LATITUDE = -6.996160;
    public const CAMPUS_LONGITUDE = 110.428510;
    public const MAX_ALLOWED_RADIUS_METERS = 250.0;

    /**
     * Validate mobile GPS coordinates against campus geofence boundary.
     *
     * @param User $user
     * @param float $latitude
     * @param float $longitude
     * @param float $accuracyMeters
     * @param bool $isMockLocation
     * @param string|null $selfieProofUrl
     * @return array<string, mixed>
     */
    public function validateCampusCheckin(
        User $user,
        float $latitude,
        float $longitude,
        float $accuracyMeters,
        bool $isMockLocation = false,
        ?string $selfieProofUrl = null
    ): array {
        // 1. Anti-Mock Location Guard
        if ($isMockLocation) {
            return [
                'success' => false,
                'code' => 'MOCK_LOCATION_DETECTED',
                'message' => 'Absensi ditolak: Terdeteksi penggunaan aplikasi Fake GPS / Lokasi Palsu.',
            ];
        }

        // 2. GPS Accuracy Guard (reject if accuracy radius is wider than 80 meters)
        if ($accuracyMeters > 80.0) {
            return [
                'success' => false,
                'code' => 'INACCURATE_GPS',
                'message' => 'Sinyal GPS terlalu lemah (akurasi > 80m). Silakan pindah ke ruang terbuka.',
            ];
        }

        // 3. Precise Haversine Great-Circle Distance
        $distance = $this->calculateHaversineDistance(
            $latitude,
            $longitude,
            self::CAMPUS_LATITUDE,
            self::CAMPUS_LONGITUDE
        );

        if ($distance > self::MAX_ALLOWED_RADIUS_METERS) {
            return [
                'success' => false,
                'code' => 'OUTSIDE_GEOFENCE_RADIUS',
                'message' => 'Anda berada di luar radius kampus SMA Islam Sultan Agung 1 (Jarak: ' . round($distance) . 'm, Maksimal: ' . self::MAX_ALLOWED_RADIUS_METERS . 'm).',
                'distance_meters' => round($distance, 1),
            ];
        }

        // 4. Record Attendance for Student entity if applicable
        $student = Student::where('user_id', $user->id)->first();
        if ($student) {
            $today = Carbon::today()->toDateString();
            $attendance = Attendance::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'date' => $today,
                ],
                [
                    'status' => 'hadir',
                    'notes' => 'Absensi Mobile Geofence GPS (Radius: ' . round($distance) . 'm)',
                ]
            );

            return [
                'success' => true,
                'message' => 'Presensi kehadiran berhasil diverifikasi.',
                'distance_meters' => round($distance, 1),
                'checkin_time' => now()->toIso8601String(),
                'attendance_id' => $attendance->id,
            ];
        }

        return [
            'success' => true,
            'message' => 'Koordinat berada dalam radius kampus.',
            'distance_meters' => round($distance, 1),
            'checkin_time' => now()->toIso8601String(),
        ];
    }

    /**
     * Haversine formula to compute great-circle distance between two GPS points in meters.
     *
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float
     */
    public function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000.0; // Mean earth radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Generate Dynamic Rotating TOTP QR Code Token (Time-sliced 30 seconds).
     *
     * @param User $user
     * @return array<string, mixed>
     */
    public function generateDynamicTotpQr(User $user): array
    {
        $timeSlice = (int) floor(time() / 30);
        $secretKey = (string) config('app.key', 'SISTA_SA1_SECRET');
        $hash = hash_hmac('sha256', "USER:{$user->id}:SLICE:{$timeSlice}", $secretKey);
        
        $token = base64_encode((string) json_encode([
            'u' => $user->id,
            'r' => $user->role,
            'ts' => $timeSlice,
            'sig' => substr($hash, 0, 16),
        ]));

        return [
            'success' => true,
            'qr_token' => $token,
            'expires_in_seconds' => 30 - (time() % 30),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Verify a scanned Dynamic QR Code Token at school gate terminals.
     *
     * @param string $qrToken
     * @return array<string, mixed>
     */
    public function verifyDynamicTotpQr(string $qrToken): array
    {
        try {
            $rawJson = base64_decode($qrToken, true);
            if (!$rawJson) {
                return ['valid' => false, 'message' => 'Token QR tidak dapat diuraikan'];
            }

            $decoded = json_decode($rawJson, true);
            if (!is_array($decoded) || !isset($decoded['u'], $decoded['ts'], $decoded['sig'])) {
                return ['valid' => false, 'message' => 'Format payload QR tidak valid'];
            }

            $currentSlice = (int) floor(time() / 30);
            // Allow current slice or previous slice (tolerance window of 30 seconds)
            if (abs($currentSlice - (int) $decoded['ts']) > 1) {
                return ['valid' => false, 'message' => 'QR Code telah kadaluarsa. Silakan refresh layar HP.'];
            }

            $secretKey = (string) config('app.key', 'SISTA_SA1_SECRET');
            $expectedHash = hash_hmac('sha256', "USER:{$decoded['u']}:SLICE:{$decoded['ts']}", $secretKey);
            if (substr($expectedHash, 0, 16) !== $decoded['sig']) {
                return ['valid' => false, 'message' => 'Tanda tangan digital QR tidak valid'];
            }

            // Anti-Replay Check: Ensure this QR token hasn't been scanned already
            $replayCacheKey = 'used_totp_qr:' . $decoded['sig'];
            if (Cache::has($replayCacheKey)) {
                return ['valid' => false, 'message' => 'Token QR ini sudah pernah dipindai sebelumnya. Silakan refresh layar HP.'];
            }
            Cache::put($replayCacheKey, true, 60);

            $user = User::find($decoded['u']);
            if (!$user) {
                return ['valid' => false, 'message' => 'Pengguna tidak ditemukan di database'];
            }

            return [
                'valid' => true,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
                'verified_at' => now()->toIso8601String(),
            ];
        } catch (\Throwable $e) {
            return ['valid' => false, 'message' => 'Gagal memverifikasi QR: ' . $e->getMessage()];
        }
    }
}
