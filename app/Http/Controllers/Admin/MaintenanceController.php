<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MaintenanceController extends Controller
{
    /**
     * Toggle maintenance mode.
     */
    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:home,global',
            'enabled' => 'required|boolean',
        ]);

        $cacheKey = 'maintenance.' . $validated['type'];
        
        if ($validated['enabled']) {
            Cache::forever($cacheKey, true);
        } else {
            Cache::forget($cacheKey);
        }

        $message = $validated['type'] === 'home' 
            ? ($validated['enabled'] ? 'Maintenance mode (Home) activé.' : 'Maintenance mode (Home) désactivé.')
            : ($validated['enabled'] ? 'Maintenance mode (Global) activé.' : 'Maintenance mode (Global) désactivé.');

        return redirect()->route('dashboard')->with('success', $message);
    }

    /**
     * Get maintenance status for dashboard.
     */
    public function status()
    {
        return response()->json([
            'home' => Cache::get('maintenance.home', false),
            'global' => Cache::get('maintenance.global', false),
        ]);
    }
}

