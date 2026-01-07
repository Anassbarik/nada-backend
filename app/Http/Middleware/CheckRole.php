<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        if (auth()->user()->role !== $role) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthorized. Required role: ' . $role
                ], 403);
            }
            
            // For web requests, logout and redirect with error message
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')->withErrors([
                'email' => __('Vous n\'avez pas les permissions nécessaires pour accéder à cette page. Seuls les administrateurs peuvent y accéder.'),
            ]);
        }

        return $next($request);
    }
}
