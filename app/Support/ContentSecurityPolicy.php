<?php

namespace App\Support;

/**
 * The Content-Security-Policy sent on every response (SecurityHeaders).
 *
 * Third-party browser libraries are served from our own build
 * (VendorAsset), so scripts and styles need no CDN hosts. The only external
 * origins left are the ones that can't be self-hosted:
 *  - Google Fonts (stylesheets + font files),
 *  - Midtrans Snap (script, payment iframe and its API), for the environment
 *    set by services.midtrans.is_production,
 *  - the Reverb WebSocket server, when it runs on another host or port,
 *  - the Google Maps embed on the public contact page,
 *  - the Vite dev server, only while `npm run dev` runs.
 */
class ContentSecurityPolicy
{
    public static function header(): string
    {
        $midtrans = [self::midtransAppHost(), self::midtransApiHost()];
        $viteDev = self::viteDevServerOrigins();

        $directives = [
            'default-src' => ["'self'"],
            // 'unsafe-eval': Alpine evaluates x-data/x-on expressions with new Function().
            'script-src' => ["'self'", "'unsafe-inline'", "'unsafe-eval'", ...$midtrans, ...$viteDev],
            'style-src' => ["'self'", "'unsafe-inline'", 'https://fonts.googleapis.com', ...$viteDev],
            // data: FullCalendar embeds its icon font in the CSS it injects.
            'font-src' => ["'self'", 'data:', 'https://fonts.gstatic.com', ...$viteDev],
            'img-src' => ["'self'", 'data:', 'https:'],
            'frame-src' => ["'self'", self::midtransAppHost(), 'https://www.google.com'],
            'connect-src' => ["'self'", ...self::websocketOrigins(), ...$midtrans, ...$viteDev],
            // Directives that don't fall back to default-src (ZAP 10055): no
            // <base> hijacking, forms only post back to this app, the app
            // can't be framed (same as X-Frame-Options: DENY), no plugins.
            'base-uri' => ["'self'"],
            'form-action' => ["'self'"],
            'frame-ancestors' => ["'none'"],
            'object-src' => ["'none'"],
        ];

        return collect($directives)
            ->map(fn (array $sources, string $name) => $name.' '.implode(' ', array_unique($sources)))
            ->implode('; ').';';
    }

    public static function midtransAppHost(): string
    {
        return self::midtransIsProduction() ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com';
    }

    public static function midtransApiHost(): string
    {
        return self::midtransIsProduction() ? 'https://api.midtrans.com' : 'https://api.sandbox.midtrans.com';
    }

    /**
     * The origin the browser's Echo client connects to. It's built from the
     * VITE_REVERB_* values, which .env.example sets to the REVERB_* ones read
     * here. 'self' already covers a WebSocket on the page's own host and port.
     *
     * @return list<string>
     */
    public static function websocketOrigins(): array
    {
        if (config('broadcasting.default') !== 'reverb') {
            return [];
        }

        $options = config('broadcasting.connections.reverb.options', []);
        $host = $options['host'] ?? null;

        if (! is_string($host) || $host === '') {
            return [];
        }

        $secure = ($options['scheme'] ?? 'https') === 'https';
        $port = (int) ($options['port'] ?? ($secure ? 443 : 80));
        $defaultPort = $secure ? 443 : 80;

        return [($secure ? 'wss://' : 'ws://').$host.($port === $defaultPort ? '' : ':'.$port)];
    }

    /**
     * While `npm run dev` runs, public/hot holds the Vite dev server URL and
     * assets (plus the HMR socket) come from there. The file only exists on a
     * developer machine; production builds never create it.
     *
     * @return list<string>
     */
    public static function viteDevServerOrigins(): array
    {
        $hotFile = public_path('hot');

        if (! is_file($hotFile)) {
            return [];
        }

        $url = parse_url(trim((string) file_get_contents($hotFile)));

        if (! isset($url['scheme'], $url['host'])) {
            return [];
        }

        $origin = $url['host'].(isset($url['port']) ? ':'.$url['port'] : '');
        $secure = $url['scheme'] === 'https';

        return [($secure ? 'https://' : 'http://').$origin, ($secure ? 'wss://' : 'ws://').$origin];
    }

    private static function midtransIsProduction(): bool
    {
        return filter_var(config('services.midtrans.is_production', false), FILTER_VALIDATE_BOOLEAN);
    }
}
