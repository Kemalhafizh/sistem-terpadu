<?php

namespace App\Services\Debloat;

use App\Models\StaticModuleCatalog;

class PragmaticModuleCatalogService
{
    /**
     * Get static monolithic active modules catalog.
     * Replaces WeChat dynamic remote JS/HTML module loader.
     */
    public function getActiveCatalog(): array
    {
        $modules = StaticModuleCatalog::where('is_active', true)
            ->orderBy('display_order')
            ->get();

        if ($modules->isEmpty()) {
            return $this->getDefaultCatalog();
        }

        return $modules->toArray();
    }

    /**
     * Default statically verified modules for SMA Islam Sultan Agung 1.
     */
    public function getDefaultCatalog(): array
    {
        return [
            [
                'module_code' => 'academic_schedule',
                'name' => 'Jadwal Pelajaran KBM',
                'category' => 'Akademik & LMS',
                'route_name' => 'schedule',
                'icon' => 'CalendarMonth',
            ],
            [
                'module_code' => 'academic_grades',
                'name' => 'Rapor & Capaian KKTP',
                'category' => 'Akademik & LMS',
                'route_name' => 'grades',
                'icon' => 'AutoGraph',
            ],
            [
                'module_code' => 'attendance_geofence',
                'name' => 'Presensi GPS Kampus',
                'category' => 'Presensi & Kehadiran',
                'route_name' => 'geofence_attendance',
                'icon' => 'LocationOn',
            ],
            [
                'module_code' => 'ibadah_mutabaah',
                'name' => 'Mutabaah Yaumiyah',
                'category' => 'Karakter & Ibadah',
                'route_name' => 'mutabaah',
                'icon' => 'Mosque',
            ],
            [
                'module_code' => 'discipline_points',
                'name' => 'Buku Saku Poin Kedisiplinan',
                'category' => 'Tata Tertib',
                'route_name' => 'discipline',
                'icon' => 'Gavel',
            ],
            [
                'module_code' => 'finance_billing',
                'name' => 'Tagihan SPP & Virtual Account',
                'category' => 'Keuangan',
                'route_name' => 'billing',
                'icon' => 'AccountBalanceWallet',
            ],
            [
                'module_code' => 'library_catalog',
                'name' => 'E-Pustaka Pintar',
                'category' => 'Perpustakaan',
                'route_name' => 'library_catalog',
                'icon' => 'LocalLibrary',
            ],
            [
                'module_code' => 'utbk_simulation',
                'name' => 'Simulasi UTBK & Analisis PTN',
                'category' => 'Persiapan Kuliah',
                'route_name' => 'utbk_tryout',
                'icon' => 'Psychology',
            ],
            [
                'module_code' => 'extracurricular',
                'name' => 'Ekstrakurikuler & OSIS',
                'category' => 'Kesiswaan',
                'route_name' => 'extracurricular',
                'icon' => 'SportsSoccer',
            ],
            [
                'module_code' => 'achievement_portfolio',
                'name' => 'Portofolio Prestasi & Sertifikat',
                'category' => 'Kesiswaan',
                'route_name' => 'achievement_upload',
                'icon' => 'EmojiEvents',
            ],
        ];
    }
}
