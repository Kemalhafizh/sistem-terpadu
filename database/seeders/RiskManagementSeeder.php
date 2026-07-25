<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\Grc\RiskManagementService;
use Illuminate\Database\Seeder;

class RiskManagementSeeder extends Seeder
{
    public function run(): void
    {
        $riskService = app(RiskManagementService::class);
        $user = User::first();

        if (! $user) {
            return;
        }

        // Seed Risk Register Entries
        $riskService->assessRisk([
            'title' => 'Serangan Siber ransomware / kebocoran data siswa',
            'description' => 'Potensi peretasan server database utama akibat malware.',
            'category' => 'IT/Cyber',
            'likelihood' => 'possible',
            'impact' => 'catastrophic', // Score: 3x5 = 15 (Extreme)
            'mitigation_plan' => 'Penerapan Enkripsi AES-256 dan Backup cloud offline harian.',
            'status' => 'mitigating',
        ], $user->id);

        $riskService->assessRisk([
            'title' => 'Keterlambatan Pembayaran SPP Orang Tua Siswa',
            'description' => 'Potensi arus kas sekolah terganggu akibat piutang SPP.',
            'category' => 'Financial',
            'likelihood' => 'likely',
            'impact' => 'moderate', // Score: 4x3 = 12 (High)
            'mitigation_plan' => 'Integrasi Payment Gateway VA dan Notifikasi Otomatis WhatsApp.',
            'status' => 'mitigating',
        ], $user->id);

        $riskService->assessRisk([
            'title' => 'Genangan Air Saat Musim Hujan Di Lapangan Utama',
            'description' => 'Potensi kegiatan olahraga terganggu.',
            'category' => 'Safety',
            'likelihood' => 'unlikely',
            'impact' => 'minor', // Score: 2x2 = 4 (Low)
            'mitigation_plan' => 'Pembersihan saluran drainase utama berkala.',
            'status' => 'resolved',
        ], $user->id);

        // Seed Incident Report
        $inc = $riskService->createIncident([
            'title' => 'Pemadaman Listrik Mendadak Saat CBT Online',
            'description' => 'Aliran PLN terputus 15 menit saat pelaksanaan Uji Coba CBT.',
            'category' => 'Safety & Infrastructure',
            'severity' => 'medium',
        ], $user->id);

        $riskService->resolveIncident(
            $inc->id,
            'Genset cadangan otomatis aktif 30 detik pasca pemadaman. Sesi ujian dilanjutkan.',
            $user->id
        );
    }
}
