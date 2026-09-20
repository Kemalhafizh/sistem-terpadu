<?php

namespace Database\Seeders;

use App\Models\LibraryBook;
use App\Models\LibraryCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LibraryMobileDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::whereIn('role', ['admin', 'superadmin'])->first() ?: User::first();

        // 1. Kategori Buku Mobile
        $categoriesData = [
            'Sains & Teknologi' => 'atom',
            'Keislaman & Tarbiyah' => 'mosque',
            'Persiapan UTBK' => 'academic-cap',
            'Sastra & Bahasa' => 'book-open',
        ];

        $categories = [];
        foreach ($categoriesData as $name => $icon) {
            $categories[$name] = LibraryCategory::firstOrCreate(
                ['name' => $name],
                ['icon' => $icon]
            );
        }

        // 2. Daftar Buku Realistis Sinkron dengan Android Mockup
        $books = [
            [
                'title' => 'Fisika Dasar untuk Universitas & Persiapan UTBK',
                'author' => 'Halliday, Resnick, Walker',
                'isbn' => '978-602-1234-56-1',
                'barcode' => '9786021234561',
                'shelf_location' => 'Rak A-02 (Lantai 1)',
                'category' => 'Sains & Teknologi',
                'synopsis' => 'Referensi lengkap teori mekanika, termodinamika, gelombang, dan fisika modern dengan ribuan contoh soal.',
                'total_copies' => 8,
                'available_copies' => 5,
                'rating' => 4.90,
            ],
            [
                'title' => 'Tafsir Al-Azhar: Surat Al-Baqarah & Ali Imran',
                'author' => 'Prof. Dr. Buya Hamka',
                'isbn' => '978-979-418-091-2',
                'barcode' => '9789794180912',
                'shelf_location' => 'Rak B-01 (Koleksi Khusus Islami)',
                'category' => 'Keislaman & Tarbiyah',
                'synopsis' => 'Karya monumental Buya Hamka yang membedah ayat Al-Qur\'an secara mendalam dengan pendekatan sastra dan realitas keumatan Nusantara.',
                'total_copies' => 4,
                'available_copies' => 3,
                'rating' => 5.00,
            ],
            [
                'title' => 'Mastering TPS & Penalaran Matematika SNBT 2026',
                'author' => 'Tim Litbang Pendidikan Sultan Agung',
                'isbn' => '978-623-8890-11-0',
                'barcode' => '9786238890110',
                'shelf_location' => 'Rak C-05 (Koleksi Kelas 12)',
                'category' => 'Persiapan UTBK',
                'synopsis' => 'Bank soal drilling terupdate metode IRT Rasch lengkap dengan trik cepat logika matematika & bahasa Inggris.',
                'total_copies' => 15,
                'available_copies' => 12,
                'rating' => 4.80,
            ],
            [
                'title' => 'Laskar Pelangi (Edisi Spesial)',
                'author' => 'Andrea Hirata',
                'isbn' => '978-979-3062-79-2',
                'barcode' => '9789793062792',
                'shelf_location' => 'Rak D-03 (Fiksi & Novel)',
                'category' => 'Sastra & Bahasa',
                'synopsis' => 'Kisah inspiratif tentang perjuangan sepuluh anak di Belitung dalam meraih cita-cita pendidikan tinggi.',
                'total_copies' => 6,
                'available_copies' => 2,
                'rating' => 4.90,
            ],
        ];

        foreach ($books as $b) {
            $cat = $categories[$b['category']];

            LibraryBook::updateOrCreate(
                ['isbn' => $b['isbn']],
                [
                    'uuid' => (string) Str::uuid(),
                    'title' => $b['title'],
                    'author' => $b['author'],
                    'barcode' => $b['barcode'],
                    'shelf_location' => $b['shelf_location'],
                    'category_id' => $cat->id,
                    'type' => 'physical',
                    'description' => $b['synopsis'],
                    'is_physical_available' => $b['available_copies'] > 0,
                    'total_copies' => $b['total_copies'],
                    'available_copies' => $b['available_copies'],
                    'rating' => $b['rating'],
                    'download_count' => 0,
                    'created_by' => $admin?->id ?: 1,
                ]
            );
        }
    }
}
