<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/*
 * Removes school settings that were planted by seeders or by the payroll
 * migration and never came from the school:
 *
 *  - NPSN 20328888 (the real NPSN is 20328918), principal
 *    "Drs. H. Ahmad Dahlan, M.Pd." with a made-up NIP, and phone/e-mail values
 *    that match nothing the school publishes — these printed on kop surat.
 *  - Campus coordinates -6.9535/110.4355 (payroll migration) and
 *    -6.9859/110.4398 (branding seeder), 1.3–4.8 km from the school, plus the
 *    100 m / 200 m radii that went with them. The branding form could never
 *    save coordinates (they were not validated, so they were dropped), which
 *    means every stored coordinate came from a migration or seeder.
 *
 * Only rows whose value is exactly one of those planted values are deleted,
 * so anything an admin actually entered stays. With the rows gone,
 * App\Support\School falls back to config/school.php (verified institutional
 * data; no default at all for the principal).
 */
return new class extends Migration
{
    /** key => planted values to remove (null = remove the key whatever its value). */
    private const PLANTED = [
        'school_npsn' => ['20328888'],
        'principal_name' => ['Drs. H. Ahmad Dahlan, M.Pd.'],
        'principal_nip' => ['196807151994031005'],
        'school_phone' => ['(024) 8314941', '021-1234567'],
        'school_email' => ['info@smaislam1-smg.sch.id', 'info@sistem-terpadu.sch.id'],
        'school_website' => ['https://smaislam1-smg.sch.id'],
        'school_maps_url' => ['https://maps.app.goo.gl/2Lp8Pa6BVvmHE1VJA'],
        'school_latitude' => ['-6.9535', '-6.9859'],
        'school_longitude' => ['110.4355', '110.4398'],
        'school_geofence_radius' => ['200'],
        // Superseded by school_geofence_radius (one radius for every check-in).
        'school_attendance_radius' => null,
        // The active year now comes from academic_years, not free text.
        'active_academic_year' => null,
    ];

    public function up(): void
    {
        foreach (self::PLANTED as $key => $values) {
            $query = DB::table('settings')->where('key', $key);
            if ($values !== null) {
                $query->whereIn('value', $values);
            }
            $query->delete();

            Cache::forget("setting_{$key}");
        }

        Cache::forget('settings_all');
        Cache::forget('single_school_branding_profile');
    }

    public function down(): void
    {
        // Intentionally irreversible: the removed values were never real.
    }
};
