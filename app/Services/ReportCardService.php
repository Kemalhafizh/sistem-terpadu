<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\ReportCard;
use App\Models\Student;

/**
 * Class ReportCardService
 *
 * Bertanggung jawab untuk mengkalkulasi dan melakukan sinkronisasi data rapor
 * (Report Cards) siswa dalam suatu kelas pada tahun ajaran tertentu.
 */
class ReportCardService
{
    /**
     * Calculate and sync report cards for a specific classroom and academic year.
     *
     * Proses yang dilakukan:
     * 1. Mengambil seluruh nilai siswa di kelas tersebut.
     * 2. Menghitung total dan rata-rata nilai.
     * 3. Mengurutkan siswa berdasarkan total nilai untuk menentukan ranking.
     * 4. Menyimpan hasil perhitungan ke tabel report_cards.
     *
     * @param  int  $classroomId  ID kelas
     * @param  int  $academicYearId  ID tahun ajaran
     */
    public function syncClassroomReportCards(int $classroomId, int $academicYearId): void
    {
        $students = Student::where('classroom_id', $classroomId)->get();
        $calculatedData = [];

        // 1. Calculate Scores
        foreach ($students as $student) {
            $grades = Grade::where('student_id', $student->id)
                ->where('academic_year_id', $academicYearId)
                ->get();

            $avg = $grades->count() > 0 ? $grades->avg('score') : 0;
            $total = $grades->sum('score');

            $calculatedData[$student->id] = [
                'total' => $total,
                'avg' => $avg,
            ];
        }

        // 2. Determine Ranking
        uasort($calculatedData, function ($a, $b) {
            return $b['total'] <=> $a['total'];
        });

        $rank = 1;
        foreach ($calculatedData as $studentId => $data) {
            $calculatedData[$studentId]['rank'] = $rank;

            // 3. Persist to Database
            ReportCard::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'academic_year_id' => $academicYearId,
                    'classroom_id' => $classroomId,
                ],
                [
                    'total_score' => $data['total'],
                    'average_score' => $data['avg'],
                    'rank_in_class' => $rank,
                ]
            );
            $rank++;
        }
    }
}
