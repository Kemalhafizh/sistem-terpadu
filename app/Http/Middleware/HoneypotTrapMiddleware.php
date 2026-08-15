<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class HoneypotTrapMiddleware
{
  /**
   * List of canary / honeypot URI patterns that indicate malicious automated scanning.
   */
  protected array $canaryPatterns = [
    '^\.env',
    '^\.git',
    '^\.aws',
    'phpmyadmin',
    'wp-login\.php',
    'wp-admin',
    'admin/login\.php',
    'eval-stdin\.php',
    'actuator/health',
    'api/v1/debug',
    'config\.php\.bak',
    'database\.sqlite',
    'dump\.sql',
  ];

  /**
   * Handle an incoming request.
   *
   * @param \Illuminate\Http\Request $request
   * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    $clientIp = $request->ip();
    $banKey = 'banned_ip_' . md5($clientIp);

    // Check if IP is currently banned in Cache
    if (Cache::has($banKey)) {
      $incidentId = Cache::get($banKey)['incident_id'] ?? 'SEC-SA1-BAN-' . strtoupper(Str::random(6));
      return $this->renderInterceptNotice($request, $clientIp, $incidentId, 'IP Blacklisted due to prior malicious scanning activity.');
    }

    $path = ltrim($request->path(), '/');

    // Check if path matches any canary honeypot pattern
    foreach ($this->canaryPatterns as $pattern) {
      if (preg_match('#' . $pattern . '#i', $path)) {
        $incidentId = 'SEC-SA1-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        
        // Lock down IP in cache for 30 days
        Cache::put($banKey, [
          'ip' => $clientIp,
          'incident_id' => $incidentId,
          'triggered_pattern' => $pattern,
          'user_agent' => $request->userAgent(),
          'banned_at' => now()->toDateTimeString(),
        ], now()->addDays(30));

        // Log threat incident to security SIEM log
        Log::channel('siem')->warning("CYBER THREAT TRAPPED: Honeypot Triggered", [
          'incident_id' => $incidentId,
          'client_ip' => $clientIp,
          'path' => $path,
          'pattern' => $pattern,
          'user_agent' => $request->userAgent(),
        ]);

        // Tarpit delay to frustrate automated scanners (unless in testing environment)
        if (!app()->environment('testing')) {
          sleep(2);
        }

        return $this->renderInterceptNotice($request, $clientIp, $incidentId, 'Canary Trap Triggered: Attempted access to decoy route.');
      }
    }

    return $next($request);
  }

  /**
   * Render the intimidating Threat Interception Response Notice.
   */
  protected function renderInterceptNotice(Request $request, string $ip, string $incidentId, string $reason): Response
  {
    if ($request->expectsJson()) {
      return response()->json([
        'status' => 'TERMINATED',
        'error' => 'CYBER THREAT INTERCEPTION NOTICE',
        'message' => 'Unauthorized automated scanning activity detected.',
        'incident_id' => $incidentId,
        'ip' => $ip,
        'notice' => 'Your device signature and network location have been logged to SISTA Cyber Operations Command.',
      ], 403);
    }

    if (view()->exists('errors.security-intercept')) {
      return response()->view('errors.security-intercept', [
        'ip' => $ip,
        'incidentId' => $incidentId,
        'reason' => $reason,
        'userAgent' => $request->userAgent(),
      ], 403);
    }

    return response("<h1> SECURITY ALERT: ACCESS TERMINATED</h1><p>Incident ID: {$incidentId}</p><p>IP: {$ip}</p>", 403);
  }
}
