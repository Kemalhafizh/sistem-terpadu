<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Room;
use App\Models\User;
use App\Models\AssetMaintenanceTicket;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AssetMaintenanceTicketSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?: User::first();
        $teachers = User::where('role', 'guru')->get();
        if ($teachers->isEmpty()) {
            $teachers = collect([$admin]);
        }

        // Fetch or create room/category if none
        $room = Room::first();
        if (! $room) {
            $room = Room::create([
                'name' => 'Lab Komputer 1',
                'location' => 'Gedung A Lantai 2',
            ]);
        }

        $category = AssetCategory::first();
        if (! $category) {
            $category = AssetCategory::create([
                'name' => 'Elektronik & Gadget',
                'code' => 'ELK',
            ]);
        }

        // Fetch or create some assets
        $assets = Asset::take(5)->get();
        if ($assets->isEmpty()) {
            $items = [
                ['name' => 'Proyektor BenQ MH560', 'code' => 'AST-PRJ-001'],
                ['name' => 'Air Conditioner Daikin 2 PK', 'code' => 'AST-AC-001'],
                ['name' => 'PC Lab Komputer Asus H61', 'code' => 'AST-PC-012'],
                ['name' => 'Kursi Kayu Guru', 'code' => 'AST-KRS-005'],
                ['name' => 'Dispenser Air Sharp', 'code' => 'AST-DSP-002'],
            ];

            $assets = collect();
            foreach ($items as $item) {
                $assets->push(Asset::create([
                    'uuid' => (string) Str::uuid(),
                    'asset_code' => $item['code'],
                    'name' => $item['name'],
                    'category_id' => $category->id,
                    'room_id' => $room->id,
                    'purchase_date' => Carbon::now()->subYears(2),
                    'purchase_price' => 5000000.00,
                    'useful_life_years' => 5,
                    'is_borrowable' => true,
                    'condition' => 'good',
                    'status' => 'available',
                    'created_by' => $admin->id,
                ]));
            }
        }

        // Create 15 tickets
        $issues = [
            'Proyektor berkedip dan lampu redup saat digunakan mengajar.',
            'AC mengeluarkan suara bising dan tidak mengeluarkan hawa dingin.',
            'Layar PC bergaris-garis biru dan sering restart sendiri.',
            'Kaki kursi goyang dan baut penahan bawahnya lepas.',
            'Dispenser air tidak memanaskan air, lampu indikator mati.',
            'Proyektor mati total, tidak merespon saat tombol power ditekan.',
            'Remote AC hilang, suhu terkunci di 26 derajat Celsius.',
            'Keyboard dan mouse PC Lab mati total tidak terdeteksi.',
            'Sandaran kursi patah sebagian.',
            'Kran air dispenser bocor menetes terus menerus.',
        ];

        $priorities = ['low', 'medium', 'high', 'critical'];
        $statuses = ['pending', 'investigating', 'repairing', 'resolved', 'scrapped'];

        for ($i = 0; $i < 12; $i++) {
            $asset = $assets->random();
            $reporter = $teachers->random();
            $status = collect($statuses)->random();
            $priority = collect($priorities)->random();

            $date = Carbon::now()->subDays(rand(1, 10))->setTime(rand(8, 16), rand(0, 59));

            $resolvedBy = null;
            $resolvedAt = null;
            $notes = null;

            if (in_array($status, ['resolved', 'scrapped'])) {
                $resolvedBy = $admin->id;
                $resolvedAt = $date->copy()->addDays(rand(1, 2));
                $notes = $status === 'resolved' 
                    ? 'Sudah diperbaiki dengan penggantian suku cadang yang rusak.' 
                    : 'Aset afkir, biaya perbaikan melebihi nilai sisa aset. Direkomendasikan untuk dihapus dari inventaris.';
            }

            AssetMaintenanceTicket::create([
                'asset_id' => $asset->id,
                'reported_by' => $reporter->id,
                'issue_description' => collect($issues)->random(),
                'priority' => $priority,
                'status' => $status,
                'photo_evidence' => null,
                'resolution_notes' => $notes,
                'resolved_by' => $resolvedBy,
                'resolved_at' => $resolvedAt,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }
}
