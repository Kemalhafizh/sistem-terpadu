<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = null;

        if (auth()->check() && ! empty(auth()->user()->preferred_locale)) {
            $locale = auth()->user()->preferred_locale;
        }

        if (! $locale && $request->hasSession()) {
            $locale = session('locale');
        }

        if (! $locale || ! in_array($locale, ['id', 'en', 'ar'])) {
            $locale = config('app.locale', 'id');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
