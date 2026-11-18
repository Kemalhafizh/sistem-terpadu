<?php

namespace App\Services\Islamic;

use App\Models\Setting;
use Carbon\CarbonImmutable;
use DateTimeZone;

/**
 * Menghitung jadwal sholat 5 waktu berdasarkan koordinat sekolah di pengaturan
 * menggunakan parameter resmi Kementerian Agama RI (Subuh -20°, Isya -18°,
 * Syafi'i Ashar faktor bayangan 1, ditambah ihtiyat 2 menit).
 *
 * Jika koordinat sekolah belum diatur, mengembalikan null (tanpa jadwal contoh).
 */
class PrayerTimeService
{
    public const DEFAULT_TIMEZONE = 'Asia/Jakarta';

    /**
     * @return array{
     *     date: string,
     *     timezone: string,
     *     location: string|null,
     *     coordinates: array{latitude: float, longitude: float},
     *     times: array{subuh: string, terbit: string, dzuhur: string, ashar: string, maghrib: string, isya: string},
     *     iso_times: array{subuh: string, terbit: string, dzuhur: string, ashar: string, maghrib: string, isya: string}
     * }|null
     */
    public function forSchool(?string $date = null): ?array
    {
        $rawLat = Setting::get('school_latitude');
        $rawLng = Setting::get('school_longitude');

        if ($rawLat === null || $rawLng === null || $rawLat === '' || $rawLng === '' || ! is_numeric($rawLat) || ! is_numeric($rawLng)) {
            return null;
        }

        $latitude = (float) $rawLat;
        $longitude = (float) $rawLng;

        if ($latitude < -90.0 || $latitude > 90.0 || $longitude < -180.0 || $longitude > 180.0) {
            return null;
        }

        $timezone = (string) (Setting::get('school_timezone', self::DEFAULT_TIMEZONE) ?: self::DEFAULT_TIMEZONE);
        $location = Setting::get('school_city') ?: Setting::get('school_name');

        return $this->calculateForCoordinates($latitude, $longitude, $date, $timezone, is_string($location) ? $location : null);
    }

