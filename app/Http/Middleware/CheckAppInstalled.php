<?php

namespace App\Http\Middleware;

use Closure;

class CheckAppInstalled
{
    public function handle($request, Closure $next)
    {
        // Skip installation check for installer routes
        if ($request->is('install*')) {
            return $next($request);
        }

        // Check if app is installed
        if (!file_exists(storage_path('installed'))) {
            return redirect(url('/install'));
        }

        return $next($request);
    }
}
