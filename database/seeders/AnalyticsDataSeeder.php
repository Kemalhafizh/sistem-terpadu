<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Billing;
use App\Models\Grade;
use App\Models\PaymentCategory;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class AnalyticsDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // 1. Get or create past Academic Year (Semester Ganjil 2025/2026)
        $pastYear = AcademicYear::where('year', '2025/2026')->where('semester', 'Ganjil')->first();
        if (! $pastYear) {
            $pastYear = AcademicYear::create([
                'year' => '2025/2026',
                'semester' => 'Ganjil',
                'is_active' => false,
                'start_date' => '2025-07-14',
                'end_date' => '2025-12-20',
            ]);
        }

        // 2. Get students and subjects
        $students = Student::all();
        $subjects = Subject::all();
        $teachers = User::where('role', 'guru')->pluck('id');
        $recordedBy = $teachers->first() ?: 1;

        if ($students->isEmpty() || $subjects->isEmpty()) {
            return;
        }

        // 3. Create past grades for students (Semester Ganjil)
        foreach ($students as $student) {
            foreach ($subjects as $subject) {
                // Seed 2 tugas and 1 ujian per subject
                Grade::create([
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'academic_year_id' => $pastYear->id,
                    'type' => 'tugas',
                    'score' => $faker->numberBetween(70, 95),
                    'recorded_by' => $recordedBy,
                ]);

                Grade::create([
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'academic_year_id' => $pastYear->id,
                    'type' => 'pts',
                    'score' => $faker->numberBetween(68, 92),
                    'recorded_by' => $recordedBy,
                ]);

                Grade::create([
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'academic_year_id' => $pastYear->id,
                    'type' => 'pas',
                    'score' => $faker->numberBetween(70, 94),
                    'recorded_by' => $recordedBy,
                ]);
            }
        }

        // 4. Create past attendance records (Semester Ganjil - 15 days)
        $ganjilDates = [];
        $startDate = Carbon::parse($pastYear->start_date);
        for ($d = 0; $d < 15; $d++) {
            $date = $startDate->copy()->addDays($d * 7);
            if (! $date->isWeekend()) {
                $ganjilDates[] = $date->toDateString();
            }
        }

        foreach ($students as $student) {
            foreach ($ganjilDates as $dateString) {
                Attendance::create([
                    'student_id' => $student->id,
                    'academic_year_id' => $pastYear->id,
                    'classroom_id' => $student->classroom_id ?: 1,
                    'date' => $dateString,
                    'status' => $faker->randomElement(['H', 'H', 'H', 'H', 'H', 'H', 'H', 'H', 'H', 'S', 'I', 'A']), // high attendance rate ~90%
                    'recorded_by' => $recordedBy,
                ]);
            }
        }

        // 5. Create past billings (SPPP Bulanan) that are fully paid for Ganjil
        $sppCategory = PaymentCategory::where('is_monthly', true)->first();
        if (! $sppCategory) {
            $sppCategory = PaymentCategory::create([
                'name' => 'SPP Bulanan',
                'default_amount' => 500000,
                'is_monthly' => true,
            ]);
        }

        $months = ['07', '08', '09', '10', '11', '12'];
        foreach ($students as $student) {
            foreach ($months as $index => $month) {
                Billing::create([
                    'student_id' => $student->id,
                    'payment_category_id' => $sppCategory->id,
                    'academic_year_id' => $pastYear->id,
                    'month' => $month,
                    'amount' => 500000,
                    'remaining_balance' => 0,
                    'due_date' => Carbon::parse($pastYear->start_date)->addMonths($index)->day(10)->toDateString(),
                    'status' => 'paid',
                ]);
            }
        }
    }
}
