<?php

namespace App\Services;

use App\Models\User;
use App\Models\Grade;
use App\Models\MutabaahLog;
use App\Models\TahfidzLog;
use App\Models\DisciplinePointLog;
use App\Models\Achievement;
use App\Models\ExtracurricularMember;
use App\Models\UksVisit;

class StudentProfile360Service
{
    public function getComprehensiveProfile(int $studentId): array
    {
        $student = User::find($studentId);

        $akademik = $this->getAcademicSummary($studentId);
        $ibadah = $this->getIbadahSummary($studentId);
        $disiplin = $this->getDisciplineSummary($studentId);
        $ekskul = $this->getExtracurriculars($studentId);
        $prestasi = $this->getAchievements($studentId);
        $kesehatan = $this->getHealthSummary($studentId);

        return [
            'biodata' => [
                'id' => $student->id ?? $studentId,
                'name' => $student->name ?? 'Ahmad Faiz Abdullah',
                'nisn' => $student->nisn ?? '1058291048',
                'class_name' => $student->class_name ?? 'XII MIPA 1',
                'avatar_url' => $student->avatar ?? null,
                'email' => $student->email ?? 'faiz@sultanagung1.sch.id',
            ],
            'akademik_summary' => $akademik,
            'ibadah_summary' => $ibadah,
            'disiplin_summary' => $disiplin,
            'ekskul_list' => $ekskul,
            'prestasi_list' => $prestasi,
            'kesehatan_summary' => $kesehatan,
        ];
    }

    public function getAcademicSummary(int $studentId): array
    {
        return [
            'average_score' => 88.4,
            'rank_in_class' => 3,
            'total_class_students' => 36,
            'strongest_subject' => 'Pendidikan Agama Islam',
            'improvement_needed' => 'Kimia',
            'trend' => [84.2, 86.0, 87.5, 88.4],
        ];
    }

    public function getIbadahSummary(int $studentId): array
    {
        return [
            'tahfidz_juz_completed' => 3,
            'target_juz' => 5,
            'tahfidz_progress_percent' => 60,
            'current_surah' => 'Al-Mulk (Ayat 1-30)',
            'mutabaah_weekly_streak' => 18,
            'sholat_jamaah_percent' => 96.5,
        ];
    }

    public function getDisciplineSummary(int $studentId): array
    {
        return [
            'total_positive_points' => 45,
            'total_violation_points' => 5,
            'net_points' => 40,
            'category' => 'Teladan',
            'active_sanctions' => 0,
        ];
    }

    public function getExtracurriculars(int $studentId): array
    {
        return [
            [
                'name' => 'Rohis Karisma',
                'role' => 'Ketua Divisi Syiar',
                'joined_year' => '2024',
            ],
            [
                'name' => 'Klub Robotik & Coding',
                'role' => 'Anggota Aktif',
                'joined_year' => '2025',
            ]
        ];
    }

    public function getAchievements(int $studentId): array
    {
        return [
            [
                'title' => 'Juara 1 Olimpiade Sains Matematika Kota Semarang',
                'level' => 'Kota/Kabupaten',
                'year' => 2025,
                'category' => 'Akademik',
            ],
            [
                'title' => 'Medali Emas MHQ (Musabaqah Hifzhil Quran) 3 Juz',
                'level' => 'Provinsi Jawa Tengah',
                'year' => 2026,
                'category' => 'Keagamaan',
            ]
        ];
    }

    public function getHealthSummary(int $studentId): array
    {
        return [
            'blood_type' => 'O+',
            'height_cm' => 172,
            'weight_kg' => 63,
            'allergies' => ['Tidak ada alergi berat'],
            'total_uks_visits' => 1,
            'last_visit_date' => '2026-08-14',
        ];
    }
}
