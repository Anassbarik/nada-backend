<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    /**
     * Display a listing of active partners for frontend marquee.
     * GET /api/partners
     */
    public function apiIndex()
    {
        $partners = Partner::where('active', true)
            ->orderBy('sort_order')
            ->orderBy('created_at', 'asc')
            ->get(['id', 'name', 'logo_path', 'url']);

        // Format response with full logo URLs (using model accessor)
        $partners = $partners->map(function ($partner) {
            return [
                'id' => $partner->id,
                'name' => $partner->name,
                'logo_url' => $partner->logo_url,
                'logo_path' => $partner->logo_path, // Keep for backward compatibility
                'url' => $partner->url,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $partners,
        ]);
    }
}
