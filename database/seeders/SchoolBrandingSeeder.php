<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SchoolBrandingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::set('school_name', 'SMA Islam Sultan Agung 1 Semarang');
        Setting::set('foundation_name', 'Yayasan Badan Wakaf Sultan Agung (YBWSA)');
        Setting::set('school_npsn', '20328888');
        Setting::set('school_akreditasi', 'A (Unggul)');
        Setting::set('school_address', 'Jl. Mataram No. 657 Semarang, Jawa Tengah 50242');
        Setting::set('school_phone', '(024) 8314941');
        Setting::set('school_email', 'info@smaislam1-smg.sch.id');
        Setting::set('principal_name', 'Drs. H. Ahmad Dahlan, M.Pd.');
        Setting::set('principal_nip', '196807151994031005');
        Setting::set('active_academic_year', '2026/2027 Semester Ganjil');
    }
}
