<?php

namespace Database\Seeders;

use App\Domains\School\Services\SchoolBrandingDomainService;
use App\Services\EnterpriseCacheService;
use Illuminate\Database\Seeder;

class CacheWarmupSeeder extends Seeder
{
    public function run(): void
    {
        $brandingService = new SchoolBrandingDomainService;
        $cacheService = new EnterpriseCacheService;

        // Pre-warm school profile
        $brandingService->getSchoolProfile();

        // Pre-warm academic & system flags
        $cacheService->remember('academic_active_semester', 3600, fn () => 'Semester Ganjil 2026/2027', ['academic']);
        $cacheService->remember('system_maintenance_mode', 3600, fn () => false, ['system']);
    }
}
