<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\InternshipCompany;
use App\Models\UniversityCatalog;
use App\Models\StudentShowcase;
use App\Models\StudentWallet;
use App\Models\MiniBankTransaction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CareerReadinessSeeder extends Seeder
{
    public function run(): void
    {
        $student = User::firstOrCreate(
            ['email' => 'student.career@sula1.sch.id'],
            [
                'name' => 'Siswa Karir Sula',
                'password' => Hash::make('password'),
                'role' => 'student'
            ]
        );

        $company = InternshipCompany::create([
            'company_name' => 'PT Teknologi Sultan Agung',
            'industry_sector' => 'Information Technology',
            'address' => 'Jl. Kaligawe Raya, Semarang',
            'pic_name' => 'Bapak Budi',
            'pic_contact' => '081234567890',
            'is_verified_partner' => true
        ]);

        $company->applications()->create([
            'student_id' => $student->id,
            'start_date' => now()->addDays(10),
            'end_date' => now()->addMonths(3),
            'status' => 'pending'
        ]);

        $uni = UniversityCatalog::create([
            'university_name' => 'Universitas Islam Sultan Agung (UNISSULA)',
            'major' => 'Kedokteran',
            'passing_grade_index' => 90.50,
            'city' => 'Semarang',
            'accreditation' => 'Unggul'
        ]);

        $uni->applications()->create([
            'student_id' => $student->id,
            'admission_type' => 'snbp',
            'status' => 'preparing'
        ]);

        StudentShowcase::create([
            'student_id' => $student->id,
            'project_title' => 'Sistem Pertanian Hidroponik Cerdas',
            'description' => 'Proyek IoT untuk memonitor tanaman hidroponik.',
            'is_public' => true,
            // view_count starts at 0 and only real views raise it (it used to be seeded as 150).
        ]);

        $wallet = StudentWallet::create([
            'student_id' => $student->id,
            'balance' => 500000,
            'savings_target' => 1000000,
            'is_active' => true
        ]);

        MiniBankTransaction::create([
            'wallet_id' => $wallet->id,
            'transaction_type' => 'deposit',
            'amount' => 500000,
            'reference_code' => 'DEP' . Str::random(6),
            'description' => 'Setoran awal tabungan sekolah'
        ]);
    }
}
