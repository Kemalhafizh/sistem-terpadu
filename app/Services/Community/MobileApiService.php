<?php

namespace App\Services\Community;

use App\Models\MobileDevice;

class MobileApiService
{
    public function registerDevice(int $parentId, string $token, string $platform = 'android', ?string $appVersion = '1.0.0'): MobileDevice
    {
        return MobileDevice::updateOrCreate(
            ['parent_user_id' => $parentId, 'device_token' => $token],
            ['platform' => $platform, 'app_version' => $appVersion, 'last_active' => now()]
        );
    }
}