    /**
     * @return array{
     *     date: string,
     *     timezone: string,
     *     location: string|null,
     *     coordinates: array{latitude: float, longitude: float},
     *     times: array{subuh: string, terbit: string, dzuhur: string, ashar: string, maghrib: string, isya: string},
     *     iso_times: array{subuh: string, terbit: string, dzuhur: string, ashar: string, maghrib: string, isya: string}
     * }
     */
    public function calculateForCoordinates(
        float $latitude,
        float $longitude,
        ?string $date = null,
        string $timezone = self::DEFAULT_TIMEZONE,
        ?string $location = null,
    ): array {
        $tz = new DateTimeZone($timezone);
        $day = $date !== null
            ? CarbonImmutable::parse($date, $tz)->startOfDay()
            : CarbonImmutable::now($tz)->startOfDay();

        $tzOffsetHours = $tz->getOffset($day) / 3600.0;

        $jd = $this->julianDate((int) $day->year, (int) $day->month, (int) $day->day) - ($longitude / (15.0 * 24.0));
        [$declination, $equationOfTime] = $this->sunPosition($jd + 0.5);

        $transit = 12.0 + $tzOffsetHours - ($longitude / 15.0) - $equationOfTime;

        $fajrOffset = $this->hourAngle($latitude, $declination, -20.0);
        $sunriseOffset = $this->hourAngle($latitude, $declination, -0.833);
        $asrAltitude = rad2deg(acot(1.0 + tan(deg2rad(abs($latitude - $declination)))));
        $asrOffset = $this->hourAngle($latitude, $declination, $asrAltitude);
        $ishaOffset = $this->hourAngle($latitude, $declination, -18.0);

        $ihtiyatHours = 2.0 / 60.0;

        $rawHours = [
            'subuh' => $transit - $fajrOffset + $ihtiyatHours,
            'terbit' => $transit - $sunriseOffset - $ihtiyatHours,
            'dzuhur' => $transit + $ihtiyatHours,
            'ashar' => $transit + $asrOffset + $ihtiyatHours,
            'maghrib' => $transit + $sunriseOffset + $ihtiyatHours,
            'isya' => $transit + $ishaOffset + $ihtiyatHours,
        ];

        $labels = [
            'subuh' => 'Subuh',
            'terbit' => 'Terbit',
            'dzuhur' => 'Dzuhur',
            'ashar' => 'Ashar',
            'maghrib' => 'Maghrib',
            'isya' => 'Isya',
        ];

        $times = [];
        $isoTimes = [];
        $prayers = [];
        foreach ($rawHours as $name => $hours) {
            $totalMinutes = (int) ceil($hours * 60.0);
            $moment = $day->addMinutes($totalMinutes);
            $formatted = $moment->format('H:i');
            $times[$name] = $formatted;
            $isoTimes[$name] = $moment->toIso8601String();
            $prayers[] = [
                'key' => $name,
                'label' => $labels[$name],
                'time' => $formatted,
            ];
        }

        return [
            'date' => $day->format('Y-m-d'),
            'timezone' => $timezone,
            'location' => $location,
            'method' => 'Kemenag RI',
            'coordinates' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ],
            'times' => $times,
            'iso_times' => $isoTimes,
            'prayers' => $prayers,
        ];
    }

    private function julianDate(int $year, int $month, int $day): float
    {
        if ($month <= 2) {
            $year -= 1;
            $month += 12;
        }
        $a = floor($year / 100);
        $b = 2 - $a + floor($a / 4);

        return floor(365.25 * ($year + 4716))
            + floor(30.6001 * ($month + 1))
            + $day + $b - 1524.5;
    }

    /**
     * @return array{0: float, 1: float} [declination in degrees, equation of time in hours]
     */
    private function sunPosition(float $jd): array
    {
        $d = $jd - 2451545.0;
        $g = $this->normalizeDegrees(357.529 + 0.98560028 * $d);
        $q = $this->normalizeDegrees(280.459 + 0.98564736 * $d);
        $l = $this->normalizeDegrees($q + 1.915 * sin(deg2rad($g)) + 0.020 * sin(deg2rad(2.0 * $g)));

        $e = 23.439 - 0.00000036 * $d;
        $ra = rad2deg(atan2(cos(deg2rad($e)) * sin(deg2rad($l)), cos(deg2rad($l)))) / 15.0;
        $ra = $this->normalizeHours($ra);

        $declination = rad2deg(asin(sin(deg2rad($e)) * sin(deg2rad($l))));
        $eqt = ($q / 15.0) - $ra;
        if ($eqt > 12.0) {
            $eqt -= 24.0;
        } elseif ($eqt < -12.0) {
            $eqt += 24.0;
        }

        return [$declination, $eqt];
    }

    private function hourAngle(float $latitude, float $declination, float $altitude): float
    {
        $latRad = deg2rad($latitude);
        $decRad = deg2rad($declination);
        $altRad = deg2rad($altitude);

        $cosH = (sin($altRad) - sin($latRad) * sin($decRad)) / (cos($latRad) * cos($decRad));
        $cosH = max(-1.0, min(1.0, $cosH));

        return rad2deg(acos($cosH)) / 15.0;
    }

    private function normalizeDegrees(float $angle): float
    {
        $angle = fmod($angle, 360.0);

        return $angle < 0 ? $angle + 360.0 : $angle;
    }

    private function normalizeHours(float $hours): float
    {
        $hours = fmod($hours, 24.0);

        return $hours < 0 ? $hours + 24.0 : $hours;
    }
}

if (! function_exists(__NAMESPACE__.'\\acot')) {
    function acot(float $x): float
    {
        return atan(1.0 / $x);
    }
}
