<?php

namespace Database\Seeders;

use App\Models\BusPassenger;
use App\Models\BusRoute;
use App\Models\CarbonEmission;
use App\Models\FacilityAsset;
use App\Models\IotDevice;
use App\Models\IotSensorReading;
use App\Models\MaintenanceLog;
use App\Models\SchoolBus;
use App\Models\User;
use App\Models\UtilityBill;
use Illuminate\Database\Seeder;

class FacilityOperationsSeeder extends Seeder
{
    public function run(): void
    {
        $tech = User::where('role', 'staf_tu')->first() ?? User::factory()->create(['role' => 'staf_tu', 'name' => 'Teknisi Sarpras']);
        $student = User::where('role', 'siswa')->first() ?? User::factory()->create(['role' => 'siswa', 'name' => 'Siswa Bus']);

        // 42.1: IoT Device & Readings
        $dev = IotDevice::create([
            'device_name' => 'Smart Meter Listrik Lab Komputer A',
            'device_type' => 'power_meter',
            'location' => 'Gedung Rektorat Lt. 2',
            'ip_address' => '192.168.10.45',
            'status' => 'online',
        ]);

        IotSensorReading::create([
            'device_id' => $dev->id,
            'metric_value' => 450.75,
            'metric_unit' => 'kWh',
            'read_at' => now(),
        ]);

        // 42.2: Utility Bill & Carbon
        UtilityBill::create([
            'utility_type' => 'electricity',
            'billing_period' => 'Mei 2026',
            'amount' => 12500000,
            'usage_kwh_m3' => 14700,
            'is_paid' => true,
            'due_date' => now()->subDays(5),
        ]);

        CarbonEmission::create([
            'period' => 'Mei 2026',
            'co2_kg' => 12495.00,
            'source_category' => 'electricity',
        ]);

        // 42.3: Bus & Route
        $bus = SchoolBus::create([
            'plate_number' => 'H 1234 ISA',
            'driver_name' => 'Suharto',
            'driver_phone' => '081234567890',
            'capacity' => 30,
            'is_active' => true,
        ]);

        $route = BusRoute::create([
            'bus_id' => $bus->id,
            'route_name' => 'Rute 1: Simpang Lima - Masjid Agung - ISA 1',
            'departure_time' => '06:00:00',
            'arrival_time' => '06:45:00',
        ]);

        BusPassenger::create([
            'route_id' => $route->id,
            'student_id' => $student->id,
            'pickup_point' => 'Halte Simpang Lima',
        ]);

        // 42.4: Asset & Maintenance Log
        $ast = FacilityAsset::create([
            'asset_tag' => 'AST-ISA1-AC-09',
            'name' => 'AC Inverter Daikin 2 PK Lab Fisika',
            'category' => 'Elektronik & Pendingin',
            'location' => 'Lab Fisika Lt. 1',
            'condition' => 'good',
            'purchase_date' => now()->subYears(1),
            'cost' => 8500000,
        ]);

        MaintenanceLog::create([
            'asset_id' => $ast->id,
            'technician_id' => $tech->id,
            'service_type' => 'Cuci AC & Cek Freon Rutin',
            'findings' => 'Kondisi freon normal, filter sudah dibersihkan.',
            'cost' => 150000,
            'service_date' => now()->subDays(30),
            'next_service_due' => now()->addMonths(5),
        ]);
    }
}
