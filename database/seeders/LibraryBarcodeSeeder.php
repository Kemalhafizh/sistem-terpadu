<?php

namespace Database\Seeders;

use App\Models\LibraryBook;
use App\Models\LibraryCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LibraryBarcodeSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?: User::first();
        $category = LibraryCategory::first();

        if (! $category) {
            $category = LibraryCategory::create([
                'name' => 'Buku Paket Pelajaran',
                'icon' => 'book',
            ]);
        }

        // Check physical books
        $physicalBooks = LibraryBook::where('type', 'physical')->get();

        if ($physicalBooks->isEmpty()) {
            $titles = [
                ['title' => 'Matematika Tingkat Lanjut SMA Kelas XI', 'author' => 'Prof. Dr. Suparman', 'barcode' => 'BK-978602000001', 'nfc' => 'NFC-A1-00-11-22'],
                ['title' => 'Fisika Eksperimental & Gelombang', 'author' => 'Dr. Bambang Sugiarto', 'barcode' => 'BK-978602000002', 'nfc' => 'NFC-A2-00-11-33'],
                ['title' => 'Bahasa Indonesia & Apresiasi Sastra', 'author' => 'Siti Nurhaliza, M.Pd.', 'barcode' => 'BK-978602000003', 'nfc' => 'NFC-A3-00-11-44'],
                ['title' => 'Sejarah Kebudayaan Islam Nusantara', 'author' => 'KH. Ahmad Fauzi', 'barcode' => 'BK-978602000004', 'nfc' => 'NFC-A4-00-11-55'],
                ['title' => 'Biologi Molekuler & Genetika SMA', 'author' => 'Dr. Endang Rahayu', 'barcode' => 'BK-978602000005', 'nfc' => 'NFC-A5-00-11-66'],
            ];

            foreach ($titles as $item) {
                LibraryBook::create([
                    'uuid' => (string) Str::uuid(),
                    'title' => $item['title'],
                    'author' => $item['author'],
                    'category_id' => $category->id,
                    'type' => 'physical',
                    'barcode' => $item['barcode'],
                    'nfc_uid' => $item['nfc'],
                    'is_physical_available' => true,
                    'download_count' => 0,
                    'created_by' => $admin->id,
                ]);
            }
        } else {
            // Assign barcodes to existing books if empty
            $counter = 1;
            foreach ($physicalBooks as $book) {
                if (! $book->barcode) {
                    $book->update([
                        'barcode' => 'BK-97860200' . str_pad($counter, 4, '0', STR_PAD_LEFT),
                        'nfc_uid' => 'NFC-99-00-11-' . str_pad($counter, 2, '0', STR_PAD_LEFT),
                    ]);
                    $counter++;
                }
            }
        }
    }
}
