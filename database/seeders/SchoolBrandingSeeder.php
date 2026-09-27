<?php

namespace Database\Seeders;

use App\Domains\School\Services\SchoolBrandingDomainService;
use Illuminate\Database\Seeder;

class SchoolBrandingSeeder extends Seeder
{
    /**
     * Seeds nothing on purpose. This used to write an invented NPSN
     * (20328888), a placeholder principal ("Drs. H. Ahmad Dahlan, M.Pd.") with
     * a made-up NIP, a phone number and e-mail the school does not use, and
     * campus coordinates ~1.3 km off — all of which printed on official
     * documents and moved the attendance geofence.
     *
     * Institutional data now falls back to config/school.php (checked against
     * the school's own website); the principal's name and NIP stay empty until
     * an admin enters them, and Admin > Profil Sekolah says so.
     */
    public function run(): void
    {
        SchoolBrandingDomainService::forgetCache();
    }
}
