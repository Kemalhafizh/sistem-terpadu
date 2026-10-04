<?php

namespace App\Support;

/**
 * The languages the web and the app are offered in, and how a request picks
 * one. Web and API share this so a choice made on one shows on the other.
 */
final class Locale
{
    public const SUPPORTED = ['id', 'en', 'ar'];

    public static function isSupported(?string $locale): bool
    {
        return in_array($locale, self::SUPPORTED, true);
    }

    /**
     * The first supported language in an Accept-Language header
     * ("ar-SA,ar;q=0.9,en;q=0.8" → "ar"), honouring q-values; null when none.
     */
    public static function fromAcceptLanguage(?string $header): ?string
    {
        if (blank($header)) {
            return null;
        }

        $ranked = collect(explode(',', $header))
            ->map(function (string $part, int $position) {
                $pieces = array_map('trim', explode(';', $part));
                $quality = 1.0;
                foreach (array_slice($pieces, 1) as $param) {
                    if (str_starts_with($param, 'q=')) {
                        $quality = (float) substr($param, 2);
                    }
                }

                return [
                    'tag' => strtolower(strtok($pieces[0], '-_') ?: ''),
                    'q' => $quality,
                    'position' => $position,
                ];
            })
            ->filter(fn (array $item) => $item['q'] > 0)
            ->sortBy([['q', 'desc'], ['position', 'asc']]);

        foreach ($ranked as $item) {
            if (self::isSupported($item['tag'])) {
                return $item['tag'];
            }
        }

        return null;
    }
}
