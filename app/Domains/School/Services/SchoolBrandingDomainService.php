<?php

namespace App\Domains\School\Services;

use App\Models\AcademicYear;
use App\Models\Setting;
use App\Support\School;
use Illuminate\Support\Facades\Cache;

class SchoolBrandingDomainService
{
    private const CACHE_KEY = 'single_school_branding_profile';

    /**
     * The school profile, all from App\Support\School (settings, then
     * config/school.php).
     *
     * This used to fall back to invented values — NPSN 20328888, principal
     * "Drs. H. Ahmad Dahlan, M.Pd." with a made-up NIP, phone (024) 8314941 —
     * which then printed on kop surat. Official fields without a real value are
     * now null and listed in `missing_official_fields`.
     */
    public function getSchoolProfile(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            $campus = School::campus();

            return [
                'school_name' => School::name(),
                'school_short_name' => School::shortName(),
                'foundation_name' => School::foundationName(),
                'npsn' => School::npsn(),
                'akreditasi' => School::accreditation(),
                'address' => School::address(),
                'phone' => School::phone(),
                'email' => School::email(),
                'website' => School::website(),
                'principal_name' => School::principalName(),
                'principal_nip' => School::principalNip(),
                // The real active academic year, not a free-text setting.
                'academic_year' => $this->activeAcademicYearLabel(),
                'logo_url' => Setting::get('school_logo_url', '/images/logo-ybwsa.png'),
                'maps_url' => Setting::get('school_maps_url') ?: null,
                'latitude' => $campus['latitude'],
                'longitude' => $campus['longitude'],
                'geofence_radius' => $campus['radius_meters'],
                'missing_official_fields' => School::missingOfficialFields(),
            ];
        });
    }

    /**
     * Update school profile & clear branding cache.
     */
    public function updateSchoolProfile(array $data): bool
    {
        foreach ($data as $key => $val) {
            Setting::set($key, $val === null ? '' : (string) $val);
        }

        self::forgetCache();
        return true;
    }

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Get formal Kop Surat header array for PDF reports & official letters.
     * Parts without a real value are left out rather than printed as a
     * placeholder.
     */
    public function getFormalHeaderData(): array
    {
        $profile = $this->getSchoolProfile();

        $line3 = implode(' | ', array_filter([
            $profile['akreditasi'] ? 'AKREDITASI: '.$profile['akreditasi'] : null,
            $profile['npsn'] ? 'NPSN: '.$profile['npsn'] : null,
        ]));

        return [
            'line_1' => strtoupper($profile['foundation_name']),
            'line_2' => strtoupper($profile['school_name']),
            'line_3' => $line3,
            'line_4' => School::contactLine(['address', 'phone']),
            'principal_signature_title' => 'Kepala '.$profile['school_short_name'],
            'principal_name' => $profile['principal_name'],
            'principal_nip' => $profile['principal_nip'],
        ];
    }

    private function activeAcademicYearLabel(): ?string
    {
        $year = AcademicYear::current();

        return $year ? trim($year->year.' Semester '.$year->semester) : null;
    }
}
