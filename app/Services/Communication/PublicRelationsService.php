<?php

namespace App\Services\Communication;

use App\Models\PressRelease;

class PublicRelationsService
{
    public function createPressRelease($data)
    {
        return PressRelease::create($data);
    }

    public function distributeToMedia(PressRelease $release, array $mediaContactIds)
    {
        $release->update([
            'distributed_to' => $mediaContactIds,
            'status' => 'published',
        ]);

        return true;
    }

    public function logMediaPickup(PressRelease $release)
    {
        $release->increment('media_pickup_count');

        return $release;
    }
}
