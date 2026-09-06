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
                    'Berapakah hasil dari $2^3 \\times 2^4 = 2^n$? Tentukan nilai $n$!',
                    'Jika $x + 5 = 12$, berapakah nilai dari $\\sqrt{x^2 + 15}$?',
                    'Berapakah turunan pertama dari fungsi $f(x) = 3x^2 + \\sqrt{4x}$?',
                    'Tentukan nilai limit fungsi trigonometri: $$\\lim_{x \\to 0} \\frac{\\sin(4x)}{\\tan(2x)}$$',
                    'Berapakah nilai dari $\\sin(30^\\circ) + \\cos(60^\\circ)$?',
                    'Tentukan nilai eksak dari $\\tan(45^\\circ) \\times \\cos(0^\\circ)$!',
                    'Jika matriks $A = \\begin{pmatrix} 1 & 2 \\\\ 3 & 4 \\end{pmatrix}$, tentukan nilai dari determinan $\\det(A)$!',
                    'Berapakah luas segitiga dengan panjang alas $a = 10\\text{ cm}$ dan tinggi $t = 8\\text{ cm}$?',
                    'Tentukan nilai integral tentu: $$\\int_{0}^{2} (3x^2 + 2x) \\, dx$$',
                    'Berapakah peluang munculnya mata dadu prima pada pelemparan sebuah dadu berkisi enam?',
                ]
            ],
            [
                'subject' => $fis,
                'teacher' => $guruFisika,
                'prefix' => 'FIS',
                'questions' => [
                    'Apakah satuan dari besaran Gaya dalam Sistem Internasional (SI)?',
                    'Tentukan kecepatan sesaat partikel $v(t) = \\frac{dx}{dt}$ jika posisi dirumuskan $x(t) = 5t^2 + 2t$ pada saat $t = 3\\text{ s}$!',
                    'Hukum Newton keberapa yang menjelaskan prinsip inersia kelembaman $\\sum F = 0$?',
                    'Berapakah besar energi kinetik $E_k = \\frac{1}{2} m v^2$ jika massa $m = 2\\text{ kg}$ dan kelajuan $v = 10\\text{ m/s}$?',
                    'Perhatikan diagram rangkaian listrik. Jika dua resistor $R_1 = 10\\,\\Omega$ dan $R_2 = 20\\,\\Omega$ disusun seri, berapakah hambatan penggantinya $R_{\\text{total}}$?',
                    'Sebuah benda ditarik gaya konstan $F = 20\\text{ N}$ membentuk sudut $\\theta = 60^\\circ$ sejauh $s = 5\\text{ m}$. Berapakah usaha $W = F s \\cos(\\theta)$?',
                    'Manakah di antara besaran berikut yang merupakan besaran vektor?',
                    'Berapakah frekuensi gelombang foton $f = \\frac{1}{T}$ jika periodenya $T = 0.02\\text{ detik}$?',
                    'Alat optik yang menggunakan susunan lensa objektif dan okuler untuk melihat benda mikroskopis adalah...',
                    'Tentukan panjang gelombang de Broglie $\\lambda = \\frac{h}{p}$ untuk partikel bermassa $m$ dan momentum $p$!',
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
                $qText = $sData['questions'][$baseIndex];

                // Add diagram image to some physics and math questions
                $qImage = null;
                if ($sData['prefix'] === 'FIS' && ($q % 3 == 0)) {
                    $qImage = 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb?w=600&auto=format&fit=crop&q=80';
                } elseif ($sData['prefix'] === 'MTK' && ($q % 4 == 0)) {
                    $qImage = 'https://images.unsplash.com/photo-1507668077129-56e32842fceb?w=600&auto=format&fit=crop&q=80';
                }

                $options = [
                    'A' => ['text' => 'Pilihan Jawaban A: $' . ($q * 2) . '$', 'image_url' => null],
                    'B' => ['text' => 'Pilihan Jawaban B (Kunci): $\\frac{' . ($q + 1) . '}{2}$', 'image_url' => null],
                    'C' => ['text' => 'Pilihan Jawaban C: $\\sqrt{' . ($q * 3) . '}$', 'image_url' => null],
                    'D' => ['text' => 'Pilihan Jawaban D: $' . ($q * 5) . '^2$', 'image_url' => null],
                    'E' => ['text' => 'Pilihan Jawaban E: $0$', 'image_url' => null],
                ];

                $qBank = QuestionBank::create([
                    'subject_id' => $sData['subject']->id,
                    'created_by' => $sData['teacher']->id,
                    'question_text' => $qText,
                    'question_image' => $qImage,
                    'type' => 'multiple_choice',
                    'options' => $options,
                    'correct_answer' => 'B',
                    'explanation' => 'Penjelasan untuk kunci jawaban B pada soal nomor ' . $q,
                    'difficulty' => ['easy', 'medium', 'hard'][$q % 3],
                    'grade_level' => 'XII',
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
