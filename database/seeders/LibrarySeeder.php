<?php

namespace Database\Seeders;

use App\Models\LibraryCategory;
use Illuminate\Database\Seeder;

class LibrarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LibraryCategory::create(['name' => 'Buku Paket', 'icon' => 'book']);
        LibraryCategory::create(['name' => 'Referensi Guru', 'icon' => 'user-check']);
        LibraryCategory::create(['name' => 'Fiksi & Sastra', 'icon' => 'heart']);
        LibraryCategory::create(['name' => 'Modul Praktikum', 'icon' => 'cpu']);
        LibraryCategory::create(['name' => 'Karya Ilmiah', 'icon' => 'file-text']);
    }
}
