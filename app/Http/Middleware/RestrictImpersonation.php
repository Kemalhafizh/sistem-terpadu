<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictImpersonation
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->has('impersonate_by')) {
            // Check for timeout (e.g. 60 minutes)
            $startTime = session('impersonate_start_time');
            if ($startTime && now()->diffInMinutes($startTime) > 60) {
                return redirect()->route('admin.impersonate.leave')
                    ->with('error', 'Sesi impersonate Anda telah habis (timeout 60 menit).');
            }

            $restrictedRoutes = [
                'profile.edit',
                'profile.update',
                'profile.destroy',
                'password.update',
                'users.destroy',
                'users.update',
                'users.store',
                'admin.logs', // Don't let them clear or view logs as someone else
                // Add more sensitive routes here
            ];

            $restrictedPrefixes = [
                'admin.webhooks.',
                'admin.integrations.',
                'treasury.',
                'finance.',
            ];

            $currentRouteName = $request->route() ? $request->route()->getName() : null;

            if ($currentRouteName) {
                if (in_array($currentRouteName, $restrictedRoutes)) {
                    abort(403, 'Aksi ini tidak diizinkan saat sedang dalam mode menyamar (impersonate).');
                }

                foreach ($restrictedPrefixes as $prefix) {
                    if (str_starts_with($currentRouteName, $prefix) && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                        abort(403, 'Aksi modifikasi data sensitif/finansial tidak diizinkan dalam mode impersonate.');
                    }
                }
            }
        }

        return $next($request);
    }
}
