<?php

namespace Database\Seeders;

use App\Models\SopDocument;
use App\Models\User;
use App\Services\Grc\SopGovernanceService;
use Illuminate\Database\Seeder;

class SopGovernanceSeeder extends Seeder
{
    public function run(): void
    {
        $sopService = app(SopGovernanceService::class);
        $user = User::first();

        if (! $user) {
            return;
        }

        // Seed Published SOP 1
        $sop1 = SopDocument::create([
            'title' => 'SOP Kebijakan Keamanan Informasi & Kata Sandi Sistem',
            'code' => 'SOP-IT-2026-001',
            'category' => 'it',
            'content_html' => "1. Setiap civitas akademika wajib memperbarui kata sandi secara berkala setiap 90 hari.\n2. Panjang kata sandi minimal 12 karakter mengombinasikan huruf kapital, angka, dan simbol.\n3. Dilarang membagikan kredensial autentikasi kepada pihak ketiga tanpa izin sah Tim IT GRC.",
            'version' => 1,
            'status' => 'published',
            'effective_date' => now()->toDateString(),
            'review_date' => now()->addYear()->toDateString(),
            'author_id' => $user->id,
            'approved_by' => $user->id,
        ]);

        $sopService->acknowledge($sop1->id, $user->id, '127.0.0.1');

        // Seed Published SOP 2
        $sop2 = SopDocument::create([
            'title' => 'SOP Tata Kelola Pembayaran SPP & Wadiah Keuangan',
            'code' => 'SOP-FIN-2026-002',
            'category' => 'financial',
            'content_html' => "1. Seluruh transaksi pembayaran SPP dan biaya pendidikan wajib dilakukan via kanal resmi Bank Syariah / Virtual Account.\n2. Bendahara sekolah menerbitkan bukti kwitansi sah berbasis e-Stamp digital.",
            'version' => 1,
            'status' => 'published',
            'effective_date' => now()->toDateString(),
            'review_date' => now()->addYear()->toDateString(),
            'author_id' => $user->id,
            'approved_by' => $user->id,
        ]);

        $sopService->acknowledge($sop2->id, $user->id, '127.0.0.1');
    }
}
