<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\Content\MediaAssetService;
use Illuminate\Database\Seeder;

class DigitalAssetSeeder extends Seeder
{
    public function run(): void
    {
        $mediaService = app(MediaAssetService::class);
        $uploader = User::first();

        if (! $uploader) {
            return;
        }

        // Seed Root Folders
        $folderKurikulum = $mediaService->createFolder('Dokumen Kurikulum & Silabus', $uploader->id);
        $folderFoto = $mediaService->createFolder('Fotografi Kegiatan & Upacara', $uploader->id);
        $folderKeuangan = $mediaService->createFolder('Arsip Kwitansi Keuangan', $uploader->id);

        // Seed Media Assets
        $mediaService->upload([
            'original_filename' => 'Silabus_Kurikulum_Merdeka_Fisika_2026.pdf',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 4587520, // ~4.3 MB
        ], $uploader->id, $folderKurikulum->id, ['kurikulum', 'pdf', 'fisika']);

        $mediaService->upload([
            'original_filename' => 'Dokumentasi_Hari_Santri_Nasional.jpg',
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => 2457600, // ~2.3 MB
        ], $uploader->id, $folderFoto->id, ['foto', 'upacara', 'santri']);

        $mediaService->upload([
            'original_filename' => 'Logo_Resmi_SMA_Islam_Sultan_Agung_1.png',
            'mime_type' => 'image/png',
            'file_size_bytes' => 512000, // ~500 KB
        ], $uploader->id, null, ['logo', 'branding', 'png'], true);
    }
}
