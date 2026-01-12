<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class AirportController extends Controller
{
    /**
     * Display a listing of airports for an event.
     * Route: GET /api/events/{event:slug}/airports
     */
    public function index(Event $event)
    {
        if ($event->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Event not found.',
            ], 404);
        }

        $airports = $event->airports()
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $airports,
        ]);
    }
}


