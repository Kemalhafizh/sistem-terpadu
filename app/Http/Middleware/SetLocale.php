<?php

namespace App\Http\Middleware;

use App\Support\Locale;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Picks the language of this request, the same way for web and API:
 * 1. the signed-in user's saved choice (users.preferred_locale), so a
 *    language picked on the web shows in the app and the other way round;
 * 2. the session (a guest who picked a language on the web);
 * 3. for the API only, the Accept-Language header the app sends (login,
 *    SPMB and other screens used before signing in);
 * 4. config('app.fallback_locale').
 */
class SetLocale
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user() ?? ($request->bearerToken() ? $request->user('sanctum') : null);

        $candidates = [
            $user?->preferred_locale,
            $request->hasSession() ? $request->session()->get('locale') : null,
            $request->hasSession() ? null : Locale::fromAcceptLanguage($request->header('Accept-Language')),
        ];

        // app.fallback_locale, not app.locale: setLocale() rewrites app.locale,
        // so in a long-lived process the previous request's language would leak.
        $locale = collect($candidates)->first(fn ($candidate) => Locale::isSupported($candidate))
            ?? config('app.fallback_locale', 'id');

        app()->setLocale($locale);

        $response = $next($request);
        $response->headers->set('Content-Language', $locale);

        return $response;
    }
}
