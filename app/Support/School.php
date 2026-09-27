<?php

namespace App\Support;

use App\Models\Setting;
use Throwable;

/**
 * The school's identity, read from the branding settings with
 * config/school.php as the fallback. Views and services use this instead of
 * spelling a name, address, phone number or coordinate out.
 */
final class School
{
    /** Official fields that must be filled by an admin before letters go out. */
    public const OFFICIAL_FIELDS = [
        'school_npsn' => 'NPSN',
        'school_akreditasi' => 'Akreditasi',
        'principal_name' => 'Nama Kepala Sekolah',
        'principal_nip' => 'NIP Kepala Sekolah',
    ];

    public static function name(): string
    {
        return (string) self::setting('school_name', 'name');
    }

    public static function shortName(): string
    {
        return (string) self::setting('school_short_name', 'short_name');
    }

    /** With $withAbbreviation false, a trailing "(YBWSA)"-style abbreviation is dropped. */
    public static function foundationName(bool $withAbbreviation = true): string
    {
        $name = (string) self::setting('foundation_name', 'foundation_name');

        return $withAbbreviation ? $name : trim(preg_replace('/\s*\([^()]*\)\s*$/', '', $name));
    }

    public static function npsn(): ?string
    {
        return self::setting('school_npsn', 'npsn');
    }

    public static function accreditation(): ?string
    {
        return self::setting('school_akreditasi', 'accreditation');
    }

    public static function address(): ?string
    {
        return self::setting('school_address', 'address');
    }

    public static function phone(): ?string
    {
        return self::setting('school_phone', 'phone');
    }

    public static function email(): ?string
    {
        return self::setting('school_email', 'email');
    }

    /** International format, e.g. +6289682585200 (used for wa.me links). */
    public static function whatsapp(): ?string
    {
        return self::setting('school_whatsapp', 'whatsapp');
    }

    /** Full URL, e.g. https://example.sch.id */
    public static function website(): ?string
    {
        return self::setting('school_website', 'website');
    }

    /** The website without scheme or trailing slash, for printing on documents. */
    public static function websiteLabel(): ?string
    {
        $url = self::website();

        return $url === null ? null : rtrim(preg_replace('#^https?://#i', '', $url), '/');
    }

    /** Null until an admin fills it — there is deliberately no default. */
    public static function principalName(): ?string
    {
        return self::setting('principal_name', 'principal_name');
    }

    /** Null until an admin fills it — there is deliberately no default. */
    public static function principalNip(): ?string
    {
        return self::setting('principal_nip', 'principal_nip');
    }

    /**
     * "Telp: … | Email: … | Website: …" style line with only the parts that
     * are known, so a missing value never prints as an empty label.
     *
     * @param  array<int, string>  $parts  any of address, phone, email, website
     */
    public static function contactLine(array $parts = ['address', 'phone', 'email'], string $separator = ' | '): string
    {
        $values = [
            'address' => self::address(),
            'phone' => ($p = self::phone()) ? 'Telp: '.$p : null,
            'email' => ($e = self::email()) ? 'Email: '.$e : null,
            'website' => ($w = self::websiteLabel()) ? 'Website: '.$w : null,
        ];

        return implode($separator, array_filter(array_map(fn ($part) => $values[$part] ?? null, $parts)));
    }

    /**
     * Centre and radius of the attendance geofence — the single source for
     * every GPS check-in path.
     *
     * @return array{latitude: float, longitude: float, radius_meters: float}
     */
    public static function campus(): array
    {
        $number = function (string $key, string $configKey): float {
            $value = self::setting($key, "campus.{$configKey}");

            return is_numeric($value) ? (float) $value : (float) config("school.campus.{$configKey}");
        };

        return [
            'latitude' => $number('school_latitude', 'latitude'),
            'longitude' => $number('school_longitude', 'longitude'),
            'radius_meters' => $number('school_geofence_radius', 'radius_meters'),
        ];
    }

    /**
     * Labels of official fields that are still empty (e.g. the principal's
     * NIP). Used to warn admins and on documents that would print them.
     *
     * @return array<string, string> setting key => label
     */
    public static function missingOfficialFields(): array
    {
        $values = [
            'school_npsn' => self::npsn(),
            'school_akreditasi' => self::accreditation(),
            'principal_name' => self::principalName(),
            'principal_nip' => self::principalNip(),
        ];

        return array_filter(self::OFFICIAL_FIELDS, fn ($label, $key) => blank($values[$key]), ARRAY_FILTER_USE_BOTH);
    }

    private static function setting(string $key, string $configKey): ?string
    {
        try {
            // '' (not null) as the default so a missing setting is cached as well.
            $value = Setting::get($key, '');
        } catch (Throwable) {
            $value = null; // settings table not migrated yet (fresh install, early boot)
        }

        if (filled($value)) {
            return trim((string) $value);
        }

        $fallback = config("school.{$configKey}");

        return filled($fallback) ? trim((string) $fallback) : null;
    }
}
