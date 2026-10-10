<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IslamicTechSeeder extends Seeder
{
    public function run(): void
    {
        $student = Student::first() ?? Student::create(['name' => 'Siswa Test']);
        $employee = Employee::first() ?? Employee::create(['name' => 'Guru Test']);

        DB::table('tahsin_records')->insert([
            'student_id' => $student->id,
            'teacher_id' => $employee->id,
            'surah_name' => 'Al-Fatihah',
            'start_ayah' => 1,
            'end_ayah' => 7,
            'fluency_score' => 85,
            'makhraj_score' => 80,
            'assessment_date' => now(),
        ]);
    }
}
