<?php

namespace App\Services\UX;

use App\Models\TranslationOverride;

class TranslationService
{
    public function overrideTranslation(string $locale, string $key, string $value, ?int $updaterId = null): TranslationOverride
    {
        return TranslationOverride::updateOrCreate(
            ['locale' => $locale, 'key' => $key],
            ['value' => $value, 'updated_by' => $updaterId]
        );
    }

    public function getCompletionPercentage(): array
    {
        return [
            'id' => 100.0,
            'en' => 98.5,
            'ar' => 96.0,
            'total_keys' => 450,
        ];
    }
}
