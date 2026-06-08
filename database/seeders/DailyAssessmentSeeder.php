<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\DailyAssessment;
use App\Models\DailyAssessmentScore;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class DailyAssessmentSeeder extends Seeder
{
    public function run(): void
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $classrooms = Classroom::with('students')->get();
        $subjects = Subject::all();

        // Mapping subjects to teachers
        $guruMtk = User::where('email', 'guru.matematika@sultanagung1.sch.id')->first();
        $guruFisika = User::where('email', 'guru.fisika@sultanagung1.sch.id')->first();
        $guruBahasa = User::where('email', 'guru.bahasa@sultanagung1.sch.id')->first();
        $guruPai = User::where('email', 'guru.pai@sultanagung1.sch.id')->first();
        $guruTahfidz = User::where('email', 'guru.tahfidz@sultanagung1.sch.id')->first();
        $guruKimia = User::where('email', 'guru.kimia@sultanagung1.sch.id')->first();
        $guruBiologi = User::where('email', 'guru.biologi@sultanagung1.sch.id')->first();
        $guruSejarah = User::where('email', 'guru.sejarah@sultanagung1.sch.id')->first();
        $guruEkonomi = User::where('email', 'guru.ekonomi@sultanagung1.sch.id')->first();

        $subjectTeachers = [
            'MTK' => $guruMtk,
            'FIS' => $guruFisika,
            'KIM' => $guruKimia,
            'BIO' => $guruBiologi,
            'BIG' => $guruBahasa,
            'BIN' => $guruBahasa,
            'SEJ' => $guruSejarah,
            'EKO' => $guruEkonomi,
            'PAI' => $guruPai,
            'THF' => $guruTahfidz,
            'AQH' => $guruPai,
        ];

        $assessmentTitles = [
            'ulangan_harian' => [
                'Ulangan Harian BAB 1: Konsep Dasar',
                'Ulangan Harian BAB 2: Analisis Kasus',
                'Ulangan Harian BAB 3: Evaluasi Akhir',
            ],
            'kuis' => [
                'Kuis Ringkas Pertemuan 3',
                'Kuis Cepat Pemahaman Materi',
            ],
            'praktikum' => [
                'Laporan Praktikum Laboratorium',
                'Uji Keterampilan Praktek',
            ],
            'project' => [
                'Tugas Proyek Kelompok Kreatif',
                'Mini Project Mandiri',
            ],
            'presentasi' => [
                'Presentasi Studi Kasus',
            ],
            'tugas' => [
                'Tugas Mandiri Struktur Soal',
                'Tugas Review Buku & Artikel',
            ],
        ];

        // Ensure tables are cleared
        DailyAssessmentScore::truncate();
        DailyAssessment::truncate();

        foreach ($classrooms as $classroom) {
            $students = $classroom->students;
            if ($students->isEmpty()) {
                continue;
            }

            // Create 10 assessments for each classroom across random subjects
            $assignedSubjects = $subjects->whereIn('code', array_keys($subjectTeachers))->random(6);

            foreach ($assignedSubjects as $subject) {
                $teacher = $subjectTeachers[$subject->code] ?? $guruMtk;
                $types = ['ulangan_harian', 'kuis', 'praktikum', 'project', 'presentasi', 'tugas'];

                // Create 2 assessments for this subject in this classroom
                for ($k = 1; $k <= 2; $k++) {
                    $type = $types[array_rand($types)];
                    $titles = $assessmentTitles[$type];
                    $title = $titles[array_rand($titles)] . ' - ' . $subject->name;

                    $kkm = $subject->kkm ?: 75;

                    $assessment = DailyAssessment::create([
                        'subject_id' => $subject->id,
                        'classroom_id' => $classroom->id,
                        'teacher_id' => $teacher->id,
                        'academic_year_id' => $activeYear->id,
                        'title' => $title,
                        'type' => $type,
                        'assessment_date' => now()->subDays(rand(1, 45))->format('Y-m-d'),
                        'max_score' => 100,
                        'kkm' => $kkm,
                        'description' => 'Penilaian berkelanjutan modul ' . $subject->name . ' untuk melacak pemahaman belajar siswa.',
                    ]);

                    // Seed scores for all students in this classroom
                    foreach ($students as $student) {
                        $score = rand(55, 99); // Generates some below KKM and some above
                        $needsRemedial = $score < $kkm;
                        
                        $remedialScore = null;
                        $remedialStatus = 'not_needed';
                        $notes = null;

                        if ($needsRemedial) {
                            $remedialStatus = 'pending';
                            // 70% chance they completed remedial
                            if (rand(1, 100) <= 70) {
                                $remedialScore = rand($kkm, $kkm + 10); // Remedial score typically caps at KKM or slightly above
                                $remedialStatus = 'completed';
                                $notes = 'Telah menyelesaikan remedial dan lulus KKM.';
                            } else {
                                $notes = 'Belum mengikuti ujian remedial ulang.';
                            }
                        }

                        DailyAssessmentScore::create([
                            'daily_assessment_id' => $assessment->id,
                            'student_id' => $student->id,
                            'score' => $score,
                            'needs_remedial' => $needsRemedial,
                            'remedial_score' => $remedialScore,
                            'remedial_status' => $remedialStatus,
                            'notes' => $notes,
                        ]);
                    }
                }
            }
        }
    }
}
