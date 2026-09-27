<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminIpWhitelistMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // IP Whitelist konfigurasi (env ADMIN_ALLOWED_IPS → config/app.php)
        $allowedIpsConfig = (string) config('app.admin_allowed_ips', '127.0.0.1,::1');
        $allowedIps = array_filter(array_map('trim', explode(',', $allowedIpsConfig)));

        // Izinkan semua akses jika di environment testing atau local pengembangan jika diizinkan
        if (app()->environment('testing')) {
            return $next($request);
        }

        $clientIp = $request->ip();

        if (! in_array($clientIp, $allowedIps) && ! in_array('*', $allowedIps)) {
            abort(403, 'Akses ditolak. Alamat IP Anda ('.$clientIp.') tidak terdaftar dalam whitelist area administratif.');
        }

        return $next($request);
    }
}
