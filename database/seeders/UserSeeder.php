<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Superadmin IT', 'email' => 'admin@sultanagung1.sch.id', 'role' => 'admin'],
            ['name' => 'KH. Ahmad Fauzi, S.Pd.I', 'email' => 'kepsek@sultanagung1.sch.id', 'role' => 'kepala_sekolah'],
            ['name' => 'Dra. Siti Aminah, M.Pd', 'email' => 'waka.kurikulum@sultanagung1.sch.id', 'role' => 'waka_kurikulum'],
            ['name' => 'Ust. Mahmud Hasan, S.Pd', 'email' => 'waka.kesiswaan@sultanagung1.sch.id', 'role' => 'waka_kesiswaan'],
            ['name' => 'Hj. Fatimah Az-Zahra', 'email' => 'waka.humas@sultanagung1.sch.id', 'role' => 'waka_humas'],
            ['name' => 'Bp. Rudi Hartono, S.T', 'email' => 'waka.sarpras@sultanagung1.sch.id', 'role' => 'waka_sarpras'],
            ['name' => 'Ust. Zainul Arifin, Lc', 'email' => 'guru.tahfidz@sultanagung1.sch.id', 'role' => 'guru'],
            ['name' => 'Ibu Dwi Rahayu, S.Pd', 'email' => 'guru.matematika@sultanagung1.sch.id', 'role' => 'guru'],
            ['name' => 'Bp. Agus Prasetyo, S.Pd', 'email' => 'guru.bk@sultanagung1.sch.id', 'role' => 'guru'],
            ['name' => 'Ibu Lestari, S.Pd', 'email' => 'guru.fisika@sultanagung1.sch.id', 'role' => 'guru'],
            ['name' => 'Bp. Hendra Wijaya, S.Pd', 'email' => 'guru.bahasa@sultanagung1.sch.id', 'role' => 'guru'],
            ['name' => 'Ibu Nur Aini', 'email' => 'staf.tu@sultanagung1.sch.id', 'role' => 'staf_tu'],
            // 6 Students
            ['name' => 'Muhammad Rizky Pratama', 'email' => 'siswa1@student.sa1.sch.id', 'role' => 'student'],
            ['name' => 'Aisyah Nur Fadilah', 'email' => 'siswa2@student.sa1.sch.id', 'role' => 'student'],
            ['name' => 'Ahmad Dzikri Maulana', 'email' => 'siswa3@student.sa1.sch.id', 'role' => 'student'],
            ['name' => 'Fatimah Azzahra Putri', 'email' => 'siswa4@student.sa1.sch.id', 'role' => 'student'],
            ['name' => 'Ilham Fajar Sidiq', 'email' => 'siswa5@student.sa1.sch.id', 'role' => 'student'],
            ['name' => 'Naura Syifa Ramadhani', 'email' => 'siswa6@student.sa1.sch.id', 'role' => 'student'],
            // 2 Parents
            ['name' => 'Bp. H. Pratama (Wali Murid)', 'email' => 'ortu1@parent.sa1.sch.id', 'role' => 'parent'],
            ['name' => 'Ibu Hj. Fadilah (Wali Murid)', 'email' => 'ortu2@parent.sa1.sch.id', 'role' => 'parent'],
        ];

        foreach ($users as $u) {
            User::create(array_merge($u, [
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]));
        }
    }
}
