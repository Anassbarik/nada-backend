<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only apply CSP in production or when explicitly enabled
        if (!config('app.csp_enabled', false)) {
            return $response;
        }

        $csp = $this->buildCSPDirective($request);
        
        $response->headers->set('Content-Security-Policy', $csp);
        
        // Also set report-only mode for testing (optional)
        if (config('app.csp_report_only', false)) {
            $response->headers->set('Content-Security-Policy-Report-Only', $csp);
            $response->headers->remove('Content-Security-Policy');
        }

        return $response;
    }

    /**
     * Build CSP directive based on environment
     */
    private function buildCSPDirective(Request $request): string
    {
        $isDev = app()->environment('local', 'development');
        $appUrl = config('app.url');
        $vitePort = env('VITE_PORT', 5173); // Default Vite port
        
        // Base directives
        $directives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'", // Required for Alpine.js, Livewire, and Vite HMR
            "style-src 'self' 'unsafe-inline'", // Required for Tailwind CSS and inline styles
            "font-src 'self' data: https://fonts.bunny.net",
            "img-src 'self' data: https: blob:", // Allow images from any HTTPS source
            "connect-src 'self'", // API calls
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
        ];

        // Development mode: Allow Vite dev server
        if ($isDev) {
            $viteHost = parse_url($appUrl, PHP_URL_HOST) ?: 'localhost';
            $directives[1] = "script-src 'self' 'unsafe-inline' 'unsafe-eval' http://{$viteHost}:{$vitePort} ws://{$viteHost}:{$vitePort}";
            $directives[2] = "style-src 'self' 'unsafe-inline' http://{$viteHost}:{$vitePort}";
            $directives[5] = "connect-src 'self' http://{$viteHost}:{$vitePort} ws://{$viteHost}:{$vitePort}";
        } else {
            // Production: Force HTTPS
            $directives[] = "upgrade-insecure-requests";
        }

        // Allow external scripts (e.g., Lucide icons from unpkg.com)
        $directives[1] .= ' https://unpkg.com';
        
        // Allow external stylesheets (fonts)
        $directives[2] .= ' https://fonts.bunny.net';

        // Sanctum-specific: Allow cookie-based authentication
        // Ensure connect-src allows your API domains with proper protocol formatting
        $sanctumDomains = config('sanctum.stateful', []);
        if (!empty($sanctumDomains) && is_array($sanctumDomains)) {
            $sanctumUrls = [];
            foreach ($sanctumDomains as $domain) {
                $domain = trim($domain);
                if (empty($domain)) continue;
                
                // If domain doesn't have protocol, add https:// (or http:// for localhost)
                if (!preg_match('/^https?:\/\//', $domain)) {
                    if (strpos($domain, 'localhost') !== false || strpos($domain, '127.0.0.1') !== false) {
                        $domain = 'http://' . $domain;
                    } else {
                        $domain = 'https://' . $domain;
                    }
                }
                $sanctumUrls[] = $domain;
            }
            if (!empty($sanctumUrls)) {
                $directives[5] .= ' ' . implode(' ', $sanctumUrls);
            }
        }

        // Add nonce support for inline scripts (more secure than 'unsafe-inline')
        // This would require generating nonces and updating views
        // For now, we use 'unsafe-inline' which is acceptable for admin panels with proper authentication

        return implode('; ', $directives);
    }
}
