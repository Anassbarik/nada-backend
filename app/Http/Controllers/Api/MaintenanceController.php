<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MaintenanceController extends Controller
{
    /**
     * Get maintenance mode status.
     * Route: GET /api/maintenance
     */
    public function index()
    {
        // Get maintenance status from cache (defaults to false if not set)
        $home = Cache::get('maintenance.home', false);
        $global = Cache::get('maintenance.global', false);

        return response()->json([
            'success' => true,
            'data' => [
                'home' => (bool) $home,
                'global' => (bool) $global,
            ],
        ]);
    }
}

