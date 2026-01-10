<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CacheController extends Controller
{
    /**
     * Clear all caches (config, cache, route, view).
     * 
     * Access via: /clear-cache?token=YOUR_SECRET_TOKEN
     * 
     * Set CACHE_CLEAR_TOKEN in your .env file for security.
     * After use, change or remove the token.
     */
    public function clear(Request $request)
    {
        $token = $request->query('token');
        $expectedToken = env('CACHE_CLEAR_TOKEN', 'change-me-in-production');

        // Security check
        if (empty($token) || $token !== $expectedToken) {
            return response()->json([
                'error' => 'Invalid token. Set CACHE_CLEAR_TOKEN in .env and use ?token=YOUR_TOKEN',
            ], 403);
        }

        try {
            $results = [];

            // Clear config cache
            Artisan::call('config:clear');
            $results['config'] = 'cleared';

            // Clear application cache
            Artisan::call('cache:clear');
            $results['cache'] = 'cleared';

            // Clear route cache
            try {
                Artisan::call('route:clear');
                $results['route'] = 'cleared';
            } catch (\Exception $e) {
                $results['route'] = 'skipped (not cached)';
            }

            // Clear view cache
            try {
                Artisan::call('view:clear');
                $results['view'] = 'cleared';
            } catch (\Exception $e) {
                $results['view'] = 'skipped';
            }

            // Clear compiled classes
            try {
                Artisan::call('clear-compiled');
                $results['compiled'] = 'cleared';
            } catch (\Exception $e) {
                $results['compiled'] = 'skipped';
            }

            // Get current environment
            $env = config('app.env');
            $debug = config('app.debug') ? 'enabled' : 'disabled';

            return response()->json([
                'success' => true,
                'message' => 'All caches cleared successfully!',
                'environment' => $env,
                'debug_mode' => $debug,
                'results' => $results,
                'note' => 'For security, change CACHE_CLEAR_TOKEN in .env after use.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to clear cache',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

