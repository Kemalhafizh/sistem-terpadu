<?php

namespace Database\Seeders;

use App\Models\MeetingNote;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MeetingNoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $staff = User::whereIn('role', ['admin', 'superadmin', 'kepala_sekolah', 'staf_tu', 'guru'])->get();
        if ($staff->isEmpty()) {
            return;
        }

        $creator = User::where('role', 'admin')->first() ?? $staff->first();
        $attendeeIds = $staff->pluck('id')->take(6)->toArray();

        // 1. Rapat Guru: Evaluasi Kelulusan & Kurikulum Merdeka (Finalized)
        MeetingNote::create([
            'title' => 'Rapat Evaluasi Pembelajaran & Kurikulum Merdeka',
            'type' => 'rapat_guru',
            'meeting_date' => Carbon::now()->subDays(10),
            'start_time' => '08:00:00',
            'end_time' => '10:30:00',
            'location' => 'Ruang Guru SMA ISA 1',
            'created_by' => $creator->id,
            'agenda' => "# Evaluasi KBM Bulan Lalu\n# Persiapan UTS Kurikulum Merdeka\n# Penanganan Siswa Remedial",
            'discussion' => "## Evaluasi KBM\n* Pelaksanaan KBM berjalan dengan baik, tingkat kehadiran rata-rata 94%.\n* Guru mapel diminta melengkapi input nilai harian di SISTA.\n\n## Kesiapan UTS\n* Soal UTS harus dikumpulkan paling lambat H-7.\n* Validasi soal UTS dilakukan oleh masing-masing Koordinator Mapel.",
            'decisions' => "## Keputusan Rapat\n1. UTS dilaksanakan serentak mulai tanggal 15 Juni 2026.\n2. Siswa dengan tingkat kehadiran < 85% wajib dipanggil oleh Wali Kelas bersama BK.\n3. Standard KKM untuk mapel PAI dan Tahfidz dinaikkan menjadi 75.",
            'action_items' => [
                [
                    'id' => 1,
                    'task' => 'Mengunggah draf soal UTS Matematika',
                    'pic' => 'Drs. Supriyadi',
                    'deadline' => Carbon::now()->subDays(3)->toDateString(),
                    'is_completed' => true,
                ],
                [
                    'id' => 2,
                    'task' => 'Pemanggilan orang tua siswa bermasalah absensi',
                    'pic' => 'Dra. Siti Aminah (BK)',
                    'deadline' => Carbon::now()->addDays(5)->toDateString(),
                    'is_completed' => false,
                ],
            ],
            'attendees' => $attendeeIds,
            'status' => 'finalized',
        ]);

        // 2. Rapat Yayasan YBWSA: Pengembangan Sarana Lab Komputer (Finalized)
        MeetingNote::create([
            'title' => 'Rapat Koordinasi Pengembangan Infrastruktur TIK dengan YBWSA',
            'type' => 'rapat_yayasan',
            'meeting_date' => Carbon::now()->subDays(5),
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'location' => 'Kantor Yayasan YBWSA',
            'created_by' => $creator->id,
            'agenda' => "# Pengadaan PC Baru Lab Komputer\n# Instalasi Jaringan Fiber Optik Sekolah\n# Penganggaran Dana Hibah TIK",
            'discussion' => "## Pengadaan PC Lab\n* Kondisi PC di Lab 2 sudah usang, membutuhkan peremajaan 30 unit PC baru.\n* Yayasan menyetujui pengadaan bertahap.\n\n## Jaringan Sekolah\n* Bandwidth internet dinaikkan menjadi 200 Mbps untuk menunjang ujian CBT.",
            'decisions' => "## Keputusan Rapat\n1. Pengadaan 30 PC disetujui dengan anggaran termin pertama.\n2. Vendor TIK akan mulai instalasi jaringan pada hari Sabtu mendatang.",
            'action_items' => [
                [
                    'id' => 1,
                    'task' => 'Menghubungi vendor PC dan meminta draf penawaran',
                    'pic' => 'Budi Santoso (Sarpras)',
                    'deadline' => Carbon::now()->addDays(2)->toDateString(),
                    'is_completed' => false,
                ],
            ],
            'attendees' => array_slice($attendeeIds, 0, 3),
            'status' => 'finalized',
        ]);

        // 3. Rapat OSIS: Persiapan Event Class Meeting (Draft)
        MeetingNote::create([
            'title' => 'Rapat Rencana Pelaksanaan Class Meeting Semester Genap',
            'type' => 'rapat_osis',
            'meeting_date' => Carbon::now()->addDays(2),
            'start_time' => '13:00:00',
            'end_time' => '14:30:00',
            'location' => 'Ruang OSIS SMA ISA 1',
            'created_by' => $creator->id,
            'agenda' => "# Pembentukan Panitia Pelaksana\n# Penentuan Cabang Lomba Class Meeting\n# Penyusunan Rencana Anggaran Biaya (RAB)",
            'discussion' => "## Kepanitiaan\n* Ketua panitia terpilih: Andi (XI IPA 1).\n\n## Cabang Lomba\n* Diusulkan lomba futsal antar kelas, e-sports, dan kaligrafi keislaman.",
            'decisions' => "## Keputusan Sementara\n1. Cabang lomba yang disepakati: Futsal, Mobile Legends, dan Lomba Adzan.\n2. Pengajuan anggaran maksimal Rp 2.500.000.",
            'action_items' => [
                [
                    'id' => 1,
                    'task' => 'Membuat proposal Class Meeting dan draf RAB',
                    'pic' => 'Andi Wijaya (Ketua OSIS)',
                    'deadline' => Carbon::now()->addDays(4)->toDateString(),
                    'is_completed' => false,
                ],
            ],
            'attendees' => array_slice($attendeeIds, 2, 4),
            'status' => 'draft',
        ]);
    }
}
