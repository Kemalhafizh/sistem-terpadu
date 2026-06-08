<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\QuestionBank;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CbtSeeder extends Seeder
{
    public function run(): void
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $admin = User::where('role', 'admin')->first();

        $mtk = Subject::where('code', 'MTK')->first();
        $fis = Subject::where('code', 'FIS')->first();
        $pai = Subject::where('code', 'PAI')->first();

        $guruMtk = User::where('email', 'guru.matematika@sultanagung1.sch.id')->first();
        $guruFisika = User::where('email', 'guru.fisika@sultanagung1.sch.id')->first();
        $guruPai = User::where('email', 'guru.pai@sultanagung1.sch.id')->first();

        // Clear existing tables
        ExamAttempt::truncate();
        \DB::table('exam_questions')->truncate();
        QuestionBank::truncate();
        Exam::truncate();

        // 1. Create 30 Questions for Matematika, 30 for Fisika, 30 for PAI
        $subjectsData = [
            [
                'subject' => $mtk,
                'teacher' => $guruMtk,
                'prefix' => 'MTK',
                'questions' => [
                    'Berapakah hasil dari 2^3 * 2^4?', 'Jika x + 5 = 12, berapakah nilai x?',
                    'Berapakah turunan pertama dari f(x) = 3x^2 + 2x?', 'Tentukan nilai limit x mendekati 3 dari (x^2 - 9)/(x - 3).',
                    'Berapakah nilai dari cos(60 derajat)?', 'Tentukan nilai sin(30 derajat) + cos(60 derajat).',
                    'Jika matriks A = [1 2; 3 4], tentukan determinan A.', 'Berapakah luas segitiga dengan alas 10 cm dan tinggi 8 cm?',
                    'Tentukan simpangan baku dari data: 2, 4, 6, 8.', 'Berapakah peluang munculnya mata dadu genap pada pelemparan sebuah dadu?',
                ]
            ],
            [
                'subject' => $fis,
                'teacher' => $guruFisika,
                'prefix' => 'FIS',
                'questions' => [
                    'Apakah satuan dari gaya dalam Sistem Internasional?', 'Tentukan kecepatan benda yang menempuh 100 m dalam 5 detik.',
                    'Hukum Newton keberapa yang menjelaskan tentang inersia?', 'Berapakah energi kinetik benda bermassa 2 kg dengan kecepatan 10 m/s?',
                    'Tentukan besar gaya gravitasi jika massa bumi 6x10^24 kg.', 'Sebuah benda ditarik dengan gaya 20 N sehingga berpindah 5 m. Berapakah usaha?',
                    'Manakah yang merupakan besaran vektor?', 'Berapakah frekuensi gelombang dengan periode 0.02 detik?',
                    'Alat optik yang digunakan untuk melihat benda mikro adalah...', 'Tentukan hambatan pengganti jika dua resistor 10 ohm disusun seri.',
                ]
            ],
            [
                'subject' => $pai,
                'teacher' => $guruPai,
                'prefix' => 'PAI',
                'questions' => [
                    'Siapakah nama malaikat pembawa wahyu?', 'Surah pertama yang diturunkan kepada Nabi Muhammad SAW adalah...',
                    'Berapakah jumlah rukun iman dalam Islam?', 'Zakat fitrah dibayarkan pada bulan...',
                    'Hukum menikah bagi orang yang sudah mampu secara fisik dan finansial adalah...', 'Siapakah khalifah pertama setelah wafatnya Rasulullah?',
                    'Berapa jumlah rakaat shalat fardhu dalam sehari semalam?', 'Perang yang terjadi pada tahun 2 Hijriah adalah perang...',
                    'Manakah yang termasuk akhlak mazmumah (tercela)?', 'Kitab suci yang diturunkan kepada Nabi Daud AS adalah...',
                ]
            ],
        ];

        $questionsMap = [];

        foreach ($subjectsData as $sData) {
            $questionsMap[$sData['prefix']] = [];
            // Create 30 questions for this subject by repeating/customizing templates
            for ($q = 1; $q <= 30; $q++) {
                $baseIndex = ($q - 1) % count($sData['questions']);
                $qText = $sData['questions'][$baseIndex] . ' [Soal No. ' . $q . ']';

                $qBank = QuestionBank::create([
                    'subject_id' => $sData['subject']->id,
                    'created_by' => $sData['teacher']->id,
                    'question_text' => $qText,
                    'type' => 'multiple_choice',
                    'options' => [
                        'A' => 'Jawaban Pilihan A untuk pertanyaan nomor ' . $q,
                        'B' => 'Jawaban Pilihan B (Kunci Jawaban) ' . $q,
                        'C' => 'Jawaban Pilihan C untuk pertanyaan nomor ' . $q,
                        'D' => 'Jawaban Pilihan D untuk pertanyaan nomor ' . $q,
                    ],
                    'correct_answer' => 'B',
                    'explanation' => 'Penjelasan untuk kunci jawaban B pada soal nomor ' . $q,
                    'difficulty' => ['easy', 'medium', 'hard'][$q % 3],
                    'grade_level' => 'X',
                ]);
                $questionsMap[$sData['prefix']][] = $qBank;
            }
        }

        // 2. Create 3 Exams
        $exams = [];
        $examsData = [
            [
                'title' => 'Penilaian Akhir Semester Matematika Genap X',
                'subject' => $mtk,
                'prefix' => 'MTK',
                'teacher' => $guruMtk,
            ],
            [
                'title' => 'Penilaian Akhir Semester Fisika Genap XI',
                'subject' => $fis,
                'prefix' => 'FIS',
                'teacher' => $guruFisika,
            ],
            [
                'title' => 'Penilaian Akhir Semester PAI Genap XII',
                'subject' => $pai,
                'prefix' => 'PAI',
                'teacher' => $guruPai,
            ],
        ];

        foreach ($examsData as $idx => $eData) {
            $exam = Exam::create([
                'uuid' => (string) Str::uuid(),
                'title' => $eData['title'],
                'subject_id' => $eData['subject']->id,
                'classroom_id' => Classroom::skip($idx)->first()->id, // Assign to first 3 classrooms X-A, X-B, XI-A
                'academic_year_id' => $activeYear->id,
                'created_by' => $eData['teacher']->id,
                'type' => 'uas',
                'start_time' => now()->subDays(5)->startOfDay()->addHours(8),
                'end_time' => now()->addDays(5)->endOfDay(),
                'duration_minutes' => 90,
                'total_questions' => 30,
                'shuffle_questions' => true,
                'shuffle_options' => false,
                'is_secure_mode' => true,
                'max_violations' => 3,
                'passing_score' => 75.00,
                'status' => 'published',
            ]);

            // Link 30 questions to the exam
            $qList = $questionsMap[$eData['prefix']];
            foreach ($qList as $qIdx => $qBank) {
                $exam->questions()->attach($qBank->id, [
                    'order' => $qIdx + 1,
                    'weight' => 3.33,
                ]);
            }
            $exams[] = $exam;
        }

        // 3. Seed Attempts (~120 attempts, 40 attempts per exam)
        // All students from all classrooms can attempt the exams to simulate massive participation
        $allStudents = Student::all();

        foreach ($exams as $examIdx => $exam) {
            // Pick 40 random students for this exam to get 3 * 40 = 120 attempts
            $attemptingStudents = $allStudents->random(40);

            foreach ($attemptingStudents as $student) {
                $score = rand(60, 100);
                // Calculate correct/wrong/unanswered counts based on score
                $correctCount = round(($score / 100) * 30);
                $wrongCount = 30 - $correctCount;
                $unansweredCount = 0;

                // Simulate tab switching violations
                $violationCount = rand(0, 3);
                $violationLogs = [];
                for ($v = 0; $v < $violationCount; $v++) {
                    $violationLogs[] = [
                        'timestamp' => now()->subMinutes(rand(5, 45))->toIso8601String(),
                        'reason' => 'User switched tabs or minimized browser window.',
                    ];
                }

                // Simulate answer options selected
                $answers = [];
                for ($ans = 1; $ans <= 30; $ans++) {
                    // Pick correct key 'B' mostly, or other keys randomly
                    $selected = rand(1, 100) <= ($score - 10) ? 'B' : ['A', 'C', 'D'][rand(0, 2)];
                    $answers[$ans] = $selected;
                }

                // Shuffle question ordering array
                $questionOrder = range(1, 30);
                shuffle($questionOrder);

                ExamAttempt::create([
                    'uuid' => (string) Str::uuid(),
                    'exam_id' => $exam->id,
                    'student_id' => $student->id,
                    'answers' => $answers,
                    'question_order' => $questionOrder,
                    'score' => $score,
                    'correct_count' => $correctCount,
                    'wrong_count' => $wrongCount,
                    'unanswered_count' => $unansweredCount,
                    'started_at' => now()->subDays(2)->subHours(rand(1, 48)),
                    'submitted_at' => now()->subDays(2)->subHours(rand(1, 48))->addMinutes(rand(30, 85)),
                    'status' => 'submitted',
                    'violation_count' => $violationCount,
                    'violation_logs' => $violationLogs,
                    'is_auto_submitted' => $violationCount >= 3 ? true : false,
                ]);
            }
        }
    }
}
