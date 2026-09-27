<?php

namespace App\Services\Security;

class SsrfGuardService
{
    /**
     * Validate a target URL to ensure it is safe from Server-Side Request Forgery (SSRF).
     *
     * @throws \InvalidArgumentException
     */
    public static function validateUrl(string $url): string
    {
        $parts = parse_url($url);

        if (! isset($parts['scheme']) || ! in_array(strtolower($parts['scheme']), ['http', 'https'])) {
            throw new \InvalidArgumentException('Protokol URL tidak diizinkan. Hanya http dan https yang diperbolehkan.');
        }

        $host = $parts['host'] ?? '';
        if (empty($host)) {
            throw new \InvalidArgumentException('Host URL tidak valid.');
        }

        $normalizedHost = strtolower($host);

        // Always block AWS / GCP / Azure cloud metadata IP & internal domain
        if ($normalizedHost === '169.254.169.254' || $normalizedHost === 'metadata.google.internal') {
            throw new \InvalidArgumentException('Akses ke instance cloud metadata dilarang.');
        }

        // Loopback protection (allow only in local/testing for integration tests)
        if ($normalizedHost === 'localhost' || $normalizedHost === '127.0.0.1' || $normalizedHost === '::1') {
            if (! app()->environment('testing', 'local')) {
                throw new \InvalidArgumentException('Akses ke loopback/localhost dilarang.');
            }
        }

        // Resolve DNS and check IP address against private/reserved ranges
        if (! app()->environment('testing', 'local')) {
            $ips = @gethostbynamel($host);
            if ($ips === false || empty($ips)) {
                throw new \InvalidArgumentException('Domain tujuan tidak dapat diselesaikan via DNS.');
            }

            foreach ($ips as $ip) {
                if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    throw new \InvalidArgumentException("IP tujuan [{$ip}] berada di jaringan internal/privat yang diblokir.");
                }
            }
        }

        // Port restrictions
        if (isset($parts['port'])) {
            $port = (int) $parts['port'];
            $allowedPorts = [80, 443, 8080, 8443];
            if (! in_array($port, $allowedPorts) && ! app()->environment('testing', 'local')) {
                throw new \InvalidArgumentException("Port [{$port}] tidak diizinkan untuk transmisi webhook.");
            }
        }

        return $url;
    }
}
