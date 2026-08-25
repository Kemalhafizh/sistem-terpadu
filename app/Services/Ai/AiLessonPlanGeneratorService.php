<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Models\GeneratedLessonPlan;
use App\Models\User;

class AiLessonPlanGeneratorService
{
    /**
     * Generate structured Lesson Plan (RPP / Modul Ajar) aligned with Kurikulum Merdeka.
     */
    public function generateLessonPlan(User $teacher, string $subjectName, string $gradeLevel, string $topic): GeneratedLessonPlan
    {
        $objectives = [
            "Siswa mampu menjelaskan prinsip dasar {$topic} dengan kalimat sendiri.",
            "Siswa dapat menganalisis studi kasus nyata dan mengidentifikasi penerapan {$topic}.",
            "Siswa dapat mempresentasikan solusi pemecahan masalah secara kolaboratif.",
        ];

        $steps = [
            'pendahuluan' => [
                'duration_minutes' => 15,
                'activities' => [
                    'Pembukaan dengan salam dan doa bersama (Profil Pelajar Pancasila Beriman/Bertaqwa).',
                    'Apersepsi dan pemantik diskusi awal seputar ' . $topic,
                ],
            ],
            'inti' => [
                'duration_minutes' => 60,
                'activities' => [
                    'Eksplorasi materi secara mandiri / microlearning.',
                    'Diskusi kelompok dan pengerjaan lembar kerja interaktif.',
                    'Presentasi hasil analisis dan refleksi kelompok.',
                ],
            ],
            'penutup' => [
                'duration_minutes' => 15,
                'activities' => [
                    'Menyimpulkan poin-poin utama bersama guru.',
                    'Kuis formatif singkat (3 soal) dan tindak lanjut tugas rumah.',
                ],
            ],
        ];

        $rubric = [
            'pemahaman_konsep' => ['weight' => 40, 'criteria' => 'Ketepatan penjelasan konsep dan argumen ilmiah.'],
            'keaktifan_kolaborasi' => ['weight' => 30, 'criteria' => 'Partisipasi dalam diskusi kelompok dan kepemimpinan.'],
            'keterampilan_komunikasi' => ['weight' => 30, 'criteria' => 'Kejelasan penyampaian ide saat presentasi.'],
        ];

        return GeneratedLessonPlan::create([
            'teacher_id' => $teacher->id,
            'subject_name' => $subjectName,
            'grade_level' => $gradeLevel,
            'topic' => $topic,
            'curriculum_type' => 'Kurikulum Merdeka',
            'learning_objectives' => $objectives,
            'pedagogical_steps' => $steps,
            'assessment_rubric' => $rubric,
            'status' => 'draft',
        ]);
    }
}
