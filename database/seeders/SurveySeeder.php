<?php

namespace Database\Seeders;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Database\Seeder;

class SurveySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $studentUsers = User::where('role', 'student')->get();
        $parentUsers = User::where('role', 'parent')->get();

        SurveyResponse::truncate();
        SurveyQuestion::truncate();
        Survey::truncate();

        // 1. Create Survey 1 (For Students)
        $survey1 = Survey::create([
            'title' => 'Evaluasi Kepuasan Pembelajaran E-Learning SISTA',
            'description' => 'Mohon berikan umpan balik Anda mengenai efektivitas penggunaan platform E-Learning SISTA selama semester ini.',
            'created_by' => $admin->id,
            'status' => 'active',
            'target_role' => 'student',
            'is_anonymous' => false,
            'start_date' => now()->subDays(5),
            'end_date' => now()->addDays(15),
            'max_responses' => 100,
            'send_notification' => false,
        ]);

        $s1q1 = SurveyQuestion::create([
            'survey_id' => $survey1->id,
            'question_text' => 'Seberapa mudah Anda mengakses materi dan tugas di e-learning SISTA?',
            'type' => 'scale',
            'is_required' => true,
            'order' => 1,
            'scale_min' => 1,
            'scale_max' => 5,
            'scale_min_label' => 'Sangat Sulit',
            'scale_max_label' => 'Sangat Mudah',
        ]);

        $s1q2 = SurveyQuestion::create([
            'survey_id' => $survey1->id,
            'question_text' => 'Fitur apa yang paling sering Anda gunakan?',
            'type' => 'select',
            'options' => ['Membaca Materi PDF', 'Mengerjakan Tugas / Assignment', 'Menjawab Ujian CBT', 'Diskusi Forum Kelas'],
            'is_required' => true,
            'order' => 2,
        ]);

        $s1q3 = SurveyQuestion::create([
            'survey_id' => $survey1->id,
            'question_text' => 'Berikan kritik atau saran Anda untuk pengembangan e-learning SISTA ke depan.',
            'type' => 'text',
            'is_required' => false,
            'order' => 3,
        ]);

        // 2. Create Survey 2 (For Parents, Anonymous)
        $survey2 = Survey::create([
            'title' => 'Kuesioner Fasilitas & Sarana Prasarana Sekolah',
            'description' => 'Evaluasi fasilitas fisik sekolah: ruang kelas, laboratorium, masjid, dan toilet demi kenyamanan putra-putri Anda.',
            'created_by' => $admin->id,
            'status' => 'active',
            'target_role' => 'parent',
            'is_anonymous' => true,
            'start_date' => now()->subDays(10),
            'end_date' => now()->addDays(10),
            'max_responses' => 100,
            'send_notification' => false,
        ]);

        $s2q1 = SurveyQuestion::create([
            'survey_id' => $survey2->id,
            'question_text' => 'Bagaimana penilaian Anda terhadap kebersihan lingkungan sekolah secara umum?',
            'type' => 'scale',
            'is_required' => true,
            'order' => 1,
            'scale_min' => 1,
            'scale_max' => 5,
            'scale_min_label' => 'Sangat Kotor',
            'scale_max_label' => 'Sangat Bersih',
        ]);

        $s2q2 = SurveyQuestion::create([
            'survey_id' => $survey2->id,
            'question_text' => 'Fasilitas apa yang menurut Anda paling mendesak untuk ditingkatkan?',
            'type' => 'select',
            'options' => ['Pendingin Ruangan (AC) Kelas', 'Buku Perpustakaan Fisik', 'Alat Praktikum Lab IPA', 'Toilet & Fasilitas Wudhu'],
            'is_required' => true,
            'order' => 2,
        ]);

        $s2q3 = SurveyQuestion::create([
            'survey_id' => $survey2->id,
            'question_text' => 'Ada saran tambahan terkait sarana prasarana sekolah?',
            'type' => 'text',
            'is_required' => false,
            'order' => 3,
        ]);

        // 3. Seed 30 Responses for Survey 1 (from Students)
        $studentResponses = 30;
        $s1FeedbackText = [
            'E-learning sangat membantu belajar di rumah.',
            'Tampilan SISTA sangat modern, saya suka.',
            'Koneksi kadang lambat saat jam sibuk pagi hari.',
            'Fitur forum diskusi perlu diperbanyak materinya.',
            'CBT online sudah sangat aman dari kecurangan.',
            'Mohon batas ukuran upload file tugas ditambah.',
            'Sudah sangat baik, tidak ada keluhan.',
        ];

        for ($i = 0; $i < $studentResponses; $i++) {
            $student = $studentUsers[$i] ?? $studentUsers->random();
            SurveyResponse::create([
                'survey_id' => $survey1->id,
                'user_id' => $student->id,
                'answers' => [
                    $s1q1->id => rand(3, 5),
                    $s1q2->id => ['Membaca Materi PDF', 'Mengerjakan Tugas / Assignment', 'Menjawab Ujian CBT', 'Diskusi Forum Kelas'][rand(0, 3)],
                    $s1q3->id => rand(1, 100) <= 60 ? $s1FeedbackText[array_rand($s1FeedbackText)] : '',
                ],
                'ip_address' => '192.168.1.'.rand(10, 254),
            ]);
        }

        // 4. Seed 25 Responses for Survey 2 (from Parents, Anonymous)
        $parentResponses = 25;
        $s2FeedbackText = [
            'AC di kelas XI-A sering mati, mohon diperbaiki.',
            'Fasilitas wudhu masjid sudah cukup bersih, pertahankan.',
            'Buku perpustakaan fisika perlu diperbanyak jumlahnya.',
            'Toilet siswa laki-laki kadang kurang air bersih.',
            'Secara umum sarana prasarana sekolah sangat baik.',
            'Laboratorium komputer mohon spesifikasi PC-nya ditingkatkan.',
        ];

        for ($i = 0; $i < $parentResponses; $i++) {
            $parent = $parentUsers[$i] ?? $parentUsers->random();
            SurveyResponse::create([
                'survey_id' => $survey2->id,
                'user_id' => $parent->id, // Store parent user id, but getRespondentAttribute will return null because survey is anonymous
                'answers' => [
                    $s2q1->id => rand(3, 5),
                    $s2q2->id => ['Pendingin Ruangan (AC) Kelas', 'Buku Perpustakaan Fisik', 'Alat Praktikum Lab IPA', 'Toilet & Fasilitas Wudhu'][rand(0, 3)],
                    $s2q3->id => rand(1, 100) <= 60 ? $s2FeedbackText[array_rand($s2FeedbackText)] : '',
                ],
                'ip_address' => '192.168.1.'.rand(10, 254),
            ]);
        }
    }
}
