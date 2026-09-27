<?php

namespace Database\Seeders;

use App\Models\AcademicEvent;
use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\LibraryBook;
use App\Models\LibraryCategory;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExtrasSeeder extends Seeder
{
    public function run(): void
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $admin = User::where('role', 'admin')->first();

        // --- Announcements ---
        Announcement::truncate();
        Announcement::create([
            'title' => 'Jadwal Ujian Akhir Semester Genap 2026',
            'content' => "Assalamu'alaikum Wr. Wb.\n\nDiberitahukan kepada seluruh siswa/i bahwa Ujian Akhir Semester (UAS) Genap TA 2025/2026 akan dilaksanakan pada:\n\nTanggal: 2-13 Juni 2026\nWaktu: 07.30-11.00 WIB\n\nHarap mempersiapkan diri dengan baik. Jadwal per-mata pelajaran akan diumumkan melalui wali kelas masing-masing.\n\nWassalamu'alaikum Wr. Wb.",
            'priority' => 'urgent',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);
        Announcement::create([
            'title' => 'Pendaftaran Ekstrakulikuler Tahun Ajaran Baru',
            'content' => 'Pendaftaran ekskul untuk TA 2026/2027 telah dibuka! Pilihan ekskul: Pramuka, PMR, Rohis, Futsal, Basket, English Club, dan Robotika. Daftar melalui wali kelas sebelum 30 Mei 2026.',
            'priority' => 'normal',
            'target_role' => 'student',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);
        Announcement::create([
            'title' => 'Undangan Rapat Pleno Wali Murid Kelas XII',
            'content' => "Kepada Yth. Bapak/Ibu Wali Murid Kelas XII,\n\nKami mengundang Bapak/Ibu untuk menghadiri Rapat Pleno membahas persiapan wisuda dan pelepasan siswa kelas XII.\n\nHari/Tanggal: Sabtu, 24 Mei 2026\nWaktu: 09.00-12.00 WIB\nTempat: Aula Utama SMA Islam Sultan Agung 1\n\nKehadiran Bapak/Ibu sangat kami harapkan.",
            'priority' => 'normal',
            'target_role' => 'parent',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        // --- Registrations (SPMB) - 20 registrations ---
        Registration::truncate();
        $pendaftaran = [
            ['full_name' => 'Ahmad Dzikri Ramadhan', 'nisn' => '3012345601', 'previous_school' => 'SMP N 1 Semarang', 'parent_phone' => '081234567890', 'status' => 'pending'],
            ['full_name' => 'Aisyah Putri Nabila', 'nisn' => '3012345602', 'previous_school' => 'SMP Islam Hidayatullah', 'parent_phone' => '081234567891', 'status' => 'reviewing'],
            ['full_name' => 'Fajar Sidiq Prabowo', 'nisn' => '3012345603', 'previous_school' => 'MTs Al-Irsyad Semarang', 'parent_phone' => '081234567892', 'status' => 'accepted'],
            ['full_name' => 'Rizky Aditya Pratama', 'nisn' => '3012345604', 'previous_school' => 'SMP N 5 Semarang', 'parent_phone' => '081234567893', 'status' => 'rejected'],
            ['full_name' => 'Khadijah Nur Safitri', 'nisn' => '3012345605', 'previous_school' => 'SMP Muhammadiyah 3', 'parent_phone' => '081234567894', 'status' => 'pending'],
            ['full_name' => 'Muhammad Haikal', 'nisn' => '3012345606', 'previous_school' => 'MTs N 1 Semarang', 'parent_phone' => '081234567895', 'status' => 'reviewing'],
            ['full_name' => 'Naura Syifa Azzahra', 'nisn' => '3012345607', 'previous_school' => 'SMP Sultan Agung 3', 'parent_phone' => '081234567896', 'status' => 'accepted'],
            ['full_name' => 'Daffa Raditya Putra', 'nisn' => '3012345608', 'previous_school' => 'SMP N 13 Semarang', 'parent_phone' => '081234567897', 'status' => 'pending'],
            ['full_name' => 'Luthfi Hakim', 'nisn' => '3012345609', 'previous_school' => 'SMP N 2 Semarang', 'parent_phone' => '081234567898', 'status' => 'pending'],
            ['full_name' => 'Farah Nabila', 'nisn' => '3012345610', 'previous_school' => 'MTs N 2 Semarang', 'parent_phone' => '081234567899', 'status' => 'accepted'],
            ['full_name' => 'Zulfikar Ali', 'nisn' => '3012345611', 'previous_school' => 'SMP IT PAPB Semarang', 'parent_phone' => '081234567810', 'status' => 'reviewing'],
            ['full_name' => 'Safira Azzahra', 'nisn' => '3012345612', 'previous_school' => 'SMP N 15 Semarang', 'parent_phone' => '081234567811', 'status' => 'accepted'],
            ['full_name' => 'Rangga Wijaya', 'nisn' => '3012345613', 'previous_school' => 'SMP Hasanudin Semarang', 'parent_phone' => '081234567812', 'status' => 'rejected'],
            ['full_name' => 'Anisa Rahmawati', 'nisn' => '3012345614', 'previous_school' => 'SMP Islam Sultan Agung 4', 'parent_phone' => '081234567813', 'status' => 'pending'],
            ['full_name' => 'Gading Marten', 'nisn' => '3012345615', 'previous_school' => 'SMP N 3 Semarang', 'parent_phone' => '081234567814', 'status' => 'reviewing'],
            ['full_name' => 'Gisella Anastasia', 'nisn' => '3012345616', 'previous_school' => 'SMP Karangturi Semarang', 'parent_phone' => '081234567815', 'status' => 'accepted'],
            ['full_name' => 'Ferry Irawan', 'nisn' => '3012345617', 'previous_school' => 'SMP N 12 Semarang', 'parent_phone' => '081234567816', 'status' => 'pending'],
            ['full_name' => 'Venna Melinda', 'nisn' => '3012345618', 'previous_school' => 'SMP N 9 Semarang', 'parent_phone' => '081234567817', 'status' => 'accepted'],
            ['full_name' => 'Raffi Ahmad Malik', 'nisn' => '3012345619', 'previous_school' => 'SMP Pasundan Bandung', 'parent_phone' => '081234567818', 'status' => 'pending'],
            ['full_name' => 'Nagita Slavina', 'nisn' => '3012345620', 'previous_school' => 'SMP Al-Azhar Jakarta', 'parent_phone' => '081234567819', 'status' => 'accepted'],
        ];
        foreach ($pendaftaran as $idx => $p) {
            Registration::create(array_merge($p, [
                'registration_number' => 'SPMB-2026-'.str_pad($idx + 1, 4, '0', STR_PAD_LEFT),
                'academic_year_id' => $activeYear->id,
                'created_at' => now()->subDays(rand(1, 14)),
            ]));
        }

        // --- Employees (all teachers and staff_tu users) ---
        Employee::truncate();
        $staffAndTeachers = User::whereIn('role', ['guru', 'staf_tu'])->get();
        foreach ($staffAndTeachers as $idx => $user) {
            $position = match ($user->email) {
                'guru.tahfidz@sultanagung1.sch.id' => 'Guru Tahfidz & PAI',
                'guru.matematika@sultanagung1.sch.id' => 'Guru Matematika',
                'guru.bk@sultanagung1.sch.id' => 'Guru BK / Konselor',
                'guru.fisika@sultanagung1.sch.id' => 'Guru Fisika',
                'guru.bahasa@sultanagung1.sch.id' => 'Guru Bahasa Inggris & Bahasa Indonesia',
                'guru.pai@sultanagung1.sch.id' => 'Guru Pendidikan Agama Islam',
                'guru.kimia@sultanagung1.sch.id' => 'Guru Kimia',
                'guru.biologi@sultanagung1.sch.id' => 'Guru Biologi',
                'guru.sejarah@sultanagung1.sch.id' => 'Guru Sejarah',
                'guru.ekonomi@sultanagung1.sch.id' => 'Guru Ekonomi',
                'guru.informatika@sultanagung1.sch.id' => 'Guru Informatika',
                'guru.akuntansi@sultanagung1.sch.id' => 'Guru Akuntansi',
                'guru.penjas@sultanagung1.sch.id' => 'Guru Penjasorkes',
                'guru.seni@sultanagung1.sch.id' => 'Guru Seni Budaya',
                'guru.pkn@sultanagung1.sch.id' => 'Guru Pendidikan Kewarganegaraan',
                'guru.geografi@sultanagung1.sch.id' => 'Guru Geografi',
                'guru.sosiologi@sultanagung1.sch.id' => 'Guru Sosiologi',
                'guru.jawa@sultanagung1.sch.id' => 'Guru Bahasa Jawa',
                'staf.tu@sultanagung1.sch.id' => 'Kepala Tata Usaha',
                'pustakawan@sultanagung1.sch.id' => 'Staf Perpustakaan',
                'uks@sultanagung1.sch.id' => 'Staf UKS / Perawat',
                default => 'Staff Kependidikan',
            };

            $isGTT = in_array($user->email, ['guru.bk@sultanagung1.sch.id', 'guru.bahasa@sultanagung1.sch.id', 'guru.jawa@sultanagung1.sch.id']);
            $salary = $user->role === 'staf_tu' ? 3200000 : ($isGTT ? 2800000 : 4500000);

            Employee::create([
                'user_id' => $user->id,
                'employee_number' => 'NIY-'.str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                'position' => $position,
                'employment_status' => $user->role === 'staf_tu' ? 'staf_tetap' : ($isGTT ? 'gtt' : 'gty'),
                'hire_date' => now()->subYears(rand(1, 8))->format('Y-m-d'),
                'base_salary' => $salary,
                'hourly_rate' => $isGTT ? 75000 : 0,
                'bank_name' => ['BRI', 'BNI', 'BSI', 'Mandiri'][rand(0, 3)],
                'bank_account' => '00'.rand(10000000, 99999999),
            ]);
        }

        // --- Library Categories & Books ---
        LibraryCategory::truncate();
        LibraryBook::truncate();
        $cat1 = LibraryCategory::create(['name' => 'Buku Pelajaran']);
        $cat2 = LibraryCategory::create(['name' => 'Kitab Islam']);
        $cat3 = LibraryCategory::create(['name' => 'Novel / Fiksi']);
        $cat4 = LibraryCategory::create(['name' => 'Referensi Umum']);

        $books = [
            ['title' => 'Matematika Wajib Kelas X', 'author' => 'Kemendikbud', 'category_id' => $cat1->id, 'type' => 'physical'],
            ['title' => 'Fisika untuk SMA/MA', 'author' => 'Marthen Kanginan', 'category_id' => $cat1->id, 'type' => 'physical'],
            ['title' => 'Kimia Kelas XI', 'author' => 'Unggul Sudarmo', 'category_id' => $cat1->id, 'type' => 'physical'],
            ['title' => 'Biologi SMA Kelas XII', 'author' => 'Irnaningtyas', 'category_id' => $cat1->id, 'type' => 'physical'],
            ['title' => 'Bahasa Inggris: Life Today Kelas X', 'author' => 'Kemendikbud', 'category_id' => $cat1->id, 'type' => 'physical'],
            ['title' => 'Tafsir Ibnu Katsir Jilid 1', 'author' => 'Ibnu Katsir', 'category_id' => $cat2->id, 'type' => 'digital'],
            ['title' => 'Riyadhus Shalihin', 'author' => 'Imam An-Nawawi', 'category_id' => $cat2->id, 'type' => 'digital'],
            ['title' => 'Hadits Arbain Nawawiyah', 'author' => 'Imam An-Nawawi', 'category_id' => $cat2->id, 'type' => 'digital'],
            ['title' => 'Tafsir Al-Azhar Jilid 1', 'author' => 'Buya Hamka', 'category_id' => $cat2->id, 'type' => 'physical'],
            ['title' => 'Ringkasan Shahih Bukhari', 'author' => 'Imam Bukhari', 'category_id' => $cat2->id, 'type' => 'digital'],
            ['title' => 'Laskar Pelangi', 'author' => 'Andrea Hirata', 'category_id' => $cat3->id, 'type' => 'physical'],
            ['title' => 'Bumi Manusia', 'author' => 'Pramoedya Ananta Toer', 'category_id' => $cat3->id, 'type' => 'physical'],
            ['title' => 'Negeri 5 Menara', 'author' => 'A. Fuadi', 'category_id' => $cat3->id, 'type' => 'physical'],
            ['title' => 'Hafalan Shalat Delisa', 'author' => 'Tere Liye', 'category_id' => $cat3->id, 'type' => 'physical'],
            ['title' => 'Ronggeng Dukuh Paruk', 'author' => 'Ahmad Tohari', 'category_id' => $cat3->id, 'type' => 'physical'],
            ['title' => 'Kamus Besar Bahasa Indonesia', 'author' => 'KBBI', 'category_id' => $cat4->id, 'type' => 'digital'],
            ['title' => 'Oxford Advanced Learner Dictionary', 'author' => 'Oxford Press', 'category_id' => $cat4->id, 'type' => 'digital'],
            ['title' => 'Ensiklopedia Islam', 'author' => 'Dewan Redaksi', 'category_id' => $cat4->id, 'type' => 'physical'],
            ['title' => 'Sejarah Kebudayaan Islam', 'author' => 'Badri Yatim', 'category_id' => $cat4->id, 'type' => 'digital'],
            ['title' => 'Sistem Informasi Sekolah Modern', 'author' => 'Kemal Hafizh', 'category_id' => $cat4->id, 'type' => 'digital'],
        ];
        foreach ($books as $b) {
            LibraryBook::create(array_merge($b, [
                'uuid' => (string) Str::uuid(),
                'created_by' => $admin->id,
                'download_count' => rand(0, 50),
                'description' => 'Buku referensi untuk koleksi perpustakaan digital SMA Islam Sultan Agung 1 Semarang.',
            ]));
        }

        // --- Academic Calendar Events ---
        AcademicEvent::truncate();
        $classroomXIIA = Classroom::where('name', 'XII-A')->first();
        $classroomXIIB = Classroom::where('name', 'XII-B')->first();

        $events = [
            [
                'title' => 'Upacara Hari Kebangkitan Nasional',
                'description' => 'Upacara bendera memperingati Hari Kebangkitan Nasional, diikuti seluruh guru, staf, dan siswa.',
                'event_type' => 'ceremony',
                'start_date' => '2026-05-20 07:00:00',
                'end_date' => '2026-05-20 08:30:00',
                'is_all_day' => false,
                'color' => '#6366f1',
                'target_role' => null,
                'target_classroom_id' => null,
                'send_notification' => false,
            ],
            [
                'title' => 'Rapat Pleno Persiapan Kelulusan XII',
                'description' => 'Rapat dewan guru dan staf TU membahas kelulusan siswa kelas XII dan teknis wisuda.',
                'event_type' => 'meeting',
                'start_date' => '2026-05-25 13:00:00',
                'end_date' => '2026-05-25 15:30:00',
                'is_all_day' => false,
                'color' => '#f59e0b',
                'target_role' => 'guru',
                'target_classroom_id' => null,
                'send_notification' => false,
            ],
            [
                'title' => 'Hari Libur Idul Adha 1447 H',
                'description' => 'Libur nasional memperingati Hari Raya Idul Adha 1447 H. Kegiatan belajar mengajar diliburkan.',
                'event_type' => 'holiday',
                'start_date' => '2026-05-27 00:00:00',
                'end_date' => '2026-05-29 00:00:00',
                'is_all_day' => true,
                'color' => '#10b981',
                'target_role' => null,
                'target_classroom_id' => null,
                'send_notification' => false,
            ],
            [
                'title' => 'Tryout Akbar UTBK Mandiri XII-A',
                'description' => 'Tryout mandiri berbasis komputer persiapan UTBK untuk siswa kelas XII-A.',
                'event_type' => 'tryout',
                'start_date' => '2026-05-29 08:00:00',
                'end_date' => '2026-05-29 12:00:00',
                'is_all_day' => false,
                'color' => '#8b5cf6',
                'target_role' => 'student',
                'target_classroom_id' => $classroomXIIA ? $classroomXIIA->id : null,
                'send_notification' => false,
            ],
            [
                'title' => 'Tryout Akbar UTBK Mandiri XII-B',
                'description' => 'Tryout mandiri berbasis komputer persiapan UTBK untuk siswa kelas XII-B.',
                'event_type' => 'tryout',
                'start_date' => '2026-05-29 08:00:00',
                'end_date' => '2026-05-29 12:00:00',
                'is_all_day' => false,
                'color' => '#8b5cf6',
                'target_role' => 'student',
                'target_classroom_id' => $classroomXIIB ? $classroomXIIB->id : null,
                'send_notification' => false,
            ],
            [
                'title' => 'Bazaar OSIS & Pentas Seni Islami',
                'description' => 'Acara kreativitas siswa, bazaar kewirausahaan, serta panggung pentas seni religi yang diselenggarakan OSIS.',
                'event_type' => 'osis',
                'start_date' => '2026-05-30 08:00:00',
                'end_date' => '2026-05-30 16:00:00',
                'is_all_day' => false,
                'color' => '#ec4899',
                'target_role' => null,
                'target_classroom_id' => null,
                'send_notification' => false,
            ],
            [
                'title' => 'Pengajian Bulanan & Doa Bersama',
                'description' => 'Kajian keagamaan bulanan menyambut UAS, sekaligus doa bersama untuk kesuksesan akademik siswa.',
                'event_type' => 'other',
                'start_date' => '2026-06-01 07:30:00',
                'end_date' => '2026-06-01 09:30:00',
                'is_all_day' => false,
                'color' => '#3b82f6',
                'target_role' => null,
                'target_classroom_id' => null,
                'send_notification' => false,
            ],
            [
                'title' => 'Ujian Akhir Semester Genap',
                'description' => 'Pelaksanaan Penilaian Akhir Semester (PAS) Genap Tahun Ajaran 2025/2026 untuk seluruh jenjang kelas.',
                'event_type' => 'exam',
                'start_date' => '2026-06-02 00:00:00',
                'end_date' => '2026-06-14 00:00:00',
                'is_all_day' => true,
                'color' => '#ef4444',
                'target_role' => null,
                'target_classroom_id' => null,
                'send_notification' => true,
            ],
        ];

        foreach ($events as $eventData) {
            AcademicEvent::create(array_merge($eventData, [
                'academic_year_id' => $activeYear->id,
                'created_by' => $admin->id,
                'is_active' => true,
            ]));
        }
    }
}
