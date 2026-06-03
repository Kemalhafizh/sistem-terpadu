<?php

namespace Database\Seeders;

use App\Models\AcademicEvent;
use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\LibraryBook;
use App\Models\LibraryCategory;
use App\Models\MutabaahItem;
use App\Models\MutabaahLog;
use App\Models\Registration;
use App\Models\Student;
use App\Models\TahfidzLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExtrasSeeder extends Seeder
{
    public function run(): void
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $admin = User::where('role', 'admin')->first();
        $guruTahfidz = User::where('email', 'guru.tahfidz@sultanagung1.sch.id')->first();
        $students = Student::all();

        // --- Tahfidz Logs ---
        $surahs = [
            [78, 'An-Naba', 40], [79, 'An-Naziat', 46], [80, 'Abasa', 42],
            [81, 'At-Takwir', 29], [82, 'Al-Infitar', 19], [83, 'Al-Mutaffifin', 36],
        ];
        foreach ($students->take(10) as $idx => $student) {
            $surah = $surahs[$idx % count($surahs)];
            TahfidzLog::create([
                'student_id' => $student->id,
                'teacher_id' => $guruTahfidz->id,
                'surah_number' => $surah[0],
                'surah_name' => $surah[1],
                'from_ayat' => 1,
                'to_ayat' => min(rand(5, 20), $surah[2]),
                'type' => rand(0, 1) ? 'ziyadah' : 'murajaah',
                'grade' => ['A', 'B', 'B', 'C'][rand(0, 3)],
                'date' => now()->subDays(rand(0, 7))->format('Y-m-d'),
            ]);
        }

        // --- Mutabaah Items ---
        $items = [
            ['name' => 'Shalat Subuh Berjamaah', 'category' => 'wajib'],
            ['name' => 'Shalat Dhuha', 'category' => 'sunnah'],
            ['name' => 'Tilawah 1 Halaman', 'category' => 'wajib'],
            ['name' => 'Puasa Sunnah', 'category' => 'sunnah'],
            ['name' => 'Sedekah Harian', 'category' => 'akhlak'],
            ['name' => 'Dzikir Pagi & Petang', 'category' => 'sunnah'],
        ];
        foreach ($items as $item) {
            MutabaahItem::create($item);
        }

        // --- Mutabaah Logs (7 days × 8 students × 3 random items) ---
        $mutabaahItems = MutabaahItem::all();
        foreach ($students->take(8) as $student) {
            for ($d = 0; $d < 7; $d++) {
                foreach ($mutabaahItems->random(3) as $item) {
                    MutabaahLog::create([
                        'student_id' => $student->id,
                        'mutabaah_item_id' => $item->id,
                        'is_completed' => rand(0, 1),
                        'date' => now()->subDays($d)->format('Y-m-d'),
                    ]);
                }
            }
        }

        // --- Announcements ---
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

        // --- Registrations (SPMB) ---
        $pendaftar = [
            ['full_name' => 'Ahmad Dzikri Ramadhan', 'nisn' => '3012345601', 'previous_school' => 'SMP N 1 Semarang', 'parent_phone' => '081234567890', 'status' => 'pending'],
            ['full_name' => 'Aisyah Putri Nabila', 'nisn' => '3012345602', 'previous_school' => 'SMP Islam Hidayatullah', 'parent_phone' => '081234567891', 'status' => 'reviewing'],
            ['full_name' => 'Fajar Sidiq Prabowo', 'nisn' => '3012345603', 'previous_school' => 'MTs Al-Irsyad Semarang', 'parent_phone' => '081234567892', 'status' => 'accepted'],
            ['full_name' => 'Rizky Aditya Pratama', 'nisn' => '3012345604', 'previous_school' => 'SMP N 5 Semarang', 'parent_phone' => '081234567893', 'status' => 'rejected'],
            ['full_name' => 'Khadijah Nur Safitri', 'nisn' => '3012345605', 'previous_school' => 'SMP Muhammadiyah 3', 'parent_phone' => '081234567894', 'status' => 'pending'],
            ['full_name' => 'Muhammad Haikal', 'nisn' => '3012345606', 'previous_school' => 'MTs N 1 Semarang', 'parent_phone' => '081234567895', 'status' => 'reviewing'],
            ['full_name' => 'Naura Syifa Azzahra', 'nisn' => '3012345607', 'previous_school' => 'SMP Sultan Agung 3', 'parent_phone' => '081234567896', 'status' => 'accepted'],
            ['full_name' => 'Daffa Raditya Putra', 'nisn' => '3012345608', 'previous_school' => 'SMP N 13 Semarang', 'parent_phone' => '081234567897', 'status' => 'pending'],
        ];
        foreach ($pendaftar as $idx => $p) {
            Registration::create(array_merge($p, [
                'registration_number' => 'SPMB-2026-'.str_pad($idx + 1, 4, '0', STR_PAD_LEFT),
                'academic_year_id' => $activeYear->id,
                'created_at' => now()->subDays(rand(1, 14)),
            ]));
        }

        // --- Employees ---
        $employeeData = [
            ['email' => 'guru.tahfidz@sultanagung1.sch.id', 'number' => 'NIY-001', 'position' => 'Guru Tahfidz & PAI', 'status' => 'gty', 'salary' => 4500000],
            ['email' => 'guru.matematika@sultanagung1.sch.id', 'number' => 'NIY-002', 'position' => 'Guru Matematika', 'status' => 'gty', 'salary' => 4500000],
            ['email' => 'guru.bk@sultanagung1.sch.id', 'number' => 'NIY-003', 'position' => 'Guru BK / Konselor', 'status' => 'gtt', 'salary' => 2800000],
            ['email' => 'guru.fisika@sultanagung1.sch.id', 'number' => 'NIY-004', 'position' => 'Guru Fisika & Kimia', 'status' => 'gty', 'salary' => 4500000],
            ['email' => 'guru.bahasa@sultanagung1.sch.id', 'number' => 'NIY-005', 'position' => 'Guru B.Inggris & B.Indo', 'status' => 'gtt', 'salary' => 2800000],
            ['email' => 'staf.tu@sultanagung1.sch.id', 'number' => 'NIY-006', 'position' => 'Staf Tata Usaha', 'status' => 'staf_tetap', 'salary' => 3200000],
        ];
        foreach ($employeeData as $e) {
            $user = User::where('email', $e['email'])->first();
            Employee::create([
                'user_id' => $user->id,
                'employee_number' => $e['number'],
                'position' => $e['position'],
                'employment_status' => $e['status'],
                'hire_date' => now()->subYears(rand(1, 8))->format('Y-m-d'),
                'base_salary' => $e['salary'],
                'hourly_rate' => $e['status'] === 'gtt' ? 75000 : 0,
                'bank_name' => ['BRI', 'BNI', 'BSI', 'Mandiri'][rand(0, 3)],
                'bank_account' => '00'.rand(10000000, 99999999),
            ]);
        }

        // --- Library ---
        $cat1 = LibraryCategory::create(['name' => 'Buku Pelajaran']);
        $cat2 = LibraryCategory::create(['name' => 'Kitab Islam']);
        $cat3 = LibraryCategory::create(['name' => 'Novel / Fiksi']);
        $cat4 = LibraryCategory::create(['name' => 'Referensi Umum']);

        $books = [
            ['title' => 'Matematika Wajib Kelas X', 'author' => 'Kemendikbud', 'category_id' => $cat1->id, 'type' => 'physical'],
            ['title' => 'Fisika untuk SMA/MA', 'author' => 'Marthen Kanginan', 'category_id' => $cat1->id, 'type' => 'physical'],
            ['title' => 'Tafsir Ibnu Katsir Jilid 1', 'author' => 'Ibnu Katsir', 'category_id' => $cat2->id, 'type' => 'digital'],
            ['title' => 'Riyadhus Shalihin', 'author' => 'Imam An-Nawawi', 'category_id' => $cat2->id, 'type' => 'digital'],
            ['title' => 'Laskar Pelangi', 'author' => 'Andrea Hirata', 'category_id' => $cat3->id, 'type' => 'physical'],
            ['title' => 'Bumi Manusia', 'author' => 'Pramoedya Ananta Toer', 'category_id' => $cat3->id, 'type' => 'physical'],
            ['title' => 'Kamus Besar Bahasa Indonesia', 'author' => 'KBBI', 'category_id' => $cat4->id, 'type' => 'digital'],
            ['title' => 'Oxford Advanced Learner Dictionary', 'author' => 'Oxford Press', 'category_id' => $cat4->id, 'type' => 'digital'],
        ];
        foreach ($books as $b) {
            LibraryBook::create(array_merge($b, [
                'uuid' => (string) Str::uuid(),
                'created_by' => $admin->id,
                'download_count' => rand(0, 50),
                'description' => 'Buku referensi untuk koleksi perpustakaan digital SMA Islam Sultan Agung 1.',
            ]));
        }

        // --- Academic Calendar Events ---
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
                'end_date' => '2026-05-29 00:00:00', // Exclusive end date for all day event
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
                'end_date' => '2026-06-14 00:00:00', // Exclusive end date for all day event
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
