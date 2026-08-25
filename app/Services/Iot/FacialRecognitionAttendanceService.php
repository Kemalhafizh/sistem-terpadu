<?php

declare(strict_types=1);

namespace App\Services\Iot;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\FaceBiometricProfile;
use App\Models\FrictionlessAttendanceLog;
use App\Models\Student;
use App\Models\User;

class FacialRecognitionAttendanceService
{
    /**
     * Enroll or update a user's 512-dimensional facial embedding vector.
     */
    public function enrollFace(User $user, array $embeddingVector): FaceBiometricProfile
    {
        $vectorHash = hash('sha256', json_encode($embeddingVector));

        return FaceBiometricProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'feature_vector_hash' => $vectorHash,
                'embedding_vector' => $embeddingVector,
                'confidence_threshold' => 0.85,
                'enrolled_at' => now(),
                'status' => 'active',
            ]
        );
    }

    /**
     * Calculate Euclidean distance between two vectors.
     */
    public static function calculateEuclideanDistance(array $v1, array $v2): float
    {
        $sum = 0.0;
        $count = min(count($v1), count($v2));
        for ($i = 0; $i < $count; $i++) {
            $diff = (float)$v1[$i] - (float)$v2[$i];
            $sum += $diff * $diff;
        }
        return sqrt($sum);
    }

    /**
     * Match captured face embedding against enrolled profiles.
     */
    public function matchAndLogAttendance(string $gateCameraId, string $gateLocation, array $capturedVector, bool $liveness = true): array
    {
        if (!$liveness) {
            return [
                'success' => false,
                'message' => 'Zero-Touch check-in ditolak: Gagal uji liveness (anti-spoofing terpicu).',
            ];
        }

        $profiles = FaceBiometricProfile::where('status', 'active')->with('user')->get();
        $bestMatch = null;
        $lowestDistance = 999.0;

        foreach ($profiles as $profile) {
            $dist = self::calculateEuclideanDistance($profile->embedding_vector, $capturedVector);
            if ($dist < $lowestDistance) {
                $lowestDistance = $dist;
                $bestMatch = $profile;
            }
        }

        // Distance < 0.6 is considered high confidence match
        $confidence = max(0.0, min(100.0, 100.0 - ($lowestDistance * 100.0)));
        $isMatch = $lowestDistance <= 0.40;

        if (!$isMatch || !$bestMatch) {
            return [
                'success' => false,
                'message' => 'Wajah tidak dikenali dalam database biometrik kampus.',
                'distance' => round($lowestDistance, 4),
            ];
        }

        $user = $bestMatch->user;

        // Log frictionless attendance
        $log = FrictionlessAttendanceLog::create([
            'user_id' => $user->id,
            'gate_camera_id' => $gateCameraId,
            'gate_location' => $gateLocation,
            'confidence_score' => round($confidence, 2),
            'liveness_verified' => true,
            'capture_timestamp' => now(),
            'status' => 'verified',
        ]);

        // Record official attendance
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
            'nisn' => '9982348' . $user->id,
            'nis' => '120' . $user->id,
            'gender' => 'L',
            'classroom_id' => $classroom->id,
        ]);

        Attendance::firstOrCreate(
            ['student_id' => $student->id, 'date' => now()->toDateString()],
            [
                'academic_year_id' => $ay->id,
                'classroom_id' => $classroom->id,
                'status' => 'H',
                'recorded_by' => $user->id,
                'notes' => "Zero-Touch Facial Gate ({$gateLocation}, {$gateCameraId})",
            ]
        );

        return [
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
            ],
            'confidence' => round($confidence, 2),
            'gate_location' => $gateLocation,
            'log_id' => $log->id,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
