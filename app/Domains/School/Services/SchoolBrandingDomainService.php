<?php

namespace App\Domains\School\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SchoolBrandingDomainService
{
    private const CACHE_KEY = 'single_school_branding_profile';

    /**
     * Get official school profile for SMA Islam Sultan Agung 1 Semarang.
     */
    public function getSchoolProfile(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            return [
                'school_name' => Setting::get('school_name', 'SMA Islam Sultan Agung 1 Semarang'),
                'foundation_name' => Setting::get('foundation_name', 'Yayasan Badan Wakaf Sultan Agung (YBWSA)'),
                'npsn' => Setting::get('school_npsn', '20328888'),
                'akreditasi' => Setting::get('school_akreditasi', 'A (Unggul)'),
                'address' => Setting::get('school_address', 'Jl. Mataram No. 657 Semarang, Jawa Tengah 50242'),
                'phone' => Setting::get('school_phone', '(024) 8314941'),
                'email' => Setting::get('school_email', 'info@smaislam1-smg.sch.id'),
                'website' => Setting::get('school_website', 'https://smaislam1-smg.sch.id'),
                'principal_name' => Setting::get('principal_name', 'Drs. H. Ahmad Dahlan, M.Pd.'),
                'principal_nip' => Setting::get('principal_nip', '196807151994031005'),
                'academic_year' => Setting::get('active_academic_year', '2026/2027 Semester Ganjil'),
                'logo_url' => Setting::get('school_logo_url', '/images/logo-ybwsa.png'),
            ];
        });
    }

    /**
     * Update school profile & clear branding cache.
     */
    public function updateSchoolProfile(array $data): bool
    {
        foreach ($data as $key => $val) {
            Setting::set($key, (string) $val);
        }

        Cache::forget(self::CACHE_KEY);
        return true;
    }

    /**
     * Get formal Kop Surat header array for PDF reports & official letters.
     */
    public function getFormalHeaderData(): array
    {
        $profile = $this->getSchoolProfile();

        return [
            'line_1' => strtoupper($profile['foundation_name']),
            'line_2' => strtoupper($profile['school_name']),
            'line_3' => 'AKREDITASI: ' . $profile['akreditasi'] . ' | NPSN: ' . $profile['npsn'],
            'line_4' => $profile['address'] . ' | Telp: ' . $profile['phone'],
            'principal_signature_title' => 'Kepala SMA Islam Sultan Agung 1',
            'principal_name' => $profile['principal_name'],
            'principal_nip' => $profile['principal_nip'],
        ];
    }
}
