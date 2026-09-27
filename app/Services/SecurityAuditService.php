<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;

class SecurityAuditService
{
    /**
     * Run the full security hardening audit suite.
     */
    public function runFullAudit(): array
    {
        $env = $this->auditEnvironmentSecurity();
        $session = $this->auditSessionSecurity();
        $routes = $this->auditRouteSecurity();
        $rateLimits = $this->auditRateLimiting();
        $password = $this->auditPasswordPolicy();

        // Calculate a compliance score (percentage of passed checks)
        $totalChecks = 0;
        $passedChecks = 0;

        foreach ([$env, $session, $password] as $category) {
            foreach ($category as $check) {
                $totalChecks++;
                if ($check['passed']) {
                    $passedChecks++;
                }
            }
        }

        // Add 1 check for vulnerable routes (pass if empty)
        $totalChecks++;
        if (empty($routes['vulnerable'])) {
            $passedChecks++;
        }

        // Add 1 check for login rate limit (pass if enabled)
        $totalChecks++;
        if ($rateLimits['login_throttled']) {
            $passedChecks++;
        }

        $complianceScore = $totalChecks > 0 ? round(($passedChecks / $totalChecks) * 100) : 0;

        return [
            'compliance_score' => $complianceScore,
            'environment' => $env,
            'session' => $session,
            'routes' => $routes,
            'rate_limiting' => $rateLimits,
            'password' => $password,
        ];
    }

    /**
     * Check environment variables security.
     */
    public function auditEnvironmentSecurity(): array
    {
        return [
            'app_debug' => [
                'name' => 'APP_DEBUG Nonaktif',
                'description' => 'Mencegah kebocoran stack trace error detail ke publik.',
                'passed' => config('app.debug') === false,
                'value' => config('app.debug') ? 'Aktif (Rentan)' : 'Nonaktif (Aman)',
            ],
            'app_env' => [
                'name' => 'Environment Produksi',
                'description' => 'Memastikan aplikasi berjalan dalam mode aman non-local.',
                'passed' => config('app.env') !== 'local',
                'value' => config('app.env') ?: 'Not Set',
            ],
            'app_key' => [
                'name' => 'Application Key Terpasang',
                'description' => 'Digunakan untuk mengenkripsi cookie dan data sensitif.',
                'passed' => ! empty(config('app.key')) && str_contains(config('app.key'), 'base64:'),
                'value' => ! empty(config('app.key')) ? 'Terpasang (Aman)' : 'Kosong (Berbahaya)',
            ],
        ];
    }

    /**
     * Check session cookies configuration security.
     */
    public function auditSessionSecurity(): array
    {
        return [
            'session_encrypt' => [
                'name' => 'Enkripsi Sesi Aktif',
                'description' => 'Menghindari pembacaan data sesi secara langsung oleh pihak luar.',
                'passed' => config('session.encrypt') === true,
                'value' => config('session.encrypt') ? 'Aktif (Aman)' : 'Nonaktif (Rentan)',
            ],
            'session_secure' => [
                'name' => 'Secure Cookie HTTPS Only',
                'description' => 'Cookie sesi hanya dikirim melalui jalur terenkripsi HTTPS.',
                'passed' => config('session.secure') === true || app()->environment('local'),
                'value' => config('session.secure') ? 'Aktif (Aman)' : 'Nonaktif (Rentan - Diabaikan di Local)',
            ],
            'session_http_only' => [
                'name' => 'HttpOnly Cookie',
                'description' => 'Mencegah JavaScript membaca cookie sesi (perlindungan XSS/Session Stealing).',
                'passed' => config('session.http_only') === true,
                'value' => config('session.http_only') ? 'Aktif (Aman)' : 'Nonaktif (Rentan)',
            ],
            'session_same_site' => [
                'name' => 'SameSite Lax/Strict',
                'description' => 'Mencegah serangan Cross-Site Request Forgery (CSRF).',
                'passed' => in_array(config('session.same_site'), ['lax', 'strict']),
                'value' => config('session.same_site') ?: 'None (Rentan)',
            ],
        ];
    }

    /**
     * Audit routes access controls, checking for unprotected sensitive patterns.
     */
    public function auditRouteSecurity(): array
    {
        $routes = Route::getRoutes();
        $vulnerable = [];
        $totalRoutes = 0;

        foreach ($routes as $route) {
            $uri = $route->uri();
            $middlewares = $route->middleware();
            $totalRoutes++;

            // Identify sensitive modules
            $isSensitive = str_contains($uri, 'admin') ||
                           str_contains($uri, 'developer') ||
                           str_contains($uri, 'compliance') ||
                           str_contains($uri, 'finance') ||
                           str_contains($uri, 'hrd');

            // Skip public auth pages
            if ($uri === '/' || str_contains($uri, 'login') || str_contains($uri, 'logout') || str_contains($uri, 'password') || str_contains($uri, 'preferences')) {
                continue;
            }

            $hasAuth = false;
            foreach ($middlewares as $mw) {
                if (str_contains($mw, 'auth') || str_contains($mw, 'api:auth') || str_contains($mw, 'sanctum')) {
                    $hasAuth = true;
                    break;
                }
            }

            if ($isSensitive && ! $hasAuth) {
                $vulnerable[] = [
                    'uri' => '/'.ltrim($uri, '/'),
                    'method' => implode('|', $route->methods()),
                    'middlewares' => implode(', ', $middlewares) ?: 'None',
                ];
            }
        }

        return [
            'total_routes' => $totalRoutes,
            'vulnerable' => $vulnerable,
            'passed' => empty($vulnerable),
        ];
    }

    /**
     * Check if rate limit controls are configured on the login POST route.
     */
    public function auditRateLimiting(): array
    {
        $routes = Route::getRoutes();
        $loginThrottled = false;

        foreach ($routes as $route) {
            $uri = $route->uri();
            if (str_contains($uri, 'login') && in_array('POST', $route->methods())) {
                $middlewares = $route->middleware();
                foreach ($middlewares as $mw) {
                    if (str_contains($mw, 'throttle') || $mw === 'rate_limit:login') {
                        $loginThrottled = true;
                        break;
                    }
                }
            }
        }

        return [
            'login_throttled' => $loginThrottled,
            'value' => $loginThrottled ? 'Aktif (Aman)' : 'Nonaktif (Rentan Brute-Force)',
        ];
    }

    /**
     * Check global password strength rules.
     */
    public function auditPasswordPolicy(): array
    {
        $min = config('auth.password_rules.min_length', 8);
        $upper = config('auth.password_rules.require_uppercase', true);
        $num = config('auth.password_rules.require_number', true);

        return [
            'min_length' => [
                'name' => 'Panjang Minimum Kata Sandi',
                'description' => 'Password harus memiliki panjang minimal 8 karakter.',
                'passed' => $min >= 8,
                'value' => "{$min} Karakter",
            ],
            'require_uppercase' => [
                'name' => 'Wajib Huruf Besar & Kecil',
                'description' => 'Password wajib mengombinasikan huruf kapital dan huruf kecil.',
                'passed' => $upper === true,
                'value' => $upper ? 'Wajib (Aman)' : 'Opsional (Rentan)',
            ],
            'require_number' => [
                'name' => 'Wajib Mengandung Angka',
                'description' => 'Password wajib mengombinasikan angka (0-9).',
                'passed' => $num === true,
                'value' => $num ? 'Wajib (Aman)' : 'Opsional (Rentan)',
            ],
        ];
    }
}
