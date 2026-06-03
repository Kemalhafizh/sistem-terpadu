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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = null;

        if (auth()->check()) {
            $locale = auth()->user()->preferred_locale;
        }

        if (!$locale) {
            $locale = session('locale', $request->hasSession() ? session('locale', config('app.locale', 'id')) : config('app.locale', 'id'));
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
