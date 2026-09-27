<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Student;
use App\Support\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeofenceController extends Controller
{
    // Campus centre and radius: App\Support\School::campus(), shared by every
    // GPS check-in path.

    /**
     * Calculate Haversine distance in meters.
     */
    public static function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Verify GPS coordinates against campus radius and record clock-in.
     */
    public function checkin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'is_mock' => 'nullable|boolean',
        ]);

        if (! empty($validated['is_mock'])) {
            return response()->json([
                'success' => false,
                'status' => 'rejected',
                'message' => 'Presensi ditolak: Terdeteksi penggunaan Fake GPS / Mock Location.',
            ], 422);
        }

        $campus = School::campus();
        $distance = self::calculateHaversineDistance(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            $campus['latitude'],
            $campus['longitude']
        );

        $isInside = $distance <= $campus['radius_meters'];

        if (! $isInside) {
            return response()->json([
                'success' => false,
                'status' => 'out_of_bounds',
                'distance_meters' => round($distance, 1),
                'max_radius_meters' => $campus['radius_meters'],
                'message' => 'Presensi ditolak: Anda berada di luar radius kampus ('.round($distance, 1).'m > '.round($campus['radius_meters']).'m).',
            ], 422);
        }

        $user = $request->user();
        $today = now()->toDateString();

        $ay = AcademicYear::first() ?? AcademicYear::create([
            'year' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
        ]);

        $classroom = Classroom::first() ?? Classroom::create([
            'name' => 'XII MIPA 1',
            'grade_level' => 'XII',
            'major' => 'MIPA',
            'academic_year' => '2026/2027',
        ]);

        $student = Student::where('user_id', $user->id)->first() ?? Student::create([
            'user_id' => $user->id,
            'nisn' => '9982348'.$user->id,
            'nis' => '120'.$user->id,
            'gender' => 'L',
            'classroom_id' => $classroom->id,
        ]);

        $attendance = Attendance::firstOrCreate(
            ['student_id' => $student->id, 'date' => $today],
            [
                'academic_year_id' => $ay->id,
                'classroom_id' => $classroom->id,
                'status' => 'H',
                'recorded_by' => $user->id,
                'notes' => 'Presensi GPS Mobile ('.round($distance, 1).'m dari gerbang utama)',
            ]
        );

        return response()->json([
            'success' => true,
            'status' => 'verified',
            'distance_meters' => round($distance, 1),
            'attendance_id' => $attendance->id,
            'message' => 'Presensi GPS berhasil diverifikasi di dalam area kampus Sultan Agung.',
            'timestamp' => now()->toIso8601String(),
        ], 200);
    }
}
