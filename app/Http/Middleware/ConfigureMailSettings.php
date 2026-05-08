<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\Helper;

class ConfigureMailSettings
{
    public function handle(Request $request, Closure $next)
    {
        Helper::configureMailSettings();
        return $next($request);
    }
}