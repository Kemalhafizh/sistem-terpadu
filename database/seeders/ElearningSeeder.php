<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\ElearningAnnouncement;
use App\Models\ElearningAssignment;
use App\Models\ElearningClass;
use App\Models\ElearningMaterial;
use App\Models\ElearningMaterialComment;
use App\Models\ElearningMaterialView;
use App\Models\ElearningSubmission;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ElearningSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate tables
        ElearningMaterialComment::truncate();
        ElearningMaterialView::truncate();
        ElearningAnnouncement::truncate();
        ElearningSubmission::truncate();
        ElearningAssignment::truncate();
        ElearningMaterial::truncate();
        ElearningClass::truncate();

        // Get classrooms and subjects
        $classXA = Classroom::where('name', 'X-A')->first();
        $classXB = Classroom::where('name', 'X-B')->first();
        $classXIA = Classroom::where('name', 'XI-A')->first();
        $classXIIA = Classroom::where('name', 'XII-A')->first();
        $classXIIB = Classroom::where('name', 'XII-B')->first();

        $mtk = Subject::where('code', 'MTK')->first();
        $big = Subject::where('code', 'BIG')->first();
        $fis = Subject::where('code', 'FIS')->first();
        $pai = Subject::where('code', 'PAI')->first();
        $sej = Subject::where('code', 'SEJ')->first();

        $guruMtk = User::where('email', 'guru.matematika@sultanagung1.sch.id')->first();
        $guruBahasa = User::where('email', 'guru.bahasa@sultanagung1.sch.id')->first();
        $guruFisika = User::where('email', 'guru.fisika@sultanagung1.sch.id')->first();
        $guruPai = User::where('email', 'guru.pai@sultanagung1.sch.id')->first();
        $guruSejarah = User::where('email', 'guru.sejarah@sultanagung1.sch.id')->first();

        // Define 5 active classes
        $classesData = [
            [
                'subject_id' => $mtk->id,
                'classroom_id' => $classXA->id,
                'teacher_id' => $guruMtk->id,
                'name' => 'Matematika Aljabar Kelas X-A',
                'description' => 'Mata pelajaran matematika dasar mencakup persamaan kuadrat, eksponen, dan logaritma.',
                'materials' => [
                    ['title' => 'Pengenalan Eksponen & Sifatnya', 'desc' => 'PDF ringkasan materi dan rumus eksponen.'],
                    ['title' => 'Persamaan Kuadrat & Metode Faktorisasi', 'desc' => 'Video penjelasan beserta PDF soal latihan.'],
                    ['title' => 'Logaritma Dasar & Sifat-sifat Operasi', 'desc' => 'Modul lengkap logaritma.'],
                    ['title' => 'Pertidaksamaan Nilai Mutlak', 'desc' => 'Slide presentasi materi nilai mutlak.'],
                ],
                'assignments' => [
                    ['title' => 'Tugas 1: Latihan Eksponen', 'desc' => 'Kerjakan soal halaman 12-15 di buku paket.'],
                    ['title' => 'Kuis 1: Persamaan Kuadrat', 'desc' => 'Kuis online 5 soal esai singkat.'],
                ],
                'announcements' => [
                    'Selamat datang di kelas Matematika Aljabar. Silakan download materi eksponen pertama.',
                    'Pengingat: Tugas Latihan Eksponen dikumpulkan paling lambat besok malam ya.',
                ],
            ],
            [
                'subject_id' => $big->id,
                'classroom_id' => $classXB->id,
                'teacher_id' => $guruBahasa->id,
                'name' => 'Bahasa Inggris: Recount Text X-B',
                'description' => 'E-learning bahasa Inggris membahas teks recount, struktur bahasa, dan descriptive text.',
                'materials' => [
                    ['title' => 'Introduction to Recount Text', 'desc' => 'What is recount text? Structure and characteristics.'],
                    ['title' => 'Past Tense & Time Connectors', 'desc' => 'Grammar review for writing recount text.'],
                    ['title' => 'Sample of Recount Texts', 'desc' => 'Reading comprehension materials.'],
                    ['title' => 'Descriptive Text Overview', 'desc' => 'How to describe places and historical events.'],
                ],
                'assignments' => [
                    ['title' => 'Assignment 1: Write a Personal Experience', 'desc' => 'Write a recount text about your last holiday (min 150 words).'],
                    ['title' => 'Assignment 2: Reading Comprehension', 'desc' => 'Answer the questions based on the text provided.'],
                ],
                'announcements' => [
                    'Please read the past tense grammar summary before writing your personal experience recount text.',
                ],
            ],
            [
                'subject_id' => $fis->id,
                'classroom_id' => $classXIA->id,
                'teacher_id' => $guruFisika->id,
                'name' => 'Fisika Mekanika Klasik XI-A',
                'description' => 'Kelas Fisika membahas Dinamika Rotasi, Kesetimbangan Benda Tegar, dan Elastisitas.',
                'materials' => [
                    ['title' => 'Dinamika Rotasi & Momen Inersia', 'desc' => 'Materi torsi dan momen inersia berbagai benda.'],
                    ['title' => 'Kesetimbangan Benda Tegar', 'desc' => 'Prinsip kesetimbangan statis.'],
                    ['title' => 'Hukum Hooke & Elastisitas Bahan', 'desc' => 'Modul praktikum mandiri di rumah.'],
                    ['title' => 'Fluida Statis & Dinamis', 'desc' => 'Hukum Pascal, Archimedes, dan Bernoulli.'],
                ],
                'assignments' => [
                    ['title' => 'Tugas Mandiri: Soal Momen Inersia', 'desc' => 'Selesaikan 5 soal hitungan berikut jalan penyelesaiannya.'],
                    ['title' => 'Laporan Praktikum: Hukum Hooke', 'desc' => 'Unggah laporan praktikum elastisitas pegas format PDF.'],
                ],
                'announcements' => [
                    'Praktikum Hukum Hooke dikerjakan secara individu menggunakan simulator lab virtual PHET.',
                ],
            ],
            [
                'subject_id' => $pai->id,
                'classroom_id' => $classXIIA->id,
                'teacher_id' => $guruPai->id,
                'name' => 'PAI & Budi Pekerti XII-A',
                'description' => 'Membahas bab Mawaris (Hukum Waris Islam), Munakahat (Pernikahan), dan Aqidah Akhlak.',
                'materials' => [
                    ['title' => 'Pengantar Hukum Waris (Mawaris)', 'desc' => 'Pembagian harta pusaka dan ahli waris.'],
                    ['title' => 'Munakahat: Rukun & Syarat Pernikahan', 'desc' => 'Materi fiqih pernikahan dalam Islam.'],
                    ['title' => 'Etika & Akhlak Berbangsa', 'desc' => 'Karakter Khaira Ummah dalam kehidupan bernegara.'],
                    ['title' => 'Islam di Indonesia & Perkembangannya', 'desc' => 'Sejarah masuknya dakwah Islam di Nusantara.'],
                ],
                'assignments' => [
                    ['title' => 'Tugas Mawaris: Simulasi Kasus Waris', 'desc' => 'Hitunglah bagian masing-masing ahli waris dari studi kasus terlampir.'],
                    ['title' => 'Review Ceramah Munakahat', 'desc' => 'Tulis rangkuman esai hikmah pernikahan.'],
                ],
                'announcements' => [
                    'Ujian Sumatif Bab Mawaris akan diadakan minggu depan. Persiapkan diri dengan membaca modul warisan.',
                ],
            ],
            [
                'subject_id' => $sej->id,
                'classroom_id' => $classXIIB->id,
                'teacher_id' => $guruSejarah->id,
                'name' => 'Sejarah Indonesia Kontemporer XII-B',
                'description' => 'Membahas Sejarah Indonesia Pasca Kemerdekaan, Orde Baru, dan Masa Reformasi.',
                'materials' => [
                    ['title' => 'Demokrasi Liberal & Terpimpin', 'desc' => 'Peta politik Indonesia pasca kemerdekaan.'],
                    ['title' => 'Lahirnya Orde Baru & Supersemar', 'desc' => 'Transisi kekuasaan dan stabilitas politik.'],
                    ['title' => 'Era Reformasi 1998', 'desc' => 'Latar belakang runtuhnya Orde Baru dan awal reformasi.'],
                    ['title' => 'Kebijakan Politik Luar Negeri Bebas Aktif', 'desc' => 'Peran Indonesia dalam KAA dan ASEAN.'],
                ],
                'assignments' => [
                    ['title' => 'Analisis Krisis Moneter 1998', 'desc' => 'Buat esai analisis penyebab krisis moneter 1997-1998.'],
                    ['title' => 'Tugas Mandiri: Tokoh Reformasi', 'desc' => 'Tulis biografi singkat salah satu tokoh pelopor reformasi.'],
                ],
                'announcements' => [
                    'Silakan tonton dokumenter reformasi 1998 yang saya sematkan di materi log.',
                ],
            ],
        ];

        foreach ($classesData as $classInfo) {
            $eclass = ElearningClass::create([
                'uuid' => (string) Str::uuid(),
                'subject_id' => $classInfo['subject_id'],
                'classroom_id' => $classInfo['classroom_id'],
                'teacher_id' => $classInfo['teacher_id'],
                'name' => $classInfo['name'],
                'description' => $classInfo['description'],
                'is_archived' => false,
            ]);

            // Seed Announcements
            foreach ($classInfo['announcements'] as $annContent) {
                ElearningAnnouncement::create([
                    'uuid' => (string) Str::uuid(),
                    'elearning_class_id' => $eclass->id,
                    'user_id' => $classInfo['teacher_id'],
                    'content' => $annContent,
                ]);
            }

            // Seed Materials (20 materials total)
            $createdMaterials = [];
            foreach ($classInfo['materials'] as $mat) {
                $material = ElearningMaterial::create([
                    'uuid' => (string) Str::uuid(),
                    'elearning_class_id' => $eclass->id,
                    'title' => $mat['title'],
                    'description' => $mat['desc'],
                    'file_path' => 'materials/sample_pdf.pdf',
                    'video_url' => rand(0, 1) ? 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' : null,
                ]);
                $createdMaterials[] = $material;
            }

            // Seed Assignments (10 assignments total)
            foreach ($classInfo['assignments'] as $idx => $assign) {
                $isSecure = $idx === 1; // Second assignment in each class runs in Secure Mode
                $assignment = ElearningAssignment::create([
                    'uuid' => (string) Str::uuid(),
                    'elearning_class_id' => $eclass->id,
                    'title' => $assign['title'],
                    'description' => $assign['desc'],
                    'due_at' => now()->addDays(rand(2, 10)),
                    'allow_late' => rand(0, 1) ? true : false,
                    'is_secure_mode' => $isSecure,
                    'max_violations' => $isSecure ? 3 : 0,
                ]);

                // Seed Submissions (~50 submissions total, around 12 per assignment across target classrooms)
                $classroomStudents = Classroom::find($classInfo['classroom_id'])->students;
                $submittingStudents = $classroomStudents->random(min(12, $classroomStudents->count()));

                foreach ($submittingStudents as $student) {
                    $status = ['submitted', 'graded', 'graded', 'locked_cheated'][rand(0, 3)];

                    if ($status === 'locked_cheated') {
                        $score = null;
                        $feedback = 'Tugas dikunci karena melanggar batas perpindahan halaman ujian/tugas (Secure Mode).';
                        $violationCount = $isSecure ? $assignment->max_violations : 1;
                    } elseif ($status === 'graded') {
                        $score = rand(70, 95);
                        $feedback = 'Kerja bagus, pertahankan kualitas tugas Anda.';
                        $violationCount = $isSecure ? rand(0, max(0, $assignment->max_violations - 1)) : 0;
                    } else { // submitted
                        $score = null;
                        $feedback = null;
                        $violationCount = $isSecure ? rand(0, max(0, $assignment->max_violations - 1)) : 0;
                    }

                    ElearningSubmission::create([
                        'elearning_assignment_id' => $assignment->id,
                        'student_id' => $student->id,
                        'submitted_at' => now()->subDays(rand(1, 5)),
                        'file_path' => 'submissions/'.$student->nis.'_tugas.pdf',
                        'score' => $score,
                        'feedback' => $feedback,
                        'status' => $status,
                        'violation_count' => $violationCount,
                    ]);
                }
            }

            // Seed Material Views & Comments for students in this class (OUTSIDE assignments loop)
            $classroomStudents = Classroom::find($classInfo['classroom_id'])->students;
            foreach ($classroomStudents as $student) {
                // Seed views for 70% of materials
                foreach ($createdMaterials as $material) {
                    if (rand(0, 10) < 7) {
                        ElearningMaterialView::create([
                            'student_id' => $student->id,
                            'elearning_material_id' => $material->id,
                            'viewed_at' => now()->subDays(rand(1, 4)),
                        ]);

                        // Seed discussion comments (15% chance per viewed material)
                        if (rand(0, 100) < 15) {
                            ElearningMaterialComment::create([
                                'uuid' => (string) Str::uuid(),
                                'elearning_material_id' => $material->id,
                                'user_id' => $student->user_id,
                                'content' => ['Saya kurang paham rumus ini.', 'Izin menanyakan pdf halaman 3 pak.', 'Sangat jelas penjelasannya bu.', 'Apakah materi ini keluar di UTS nanti?'][rand(0, 3)],
                            ]);

                            // Guru replies
                            if (rand(0, 1) === 1) {
                                ElearningMaterialComment::create([
                                    'uuid' => (string) Str::uuid(),
                                    'elearning_material_id' => $material->id,
                                    'user_id' => $classInfo['teacher_id'],
                                    'content' => ['Ya, pelajari baik-baik.', 'Benar sekali.', 'Bisa dibaca ulang di modul pendukung ya.', 'Ya, materi ini masuk kisi-kisi UTS.'][rand(0, 3)],
                                ]);
                            }
                        }
                    }
                }
            }
        }

        // Seed 1 Archived class from previous semester
        ElearningClass::create([
            'uuid' => (string) Str::uuid(),
            'subject_id' => $mtk->id,
            'classroom_id' => $classXA->id,
            'teacher_id' => $guruMtk->id,
            'name' => '[Arsip] Matematika Dasar Semester Ganjil',
            'description' => 'Kelas matematika dasar tahun ajaran lalu.',
            'is_archived' => true,
        ]);
    }
}
