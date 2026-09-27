<?php

namespace Database\Seeders;

use App\Models\CarbonMetric;
use App\Models\EcoParticipation;
use App\Models\EcoProject;
use App\Models\Employee;
use App\Models\Student;
use App\Models\WasteLog;
use Illuminate\Database\Seeder;

class GreenSchoolSeeder extends Seeder
{
    public function run()
    {
        $employee = Employee::first() ?? Employee::factory()->create();
        $student = Student::first() ?? Student::factory()->create();

        CarbonMetric::create([
            'measurement_date' => now(),
            'electricity_kwh' => 1500.50,
            'water_usage_m3' => 200.00,
            'transport_emissions_kg' => 450.75,
            'paper_usage_kg' => 30.00,
            'total_carbon_footprint_kg' => 2181.25,
            'source_data' => ['sensor_1' => 'active'],
            'reduction_initiatives' => 'Pengurangan penggunaan listrik saat siang hari.',
        ]);

        WasteLog::create([
            'log_date' => now(),
            'waste_category' => 'Kertas',
            'weight_kg' => 10.5,
            'disposal_method' => 'bank_sampah',
            'revenue_generated' => 15000,
            'recorded_by' => $employee->id,
        ]);

        EcoProject::create([
            'project_title' => 'Taman Vertikal',
            'description' => 'Pembuatan taman vertikal di area sekolah.',
            'sdg_alignment' => [13, 15],
            'start_date' => now()->subDays(10),
            'end_date' => now()->addDays(20),
            'status' => 'in_progress',
            'participating_classes' => ['10A', '10B'],
        ]);

        EcoParticipation::create([
            'student_id' => $student->id,
            'challenge_type' => 'Bawa Tumbler',
            'participation_date' => now(),
            'points_awarded' => 10,
            'is_verified' => true,
            'verified_by' => $employee->id,
        ]);
    }
}
