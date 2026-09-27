<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Setting;
use App\Models\StudentPointLog;
use App\Notifications\StudentAttendanceNotification;
use App\Support\School;
use Illuminate\Support\Facades\DB;

/**
 * Class AttendanceService
 *
 * Menangani logika bisnis yang berkaitan dengan pencatatan absensi harian
 * siswa dan integrasinya dengan log poin (seperti penalti BK).
 */
class AttendanceService
{
    /**
     * Record bulk attendance and handle point integration.
     *
     * Menyimpan data presensi secara massal untuk satu kelas pada tanggal tertentu.
     * Jika status presensi adalah 'Alpa' (A), sistem akan otomatis mencatat
     * log penalti poin BK untuk siswa tersebut.
     *
     * @throws \Exception
     */
    public function recordBulkAttendance(int $classroomId, string $date, array $attendancesData, int $recordedBy): void
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (! $activeYear) {
            throw new \Exception('Tahun ajaran aktif tidak ditemukan.');
        }

        DB::transaction(function () use ($classroomId, $date, $attendancesData, $recordedBy, $activeYear) {
            foreach ($attendancesData as $studentId => $data) {
                $attendance = Attendance::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'classroom_id' => $classroomId,
                        'date' => $date,
                        'schedule_id' => null, // Daily attendance
                    ],
                    [
                        'academic_year_id' => $activeYear->id,
                        'status' => $data['status'],
                        'notes' => $data['notes'] ?? null,
                        'recorded_by' => $recordedBy,
                    ]
                );

                // BK POINT INTEGRATION: Auto-log penalty if Alpa (A)
                if ($attendance->wasRecentlyCreated || $attendance->wasChanged('status')) {
                    if ($data['status'] === 'A') {
                        StudentPointLog::create([
                            'student_id' => $studentId,
                            'created_by' => $recordedBy,
                            'points' => 10,
                            'type' => 'penalty',
                            'description' => 'Bolos / Tanpa Keterangan pada tanggal '.$date,
                        ]);

                        // Send Attendance Alert to Parent
                        $student = $attendance->student;
                        if ($student && $student->parent) {
                            $student->parent->notify(new StudentAttendanceNotification($attendance));
                        }
                    }
                }
            }
        });
    }

    /**
     * Calculate distance between two GPS coordinates in meters (Haversine Formula).
     */
    public function calculateDistanceInMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Verify if given GPS location is within SMA Islam Sultan Agung 1 Semarang Geofence radius.
     */
    public function verifySchoolGeofence(float $latitude, float $longitude): array
    {
        $campus = School::campus();

        $schoolLat = $campus['latitude'];
        $schoolLng = $campus['longitude'];
        $allowedRadius = $campus['radius_meters'];

        $distance = $this->calculateDistanceInMeters($latitude, $longitude, $schoolLat, $schoolLng);
        $isWithinGeofence = $distance <= $allowedRadius;

        return [
            'is_valid' => $isWithinGeofence,
            'distance_meters' => round($distance, 2),
            'allowed_radius_meters' => $allowedRadius,
            'school_name' => School::name(),
            // Only a link the admin actually saved (the old fallback link was unverified).
            'maps_url' => Setting::get('school_maps_url') ?: null,
            'latitude' => $schoolLat,
            'longitude' => $schoolLng,
        ];
    }
}
