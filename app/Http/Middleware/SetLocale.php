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
        // Check URL parameter first
        if ($request->has('locale') && in_array($request->get('locale'), config('app.available_locales', ['en']))) {
            $locale = $request->get('locale');
            session(['locale' => $locale]);
            // Set cookie for guest users
            cookie()->queue('locale', $locale, 60 * 24 * 30); // 30 days
        } else {
            // Check session first, then cookie, then default
            $locale = session('locale') ?? $request->cookie('locale') ?? config('app.locale');
            
            // Validate locale
            if (!in_array($locale, config('app.available_locales', ['en']))) {
                $locale = config('app.locale');
            }
            
            session(['locale' => $locale]);
        }
        
        \Illuminate\Support\Facades\App::setLocale($locale);
        
        return $next($request);
    }
}
