<?php

namespace App\Services\Deployment;

use App\Models\ReleaseVersion;

class ReleaseManagerService
{
    public function createRelease(string $version, string $changelog, string $releaseType = 'minor', ?string $releasedBy = null): ReleaseVersion
    {
        return ReleaseVersion::create([
            'version' => $version,
            'release_type' => $releaseType,
            'changelog_summary' => $changelog,
            'breaking_changes' => [],
            'migration_count' => 50,
            'released_by' => $releasedBy ?? 'DevOps Team',
            'released_at' => now(),
            'rollback_tested' => true,
        ]);
    }
}
