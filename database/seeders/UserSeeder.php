<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // Staff & Admins (9 users)
            ['name' => 'Superadmin IT', 'email' => 'admin@sultanagung1.sch.id', 'role' => 'admin'],
            ['name' => 'KH. Ahmad Fauzi, S.Pd.I', 'email' => 'kepsek@sultanagung1.sch.id', 'role' => 'kepala_sekolah'],
            ['name' => 'Dra. Siti Aminah, M.Pd', 'email' => 'waka.kurikulum@sultanagung1.sch.id', 'role' => 'waka_kurikulum'],
            ['name' => 'Ust. Mahmud Hasan, S.Pd', 'email' => 'waka.kesiswaan@sultanagung1.sch.id', 'role' => 'waka_kesiswaan'],
            ['name' => 'Hj. Fatimah Az-Zahra', 'email' => 'waka.humas@sultanagung1.sch.id', 'role' => 'waka_humas'],
            ['name' => 'Bp. Rudi Hartono, S.T', 'email' => 'waka.sarpras@sultanagung1.sch.id', 'role' => 'waka_sarpras'],
            ['name' => 'Ibu Nur Aini', 'email' => 'staf.tu@sultanagung1.sch.id', 'role' => 'staf_tu'],
            ['name' => 'Ibu Rahmawati, A.Md', 'email' => 'pustakawan@sultanagung1.sch.id', 'role' => 'staf_tu'],
            ['name' => 'Bp. Slamet', 'email' => 'uks@sultanagung1.sch.id', 'role' => 'staf_tu'],

            // Teachers (18 users)
            ['name' => 'Ust. Zainul Arifin, Lc', 'email' => 'guru.tahfidz@sultanagung1.sch.id', 'role' => 'guru'],
            ['name' => 'Ibu Dwi Rahayu, S.Pd', 'email' => 'guru.matematika@sultanagung1.sch.id', 'role' => 'guru'],
            ['name' => 'Bp. Agus Prasetyo, S.Pd', 'email' => 'guru.bk@sultanagung1.sch.id', 'role' => 'guru'],
            ['name' => 'Ibu Lestari, S.Pd', 'email' => 'guru.fisika@sultanagung1.sch.id', 'role' => 'guru'],
            ['name' => 'Bp. Hendra Wijaya, S.Pd', 'email' => 'guru.bahasa@sultanagung1.sch.id', 'role' => 'guru'],
            ['name' => 'Bp. Ahmad Dahlan, S.Ag', 'email' => 'guru.pai@sultanagung1.sch.id', 'role' => 'guru'],
            ['name' => 'Ibu Kartini, S.Pd', 'email' => 'guru.kimia@sultanagung1.sch.id', 'role' => 'guru'],
            ['name' => 'Ibu Megawati, S.Pd', 'email' => 'guru.biologi@sultanagung1.sch.id', 'role' => 'guru'],
            ['name' => 'Bp. Soekarno, S.Pd', 'email' => 'guru.sejarah@sultanagung1.sch.id', 'role' => 'guru'],
            ['name' => 'Bp. Hatta, S.E', 'email' => 'guru.ekonomi@sultanagung1.sch.id', 'role' => 'guru'],
            ['name' => 'Bp. Habibie, S.Kom', 'email' => 'guru.informatika@sultanagung1.sch.id', 'role' => 'guru'],
            ['name' => 'Ibu Sri Mulyani, M.B.A', 'email' => 'guru.akuntansi@sultanagung1.sch.id', 'role' => 'guru'],
            ['name' => 'Bp. Sudirman, S.Pd', 'email' => 'guru.penjas@sultanagung1.sch.id', 'role' => 'guru'],
            ['name' => 'Ibu Cut Nyak Dhien, S.Pd', 'email' => 'guru.seni@sultanagung1.sch.id', 'role' => 'guru'],
            ['name' => 'Bp. Ki Hajar Dewantara, S.Pd', 'email' => 'guru.pkn@sultanagung1.sch.id', 'role' => 'guru'],
            ['name' => 'Ibu Raden Ajeng, S.Pd', 'email' => 'guru.geografi@sultanagung1.sch.id', 'role' => 'guru'],
            ['name' => 'Bp. Pattimura, S.Pd', 'email' => 'guru.sosiologi@sultanagung1.sch.id', 'role' => 'guru'],
            ['name' => 'Bp. Diponegoro, S.Pd', 'email' => 'guru.jawa@sultanagung1.sch.id', 'role' => 'guru'],
        ];

        // Students (25 users)
        $studentNames = [
            'Muhammad Rizky Pratama', 'Aisyah Nur Fadilah', 'Ahmad Dzikri Maulana', 'Fatimah Azzahra Putri',
            'Ilham Fajar Sidiq', 'Naura Syifa Ramadhani', 'Bagus Adi Nugroho', 'Citra Kirana',
            'Dimas Seto', 'Eka Putri', 'Farhan Halim', 'Gita Gutawa',
            'Hafizh Syahputra', 'Indah Permatasari', 'Joko Widodo', 'Kurnia Meiga',
            'Lesti Kejora', 'Maudy Ayunda', 'Nadiem Makarim', 'Olga Syahputra',
            'Pevita Pearce', 'Raffi Ahmad', 'Sule Sutisna', 'Tulus Prasetio', 'Unique Priscilla'
        ];
        foreach ($studentNames as $idx => $name) {
            $users[] = [
                'name' => $name,
                'email' => 'siswa' . ($idx + 1) . '@student.sa1.sch.id',
                'role' => 'student'
            ];
        }

        // Parents (25 users)
        $parentNames = [
            'Bp. H. Pratama', 'Ibu Hj. Fadilah', 'Bp. Joko Maulana', 'Ibu Aminah Azzahra',
            'Bp. Bambang Sidiq', 'Ibu Lestari Ramadhani', 'Bp. Budi Nugroho', 'Ibu Kirana',
            'Bp. Seto', 'Ibu Eka', 'Bp. Halim', 'Ibu Gutawa',
            'Bp. Syahputra', 'Ibu Permatasari', 'Bp. Widodo', 'Ibu Meiga',
            'Bp. Kejora', 'Ibu Ayunda', 'Bp. Makarim', 'Ibu Syahputra',
            'Bp. Pearce', 'Ibu Ahmad', 'Bp. Sutisna', 'Ibu Prasetio', 'Bp. Priscilla'
        ];
        foreach ($parentNames as $idx => $name) {
            $users[] = [
                'name' => $name,
                'email' => 'ortu' . ($idx + 1) . '@parent.sa1.sch.id',
                'role' => 'parent'
            ];
        }

        foreach ($users as $u) {
            User::create(array_merge($u, [
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]));
        }
    }
}
