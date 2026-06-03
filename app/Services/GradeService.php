<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Grade;

/**
 * Class GradeService
 *
 * Menangani logika kompleks terkait pencatatan dan pengolahan nilai siswa (Grades).
 */
class GradeService
{
    /**
     * Records bulk grades for multiple students across multiple types.
     *
     * Melakukan iterasi pada data nilai siswa dan menyimpan tipe nilai yang diisi
     * (misal: tugas, UH, PTS, PAS) ke dalam database menggunakan operasi updateOrCreate
     * agar terhindar dari duplikasi data pada tahun ajaran aktif.
     *
     * @param  array  $gradesData  Data nilai berstruktur: [student_id => ['tugas' => 80, ...]]
     * @param  int  $subjectId  ID dari mata pelajaran yang sedang dinilai
     * @param  int  $userId  ID pengguna (guru/admin) yang mencatat nilai
     * @return int Jumlah total nilai yang berhasil dicatat atau diperbarui
     *
     * @throws \Exception Jika tidak ada tahun ajaran yang sedang aktif
     */
    public function recordBulkGrades(array $gradesData, int $subjectId, int $userId): int
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (! $activeYear) {
            throw new \Exception('Tahun ajaran aktif tidak ditemukan.');
        }

        $types = ['tugas', 'uh', 'pts', 'pas'];
        $upsertData = [];
        $now = now();

        foreach ($gradesData as $studentId => $scores) {
            foreach ($types as $type) {
                if (isset($scores[$type]) && $scores[$type] !== '') {
                    $upsertData[] = [
                        'student_id' => (int) $studentId,
                        'subject_id' => $subjectId,
                        'academic_year_id' => $activeYear->id,
                        'type' => $type,
                        'score' => $scores[$type],
                        'recorded_by' => $userId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        if (empty($upsertData)) {
            return 0;
        }

        // Perform mass upsert in a single query
        Grade::upsert(
            $upsertData,
            ['student_id', 'subject_id', 'academic_year_id', 'type'],
            ['score', 'recorded_by', 'updated_at']
        );

        return count($upsertData);
    }
}
