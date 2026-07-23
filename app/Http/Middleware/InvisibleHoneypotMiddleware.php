<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class InvisibleHoneypotMiddleware
{
    /**
     * Honeypot field name used across public forms.
     */
    public const HONEYPOT_FIELD = '_hp_verification_name';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only evaluate form submission methods
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            if ($request->has(self::HONEYPOT_FIELD) && !empty($request->input(self::HONEYPOT_FIELD))) {
                $clientIp = $request->ip();
                $incidentId = 'SEC-SA1-BOT-' . date('Ymd') . '-' . strtoupper(Str::random(6));
                $banKey = 'banned_ip_' . md5($clientIp);

                // Auto ban bot IP for 30 days
                Cache::put($banKey, [
                    'ip' => $clientIp,
                    'incident_id' => $incidentId,
                    'triggered_pattern' => 'Invisible Form Honeypot Field Filled',
                    'user_agent' => $request->userAgent(),
                    'banned_at' => now()->toDateTimeString(),
                ], now()->addDays(30));

                Log::channel('siem')->warning("AUTOMATED BOT TRAPPED: Invisible Form Field Filled", [
                    'incident_id' => $incidentId,
                    'client_ip' => $clientIp,
                    'user_agent' => $request->userAgent(),
                    'form_url' => $request->fullUrl(),
                ]);

                if (!app()->environment('testing')) {
                    sleep(2);
                }

                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => 'TERMINATED',
                        'error' => 'AUTOMATED BOT DETECTED',
                        'incident_id' => $incidentId,
                    ], 403);
                }

                return response()->view('errors.security-intercept', [
                    'ip' => $clientIp,
                    'incidentId' => $incidentId,
                    'reason' => 'Automated Form Bot Detected (Honeypot Decoy Field Filled)',
                    'userAgent' => $request->userAgent(),
                ], 403);
            }
        }

        return $next($request);
    }
}
