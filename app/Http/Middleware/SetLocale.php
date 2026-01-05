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
        // Skip for Livewire/AJAX requests to avoid interference
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Livewire')) {
            // Still set locale from session for consistency
            $sessionLocale = session('locale', config('app.locale'));
            if (in_array($sessionLocale, config('app.available_locales', ['en', 'fr']))) {
                app()->setLocale($sessionLocale);
            }
            return $next($request);
        }

        $availableLocales = config('app.available_locales', ['en', 'fr']);
        $currentLocale = app()->getLocale();
        $sessionLocale = session('locale', config('app.locale'));
        
        // Get locale from query parameter or session
        $locale = $request->query('lang', $sessionLocale);
        
        // Validate locale
        if (!in_array($locale, $availableLocales)) {
            $locale = $sessionLocale;
        }
        
        // Set locale
        if ($locale !== $currentLocale) {
            app()->setLocale($locale);
        }
        
        // Only write to session if locale actually changed (prevents unnecessary session writes)
        if ($locale !== $sessionLocale) {
            session(['locale' => $locale]);
        }
        
        return $next($request);
    }
}
