<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\Event;
use Illuminate\Http\Request;

class AirportController extends Controller
{
    /**
     * Find event or accommodation by slug.
     * Checks both Event and Accommodation models.
     */
    private function findEventBySlug($slug)
    {
        // First try Accommodation (most common)
        $event = Accommodation::where('slug', $slug)
            ->where('status', 'published')
            ->first();
        
        // If not found, try Event
        if (!$event) {
            $event = Event::where('slug', $slug)
                ->where('status', 'published')
                ->first();
        }
        
        return $event;
    }

    /**
     * Display a listing of airports for an event.
     * Route: GET /api/events/{slug}/airports
     * Works for both Event and Accommodation types.
     */
    public function index($slug)
    {
        $event = $this->findEventBySlug($slug);

        if (!$event) {
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




