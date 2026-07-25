<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\Communication\NotificationDispatchService;
use Illuminate\Database\Seeder;

class NotificationCenterSeeder extends Seeder
{
    public function run(): void
    {
        $dispatchService = app(NotificationDispatchService::class);
        $users = User::take(5)->get();

        if ($users->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            // Seed Preferences
            $dispatchService->updatePreferences($user->id, [
                'academic' => ['inapp' => true, 'email' => true, 'whatsapp' => true, 'push' => false],
                'finance' => ['inapp' => true, 'email' => true, 'whatsapp' => false, 'push' => false],
                'discipline' => ['inapp' => true, 'email' => true, 'whatsapp' => true, 'push' => true],
                'system' => ['inapp' => true, 'email' => false, 'whatsapp' => false, 'push' => false],
            ]);

            // Seed Notification Logs
            $dispatchService->dispatch(
                $user->id,
                'academic.grade_published',
                'Nilai Rapor Semester Genap Telah Terbit',
                'Nilai hasil evaluasi belajar akhir semester Anda telah diterbitkan secara resmi oleh wali kelas.',
                ['semester' => 'Genap', 'academic_year' => '2025/2026'],
                '/academic/grades'
            );

            $dispatchService->dispatch(
                $user->id,
                'finance.spp_due',
                'Tagihan SPP Bulan Juli 2026 Siap Dibayar',
                'Tagihan biaya pendidikan SPP bulanan Anda untuk periode Juli 2026 telah tersedia di portal keuangan.',
                ['amount' => 750000],
                '/finance/payments'
            );

            $dispatchService->dispatch(
                $user->id,
                'system.security_alert',
                'Sesi Login Baru Terdeteksi',
                'Sistem mendeteksi akses masuk dari perangkat Windows Chrome pada IP 110.43.198.12.',
                ['ip' => '110.43.198.12'],
                '/admin/security/2fa'
            );
        }
    }
}
