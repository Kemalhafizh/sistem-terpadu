<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This is the designated Entry Point for Demo Environment (Modul 8.5)
     */
    public function run(): void
    {
        $this->command->info('🧹 Cleaning up database before seeding demo data...');
        Artisan::call('migrate:fresh');
        $this->command->info('✅ Database migrated.');

        $this->command->info('🌱 Planting demo seeds. This might take a minute...');

        // Reuse existing seeders for the rich demo data
        $this->call([
            UserSeeder::class,      // Roles: Admin, Kepsek, Wakil, TU, BK, Guru
            AcademicSeeder::class,  // 6 Kelas, 180 Siswa + Orang Tua, 1 Tahun Ajaran Aktif
            FinanceSeeder::class,   // SPP 6 bulan, Pembayaran parsial
            ActivitySeeder::class,  // Absensi 3 bulan, Nilai mid-semester, Poin BK
            ExtrasSeeder::class,    // Log WA, Acara kalender, dll (jika ada di ExtrasSeeder)
        ]);

        $this->command->info('🎉 DemoSeeder completed successfully! Your system is now populated with rich, realistic data.');
        $this->command->warn('⚠️  Login as Superadmin: admin@sultanagung.sch.id / password');
    }
}
