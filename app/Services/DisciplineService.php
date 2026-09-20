<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentPointLog;
use App\Models\WarningLetter;
use Exception;

class DisciplineService
{
    /**
     * Get discipline summary.
     */
    public function getSummary(Student $student)
    {
        $totalPoints = $student->total_points;
        $status = 'Aman';
        
        if ($totalPoints >= 100) $status = 'SP3 (Sangat Kritis)';
        elseif ($totalPoints >= 75) $status = 'SP2 (Kritis)';
        elseif ($totalPoints >= 50) $status = 'SP1 (Waspada)';
        elseif ($totalPoints >= 30) $status = 'Perlu Perhatian';

        return [
            'total_points' => $totalPoints,
            'status' => $status,
            'last_updated' => $student->pointLogs()->latest()->first()?->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get discipline history logs.
     */
    public function getHistory(Student $student, $paginate = true)
    {
        $query = StudentPointLog::with('category', 'creator:id,name')
            ->where('student_id', $student->id)
            ->orderBy('created_at', 'desc');

        if ($paginate) {
            $logs = $query->paginate(15);
        } else {
            $logs = $query->get();
        }

        // Transform for API
        $logs->getCollection()->transform(function ($log) {
            return [
                'id' => $log->id,
                'points' => $log->points,
                'type' => $log->type, // 'violation' or 'achievement'
                'category_name' => $log->category ? $log->category->name : 'N/A',
                'description' => $log->description,
                'date' => $log->created_at->format('Y-m-d H:i:s'),
                'recorded_by' => $log->creator ? $log->creator->name : 'System',
            ];
        });

        return $logs;
    }

    /**
     * Get all warning letters for a student.
     */
    public function getWarningLetters(Student $student)
    {
        return WarningLetter::where('student_id', $student->id)
            ->orderBy('level', 'desc')
            ->get()
            ->map(function ($letter) {
                return [
                    'id' => $letter->id,
                    'level' => $letter->level,
                    'point_threshold' => $letter->point_threshold,
                    'issued_at' => $letter->issued_at->format('Y-m-d'),
                    'is_signed' => !is_null($letter->signed_at),
                    'signed_at' => $letter->signed_at ? $letter->signed_at->format('Y-m-d H:i:s') : null,
                    'notes' => $letter->notes,
                ];
            });
    }

    /**
     * Sign a warning letter (Digital Signature by Parent).
     */
    public function signWarningLetter($letterId, $user, $signatureString)
    {
        $letter = WarningLetter::findOrFail($letterId);

        // Validasi: yang sign harus parent dari siswa yang bersangkutan
        if ($user->role !== 'parent' && $user->role !== 'orangtua') {
            throw new Exception("Akses ditolak. Hanya orang tua/wali yang dapat menandatangani surat peringatan.");
        }

        $student = Student::find($letter->student_id);
        if (!$student || $student->parent_id !== $user->id) {
            throw new Exception("Otorisasi gagal. Anda bukan orang tua yang terdaftar untuk siswa ini.");
        }

        if ($letter->signed_at) {
            throw new Exception("Surat peringatan ini sudah ditandatangani sebelumnya.");
        }

        if (empty($signatureString)) {
            throw new Exception("Tanda tangan digital tidak boleh kosong.");
        }

        $letter->update([
            'signed_at' => now(),
            'signed_by_parent_id' => $user->id,
            'digital_signature' => $signatureString,
        ]);

        return clone $letter;
    }
}
