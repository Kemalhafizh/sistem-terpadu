<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolSafetySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cctv_incidents')->insert([
            ['camera_id' => 'CAM-01', 'zone_name' => 'Gerbang Utama', 'incident_time' => Carbon::now(), 'incident_type' => 'unauthorized_access', 'status' => 'open'],
        ]);
        DB::table('emergency_alerts')->insert([
            ['trigger_source' => 'Panic Button 3', 'location_description' => 'Laboratorium Kimia', 'alert_level' => 'critical'],
        ]);
        DB::table('safety_drills')->insert([
            ['drill_type' => 'Evakuasi Kebakaran', 'scheduled_date' => Carbon::now()->addDays(5), 'compliance_status' => 'scheduled'],
        ]);
        // Note: for zone breaches we need student_id, omitting raw insert or handle constraint properly (assume dummy)
        try {
            DB::table('zone_breaches')->insert([
                ['student_id' => 1, 'zone_name' => 'Area Parkir', 'breach_type' => 'unauthorized_exit', 'breach_time' => Carbon::now(), 'detected_by' => 'CAM-05'],
            ]);
        } catch (\Exception $e) {
        }
    }
}
