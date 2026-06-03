<?php

namespace Database\Seeders;

use App\Models\MutabaahItem;
use Illuminate\Database\Seeder;

class IslamicModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            // Ibadah Wajib
            ['name' => 'Shalat Subuh Berjamaah', 'category' => 'wajib'],
            ['name' => 'Shalat Dzuhur Berjamaah', 'category' => 'wajib'],
            ['name' => 'Shalat Ashar Berjamaah', 'category' => 'wajib'],
            ['name' => 'Shalat Maghrib Berjamaah', 'category' => 'wajib'],
            ['name' => 'Shalat Isya Berjamaah', 'category' => 'wajib'],

            // Ibadah Sunnah
            ['name' => 'Shalat Dhuha', 'category' => 'sunnah'],
            ['name' => 'Shalat Tahajjud', 'category' => 'sunnah'],
            ['name' => 'Rawatib (Qabliyah/Ba\'diyah)', 'category' => 'sunnah'],
            ['name' => 'Tilawah Al-Qur\'an', 'category' => 'sunnah'],
            ['name' => 'Al-Ma\'tsurat (Dzikir Pagi/Petang)', 'category' => 'sunnah'],

            // Akhlak & Target Padi
            ['name' => 'Birrul Walidain (Berbakti pada Ortu)', 'category' => 'akhlak'],
            ['name' => 'Merapikan Tempat Tidur', 'category' => 'akhlak'],
            ['name' => 'Membaca Buku/Literasi', 'category' => 'akhlak'],
        ];

        foreach ($items as $item) {
            MutabaahItem::updateOrCreate(['name' => $item['name']], $item);
        }
    }
}
