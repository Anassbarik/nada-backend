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

        $user = auth()->user();
        
        // Allow both admin and super-admin roles
        if ($role === 'admin' && !in_array($user->role, ['admin', 'super-admin'])) {
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
        
        // For super-admin role, only super-admin can access
        if ($role === 'super-admin' && $user->role !== 'super-admin') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthorized. Required role: ' . $role
                ], 403);
            }
            
            return redirect()->route('dashboard')->withErrors([
                'error' => __('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.'),
            ]);
        }

        // For organizer role, only organizer can access
        if ($role === 'organizer' && $user->role !== 'organizer') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthorized. Required role: ' . $role
                ], 403);
            }
            
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')->withErrors([
                'email' => __('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.'),
            ]);
        }

        return $next($request);
    }
}
