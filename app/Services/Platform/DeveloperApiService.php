<?php

namespace App\Services\Platform;

use App\Models\DeveloperApiKey;
use Illuminate\Support\Str;

class DeveloperApiService
{
    public function generateApiKey(int $userId, string $appName, array $scopes = ['read']): DeveloperApiKey
    {
        return DeveloperApiKey::create([
            'user_id' => $userId,
            'app_name' => $appName,
            'api_key' => 'isa1_live_' . Str::random(32),
            'scopes' => $scopes,
            'is_active' => true,
        ]);
    }
}
