<?php

namespace Database\Seeders;

use App\Models\AiRecommendation;
use App\Models\CustomDashboard;
use App\Models\DashboardWidget;
use App\Models\DeveloperApiKey;
use App\Models\DevWebhookEndpoint;
use App\Models\DigitalTwinSimulation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PlatformMaturitySeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::where('role', 'superadmin')->first() ?? User::factory()->create(['role' => 'superadmin', 'name' => 'Superadmin Developer']);
        $student = User::where('role', 'siswa')->first() ?? User::factory()->create(['role' => 'siswa', 'name' => 'Siswa Enterprise']);

        // 44.1: Developer API & Webhooks
        DeveloperApiKey::create([
            'user_id' => $superadmin->id,
            'app_name' => 'ISA1 Mobile Parent App Gateway',
            'api_key' => 'isa1_live_'.Str::random(32),
            'scopes' => ['grades:read', 'attendance:read', 'notifications:send'],
            'is_active' => true,
            'last_used_at' => now(),
        ]);

        DevWebhookEndpoint::create([
            'user_id' => $superadmin->id,
            'target_url' => 'https://api.sultanagung1.sch.id/webhooks/v1/events',
            'event_types' => ['student.absent', 'payment.received', 'grade.published'],
            'secret' => Str::random(32),
            'is_active' => true,
        ]);

        // 44.2: Dashboard Builder
        $db = CustomDashboard::create([
            'user_id' => $superadmin->id,
            'title' => 'Executive Headmaster Control Center',
            'description' => 'Dashboard ringkasan real-time Kepala Sekolah SMA ISA 1 Semarang',
            'is_default' => true,
        ]);

        DashboardWidget::create([
            'dashboard_id' => $db->id,
            'widget_type' => 'chart_attendance',
            'title' => 'Tingkat Kehadiran Siswa Hari Ini',
            'grid_x' => 0,
            'grid_y' => 0,
            'grid_width' => 6,
            'grid_height' => 4,
        ]);

        // 44.3: AI Recommendation
        AiRecommendation::create([
            'user_id' => $student->id,
            'recommendation_type' => 'academic_tutoring',
            'suggestion_text' => 'Disarankan mengambil sesi pengayaan Fisika Gelombang Elektromagnetik berdasarkan evaluasi Bab 4.',
            'confidence_score' => 0.94,
            'status' => 'unread',
        ]);

        // 44.4: Digital Twin Simulation
        DigitalTwinSimulation::create([
            'scenario_name' => 'Simulasi Penambahan 2 Rombel Kelas XI & Beban Laboratorium Komputer',
            'parameters' => 'Kapasitas +72 siswa, Jam Praktek +8 jam/minggu, Beban Listrik +3.5 kWh',
            'predicted_outcomes' => [
                'lab_utilization_percent' => 88.5,
                'power_cost_increase_monthly' => 1450000,
                'teacher_workload_impact' => 'Optimal',
            ],
            'created_by' => $superadmin->id,
        ]);
    }
}
