<?php

namespace App\Services;

use App\Models\PointCategory;
use App\Models\Student;
use App\Models\StudentPointLog;
use App\Models\WarningLetter;
use Exception;

class DisciplineService
{
    /**
     * Get discipline summary.
     *
     * Point convention (see the student_point_logs migration and
     * PointCategoryController): penalties are stored positive, rewards
     * negative, so the plain sum is the net balance that drives warning
     * letters. The violation/reward split is reported separately so the
     * app can show both without re-deriving it.
     */
    public function getSummary(Student $student)
    {
        $logs = $student->pointLogs()->get(['points', 'type', 'created_at']);

        $totalPoints = (int) $logs->sum('points');
        $status = 'Aman';

        if ($totalPoints >= WarningLetter::THRESHOLDS['SP3']) {
            $status = 'SP3 (Sangat Kritis)';
        } elseif ($totalPoints >= WarningLetter::THRESHOLDS['SP2']) {
            $status = 'SP2 (Kritis)';
        } elseif ($totalPoints >= WarningLetter::THRESHOLDS['SP1']) {
            $status = 'SP1 (Waspada)';
        } elseif ($totalPoints >= 30) {
            $status = 'Perlu Perhatian';
        }

        $penalties = $logs->where('type', 'penalty');
        $nextLevel = collect(WarningLetter::THRESHOLDS)
            ->filter(fn ($threshold) => $totalPoints < $threshold)
            ->sort()
            ->keys()
            ->first();

        return [
            'total_points' => $totalPoints,
            'status' => $status,
            'total_violation_points' => (int) $penalties->sum('points'),
            'total_reward_points' => (int) $logs->where('type', 'reward')->sum(fn ($log) => abs($log->points)),
            'total_cases' => $penalties->count(),
            // Null once every level has been reached.
            'next_warning_level' => $nextLevel,
            'next_warning_threshold' => $nextLevel ? WarningLetter::THRESHOLDS[$nextLevel] : null,
            'last_updated' => $logs->max('created_at')?->format('Y-m-d H:i:s'),
            // The school's real rule book — replaces a list hardcoded in the app.
            'rules' => PointCategory::active()
                ->orderBy('type')
                ->orderBy('name')
                ->get(['name', 'type', 'default_points'])
                ->map(fn ($category) => [
                    'name' => $category->name,
                    'type' => $category->type,
                    'points' => abs((int) $category->default_points),
                ])
                ->values(),
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

        $transform = fn ($log) => [
            'id' => $log->id,
            'points' => $log->points,
            'type' => $log->type, // penalty, reward, adjustment
            // Automatic logs (e.g. an Alpa penalty from AttendanceService) have no category.
            'category_name' => $log->category?->name,
            'description' => $log->description,
            'date' => $log->created_at->format('Y-m-d H:i:s'),
            'recorded_by' => $log->creator?->name ?? 'Sistem',
        ];

        if ($paginate) {
            $logs = $query->paginate(15);
            $logs->getCollection()->transform($transform);

            return $logs;
        }

        // A plain Collection has no getCollection() — calling it here made the
        // unpaginated (mobile) history endpoint fail on every request.
        return $query->get()->map($transform)->values();
    }

    /**
     * Get all warning letters for a student.
     */
    public function getWarningLetters(Student $student, $viewer = null)
    {
        // Same rule signWarningLetter() enforces, so the app only offers the
        // signature pad to someone whose signature the server will accept.
        $viewerIsThisParent = $viewer
            && in_array($viewer->role, ['parent', 'orangtua'], true)
            && $student->parent_id === $viewer->id;

        return WarningLetter::where('student_id', $student->id)
            ->orderBy('level', 'desc')
            ->get()
            ->map(function ($letter) use ($viewerIsThisParent) {
                return [
                    'id' => $letter->id,
                    'level' => $letter->level,
                    'point_threshold' => $letter->point_threshold,
                    'issued_at' => $letter->issued_at->format('Y-m-d'),
                    'is_signed' => ! is_null($letter->signed_at),
                    'signed_at' => $letter->signed_at ? $letter->signed_at->format('Y-m-d H:i:s') : null,
                    'notes' => $letter->notes,
                    'can_sign' => $viewerIsThisParent && is_null($letter->signed_at),
                ];
            })
            ->values();
    }

    /**
     * Sign a warning letter (Digital Signature by Parent).
     */
    public function signWarningLetter($letterId, $user, $signatureString)
    {
        $letter = WarningLetter::findOrFail($letterId);

        // Validasi: yang sign harus parent dari siswa yang bersangkutan
        if ($user->role !== 'parent' && $user->role !== 'orangtua') {
            throw new Exception('Akses ditolak. Hanya orang tua/wali yang dapat menandatangani surat peringatan.');
        }

        $student = Student::find($letter->student_id);
        if (! $student || $student->parent_id !== $user->id) {
            throw new Exception('Otorisasi gagal. Anda bukan orang tua yang terdaftar untuk siswa ini.');
        }

        if ($letter->signed_at) {
            throw new Exception('Surat peringatan ini sudah ditandatangani sebelumnya.');
        }

        if (empty($signatureString)) {
            throw new Exception('Tanda tangan digital tidak boleh kosong.');
        }

        $letter->update([
            'signed_at' => now(),
            'signed_by_parent_id' => $user->id,
            'digital_signature' => $signatureString,
        ]);

        return clone $letter;
    }
}
