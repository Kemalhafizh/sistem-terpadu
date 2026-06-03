<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Billing;
use App\Models\Classroom;
use App\Models\PaymentCategory;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSultanAgungSeeder extends Seeder
{
    public function run()
    {
        // 1. Tahun Ajaran
        $ay = AcademicYear::create([
            'year' => '2025/2026',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        // 2. Kelas
        $class = Classroom::create([
            'name' => 'X-A',
            'grade_level' => 'X',
            'major' => 'MIPA',
            'academic_year' => '2025/2026',
        ]);

        // 3. User & Student (Anak Pertama)
        $u1 = User::create([
            'name' => 'Ahmad Fauzi',
            'email' => 'ahmad@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'student',
        ]);
        $s1 = Student::create([
            'user_id' => $u1->id,
            'classroom_id' => $class->id,
            'nis' => '2025001',
            'nisn' => '0012345678',
            'gender' => 'L',
        ]);

        // 4. User & Student (Anak Kedua)
        $u2 = User::create([
            'name' => 'Siti Aminah',
            'email' => 'siti@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'student',
        ]);
        $s2 = Student::create([
            'user_id' => $u2->id,
            'classroom_id' => $class->id,
            'nis' => '2025002',
            'nisn' => '0012345679',
            'gender' => 'P',
        ]);

        // 5. Parent (Wali Murid)
        $parent = User::create([
            'name' => 'Bapak Budi',
            'email' => 'wali@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'parent',
        ]);

        // Link students to parent
        $s1->update(['parent_id' => $parent->id]);
        $s2->update(['parent_id' => $parent->id]);

        // 6. Finance Category
        $cat = PaymentCategory::create(['name' => 'SPP Terpadu', 'default_amount' => 500000, 'is_monthly' => true]);

        // 7. Dummy Billings
        Billing::create([
            'student_id' => $s1->id,
            'payment_category_id' => $cat->id,
            'academic_year_id' => $ay->id,
            'month' => 'Juli',
            'amount' => 500000,
            'status' => 'unpaid',
        ]);

        Billing::create([
            'student_id' => $s2->id,
            'payment_category_id' => $cat->id,
            'academic_year_id' => $ay->id,
            'month' => 'Juli',
            'amount' => 500000,
            'status' => 'unpaid',
        ]);
    }
}
