<?php

namespace App\Services\Inclusive;

use App\Models\AccommodationRegistry;
use Illuminate\Support\Collection;

class AccessibilityAccommodationService
{
    public function enforceExamTimeExtensions(int $studentId): int
    {
        $registry = AccommodationRegistry::where('student_id', $studentId)
            ->where('accommodation_category', 'exam')
            ->where('is_active', true)
            ->first();

        if ($registry && str_contains(strtolower($registry->specific_need), 'waktu')) {
            return 30; // 30 minutes extra
        }

        return 0;
    }

    public function flagMissingAccommodations(int $studentId, array $providedAccommodations): array
    {
        $required = AccommodationRegistry::where('student_id', $studentId)
            ->where('is_active', true)
            ->pluck('specific_need')
            ->toArray();

        return array_diff($required, $providedAccommodations);
    }

    public function generateTeacherAlerts(int $studentId): Collection
    {
        return AccommodationRegistry::where('student_id', $studentId)
            ->where('is_active', true)
            ->get()
            ->map(function ($acc) {
                return "Peringatan: Siswa membutuhkan akomodasi {$acc->accommodation_category} - {$acc->specific_need}";
            });
    }
}
