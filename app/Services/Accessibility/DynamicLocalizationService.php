<?php

declare(strict_types=1);

namespace App\Services\Accessibility;

use App\Models\LocalizationString;

class DynamicLocalizationService
{
    /**
     * Seed or update dynamic multi-language strings
     */
    public function updateString(string $group, string $key, string $textId, ?string $textEn = null, ?string $textAr = null): LocalizationString
    {
        return LocalizationString::updateOrCreate(
            ['group_name' => $group, 'string_key' => $key],
            [
                'text_id' => $textId,
                'text_en' => $textEn,
                'text_ar' => $textAr,
            ]
        );
    }
}
