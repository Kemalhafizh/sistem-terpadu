<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use App\Models\Room;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AssetCategory::create(['name' => 'Elektronik', 'code' => 'ELK']);
        AssetCategory::create(['name' => 'Mebeul', 'code' => 'MBL']);
        AssetCategory::create(['name' => 'Alat Olahraga', 'code' => 'OLR']);

        Room::create(['name' => 'Lab Komputer 1', 'location' => 'Gedung A LT 2']);
        Room::create(['name' => 'Ruang Guru', 'location' => 'Gedung Utama LT 1']);
        Room::create(['name' => 'Gudang Sarpras', 'location' => 'Belakang Kantin']);
    }
}
