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

        DB::table('islamic_events')->insert([
            'event_name' => 'Ramadan 1447 H',
            'event_type' => 'ramadan',
            'gregorian_start_date' => now(),
            'hijri_start_date' => '1 Ramadan 1447',
            'description' => 'Bulan Suci Ramadan',
        ]);

        DB::table('spiritual_portfolios')->insert([
            'student_id' => $student->id,
            'record_date' => now(),
            'daily_prayers' => json_encode(['fajr' => true, 'dhuhr' => true, 'asr' => true, 'maghrib' => true, 'isha' => true]),
        ]);

        DB::table('dawah_contents')->insert([
            'author_id' => $employee->id,
            'title' => 'Keutamaan Shalat Dhuha',
            'content_type' => 'article',
            'content_body' => 'Shalat dhuha memiliki banyak keutamaan...',
            'status' => 'published',
            'published_at' => now(),
        ]);
    }
}
