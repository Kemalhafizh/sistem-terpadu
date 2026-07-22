<?php

namespace Database\Seeders;

use App\Models\PortfolioBadge;
use Illuminate\Database\Seeder;

class PortfolioBadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badges = [
            [
                'name' => 'Juara Kelas',
                'icon' => '',
                'criteria' => 'Dianugerahkan kepada siswa yang meraih peringkat 1 paralel atau juara kelas berdasarkan nilai rapor akhir semester.',
                'color' => '#f59e0b', // Amber/Emas
            ],
            [
                'name' => 'Teladan Ibadah',
                'icon' => '',
                'criteria' => 'Siswa teladan dalam pelaksanaan ibadah harian, hafalan Qur\'an, mutaba\'ah yaumiyah, dan akhlakul karimah.',
                'color' => '#10b981', // Emerald/Hijau
            ],
            [
                'name' => 'Duta Seni & Sastra',
                'icon' => '',
                'criteria' => 'Meraih prestasi gemilang dalam perlombaan seni rupa, musik, teater, kaligrafi, maupun karya sastra tulis.',
                'color' => '#ec4899', // Pink
            ],
            [
                'name' => 'Atlet Utama',
                'icon' => '',
                'criteria' => 'Mengharumkan nama sekolah dalam kompetisi olahraga, kejuaraan daerah (Kejurda), maupun kejuaraan nasional.',
                'color' => '#ef4444', // Red
            ],
            [
                'name' => 'Pelopor Sains & IT',
                'icon' => '',
                'criteria' => 'Berpartisipasi aktif dan berprestasi dalam Karya Ilmiah Remaja (KIR), Olimpiade Sains, riset teknologi, atau bidang IT.',
                'color' => '#3b82f6', // Blue
            ],
            [
                'name' => 'Tokoh Organisasi',
                'icon' => '',
                'criteria' => 'Menunjukkan kepemimpinan yang cemerlang, keaktifan berorganisasi di OSIS/MPK, Pramuka, maupun kepanitiaan sekolah.',
                'color' => '#8b5cf6', // Purple
            ],
        ];

        foreach ($badges as $badge) {
            PortfolioBadge::updateOrCreate(
                ['name' => $badge['name']],
                $badge
            );
        }
    }
}
