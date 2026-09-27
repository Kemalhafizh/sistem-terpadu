<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\DataAnonymizerService;
use Illuminate\Database\Seeder;

class PdplPrivacySeeder extends Seeder
{
    public function run(): void
    {
        $service = new DataAnonymizerService;

        // Verify PII masking rules
        assert($service->maskNik('3374012304950001') === '3374**********01');
        assert($service->maskPhone('081234567890') === '0812****7890');

        // Create sample student user for privacy testing
        User::factory()->create([
            'name' => 'Siswa Test Privacy PDP',
            'email' => 'pdp_student_test@smaisa1.sch.id',
            'role' => 'student',
        ]);
    }
}
