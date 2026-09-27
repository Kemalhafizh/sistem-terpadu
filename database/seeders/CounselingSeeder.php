<?php

namespace Database\Seeders;

use App\Models\CounselingSession;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class CounselingSeeder extends Seeder
{
    public function run(): void
    {
        $guruBk = User::where('email', 'guru.bk@sultanagung1.sch.id')->first();
        if (! $guruBk) {
            $guruBk = User::where('role', 'guru')->first();
        }

        $students = Student::take(10)->get();

        CounselingSession::truncate();

        $counselingData = [
            [
                'student_idx' => 0,
                'status' => 'completed',
                'type' => 'individual',
                'category' => 'pribadi',
                'reason' => 'Siswa merasa kurang percaya diri dalam mengikuti kegiatan kelas tahfidz.',
                'session_notes' => 'Siswa dibimbing untuk mengenali potensi dirinya dan diberikan motivasi keutamaan hafal Al-Quran.',
                'action_plan' => 'Siswa berjanji menyetor minimal 5 ayat setiap hari dan guru tahfidz akan memantau khusus.',
                'follow_up_notes' => 'Siswa menunjukkan peningkatan kepercayaan diri pada setoran minggu berikutnya.',
            ],
            [
                'student_idx' => 1,
                'status' => 'completed',
                'type' => 'individual',
                'category' => 'pribadi',
                'reason' => 'Siswa kedapatan melanggar aturan penggunaan HP saat jam pelajaran.',
                'session_notes' => 'Siswa diberikan pengertian tentang pentingnya konsentrasi belajar dan menghormati guru.',
                'action_plan' => 'HP disita selama 3 hari, siswa menandatangani surat perjanjian tidak mengulangi.',
                'follow_up_notes' => 'Siswa kooperatif dan tidak mengulangi pelanggaran.',
            ],
            [
                'student_idx' => 2,
                'status' => 'scheduled',
                'type' => 'individual',
                'category' => 'akademik',
                'reason' => 'Siswa mengalami penurunan nilai drastis pada mata pelajaran matematika.',
                'session_notes' => '',
                'action_plan' => '',
                'follow_up_notes' => '',
            ],
            [
                'student_idx' => 3,
                'status' => 'requested',
                'type' => 'individual',
                'category' => 'karir',
                'reason' => 'Siswa bingung memilih jurusan kuliah antara teknik informatika atau kedokteran.',
                'session_notes' => '',
                'action_plan' => '',
                'follow_up_notes' => '',
            ],
            [
                'student_idx' => 4,
                'status' => 'completed',
                'type' => 'group',
                'category' => 'akademik',
                'reason' => 'Konsultasi kelompok mengenai persiapan pendaftaran SNBP / UTBK 2026.',
                'session_notes' => 'Diberikan pengarahan mengenai pembobotan indeks sekolah dan analisis nilai rapor.',
                'action_plan' => 'Masing-masing siswa mengumpulkan draf 3 pilihan universitas dan jurusan.',
                'follow_up_notes' => 'Kelompok siswa sudah menetapkan pilihan jurusan kuliah.',
            ],
            [
                'student_idx' => 5,
                'status' => 'cancelled',
                'type' => 'individual',
                'category' => 'pribadi',
                'reason' => 'Masalah perselisihan ringan antar teman sebaya.',
                'session_notes' => 'Dibatalkan karena masalah selesai dimediasi langsung oleh wali kelas.',
                'action_plan' => '',
                'follow_up_notes' => '',
            ],
            [
                'student_idx' => 6,
                'status' => 'completed',
                'type' => 'individual',
                'category' => 'pribadi',
                'reason' => 'Siswa terlambat masuk sekolah lebih dari 3 kali dalam seminggu.',
                'session_notes' => 'Membahas kendala transportasi rumah. Siswa begadang main game.',
                'action_plan' => 'Siswa membatasi jam main game maksimal pukul 21.00 dan tidur lebih awal.',
                'follow_up_notes' => 'Orang tua memantau tidur malam siswa, kehadiran pagi membaik.',
            ],
            [
                'student_idx' => 7,
                'status' => 'scheduled',
                'type' => 'individual',
                'category' => 'pribadi',
                'reason' => 'Siswa tampak murung dan sering menyendiri selama jam istirahat.',
                'session_notes' => '',
                'action_plan' => '',
                'follow_up_notes' => '',
            ],
            [
                'student_idx' => 8,
                'status' => 'completed',
                'type' => 'individual',
                'category' => 'pribadi',
                'reason' => 'Terlibat perselisihan verbal di kantin sekolah.',
                'session_notes' => 'Siswa dipertemukan untuk saling meminta maaf dan memaafkan.',
                'action_plan' => 'Menjaga kerukunan, jika diulangi poin pelanggaran ditambahkan.',
                'follow_up_notes' => 'Hubungan siswa terpantau sudah kembali kondusif.',
            ],
            [
                'student_idx' => 9,
                'status' => 'requested',
                'type' => 'individual',
                'category' => 'akademik',
                'reason' => 'Kesulitan membagi waktu antara latihan futsal OSIS dengan belajar ujian.',
                'session_notes' => '',
                'action_plan' => '',
                'follow_up_notes' => '',
            ],
        ];

        foreach ($counselingData as $idx => $data) {
            $student = $students[$data['student_idx']] ?? $students->first();

            $scheduledAt = now()->subDays(rand(1, 15))->addHours(rand(8, 14));
            $startedAt = $data['status'] === 'completed' ? (clone $scheduledAt)->addMinutes(5) : null;
            $endedAt = $data['status'] === 'completed' ? (clone $startedAt)->addMinutes(45) : null;

            CounselingSession::create([
                'student_id' => $student->id,
                'counselor_id' => $guruBk->id,
                'requested_by' => rand(0, 1) ? $student->user_id : $guruBk->id,
                'scheduled_at' => $scheduledAt,
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
                'type' => $data['type'],
                'category' => $data['category'],
                'status' => $data['status'],
                'reason' => $data['reason'],
                'session_notes' => $data['session_notes'],
                'action_plan' => $data['action_plan'],
                'follow_up_notes' => $data['follow_up_notes'],
                'follow_up_date' => $data['status'] === 'completed' ? now()->addDays(7)->format('Y-m-d') : null,
                'is_confidential' => rand(0, 1) ? true : false,
            ]);
        }
    }
}
