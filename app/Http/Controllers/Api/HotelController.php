<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Hotel;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    /**
     * Display a listing of hotels for an event.
     * Route: GET /api/events/{slug}/hotels
     */
    public function index(Event $event)
    {
        if ($event->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Event not found.',
            ], 404);
        }

        $hotels = $event->hotels()
            ->where('status', 'active')
            ->select([
                'id',
                'event_id',
                'name',
                'slug',
                'location',
                'location_url',
                'duration',
                'description',
                'inclusions',
                'website',
                'rating',
                'review_count',
                'status',
                'created_at',
                'updated_at'
            ])
            ->with([
                'packages' => function ($query) {
                    $query->where('disponibilite', true);
                },
                'images' => function ($query) {
                    $query->where('status', 'active')
                        ->orderBy('sort_order')
                        ->limit(1); // Only first image for list view
                }
            ])
            ->latest()
            ->get();

        // Format images and add rating_stars for frontend display
        $hotels->each(function ($hotel) {
            // Format images array using URL accessor
            $hotel->images = $hotel->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => $image->url,
                    'alt_text' => $image->alt_text,
                    'is_primary' => $image->sort_order === 0,
                ];
            });
            
            // Always include rating_stars (null if no rating)
            $hotel->rating_stars = $hotel->rating ? $hotel->rating_stars : null;
        });

        return response()->json([
            'success' => true,
            'data' => $hotels,
        ]);
    }

    /**
     * Display a listing of all hotels.
     * Route: GET /api/hotels
     */
    public function listAll(Request $request)
    {
        $hotels = Hotel::where('status', 'active')
            ->select([
                'id',
                'event_id',
                'name',
                'slug',
                'location',
                'location_url',
                'duration',
                'description',
                'inclusions',
                'website',
                'rating',
                'review_count',
                'status',
                'created_at',
                'updated_at'
            ])
            ->with([
                'event' => function ($query) {
                    $query->where('status', 'published')
                        ->select(['id', 'name', 'slug']);
                },
                'packages' => function ($query) {
                    $query->where('disponibilite', true);
                },
                'images' => function ($query) {
                    $query->where('status', 'active')
                        ->orderBy('sort_order')
                        ->limit(1); // Only first image for list view
                }
            ])
            ->latest()
            ->get();

        // Filter out hotels that don't belong to published events
        $hotels = $hotels->filter(function ($hotel) {
            return $hotel->event !== null;
        });

        // Format images and add rating_stars for frontend display
        $hotels->each(function ($hotel) {
            // Format images array using URL accessor
            $hotel->images = $hotel->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => $image->url,
                    'alt_text' => $image->alt_text,
                    'is_primary' => $image->sort_order === 0,
                ];
            });
            
            // Always include rating_stars (null if no rating)
            $hotel->rating_stars = $hotel->rating ? $hotel->rating_stars : null;
        });

        return response()->json([
            'success' => true,
            'data' => $hotels->values(), // Reset keys after filtering
        ]);
    }

    /**
     * Display the specified hotel within an event context.
     * Route: GET /api/events/{event:slug}/hotels/{hotel:slug}
     */
    public function show(Event $event, Hotel $hotel)
    {
        // Ensure event is published
        if ($event->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Event not found.',
            ], 404);
        }

        // Route model binding resolves hotels by slug globally, not scoped to event
        // Ensure the hotel belongs to this event
        // If route model binding found a hotel from a different event, re-resolve scoped to this event
        if ($hotel->event_id !== $event->id) {
            $hotel = Hotel::where('slug', $hotel->slug)
                ->where('event_id', $event->id)
                ->firstOrFail();
        }

        // Ensure hotel is active
        if ($hotel->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Hotel not found.',
            ], 404);
        }

        // Load additional relationships
        $hotel->load([
            'packages' => function ($query) {
                $query->where('disponibilite', true);
            },
            'images' => function ($query) {
                $query->where('status', 'active')->orderBy('sort_order');
            }
        ]);

        // Format images using URL accessor
        $hotel->images = $hotel->images->map(function ($image) {
            return [
                'id' => $image->id,
                'url' => $image->url,
                'alt_text' => $image->alt_text,
                'is_primary' => $image->sort_order === 0,
            ];
        });
        
        // Add rating_stars for frontend display
        $hotel->rating_stars = $hotel->rating ? $hotel->rating_stars : null;

        // Event is already loaded from route parameter, no need to format separately
        // The event relationship will be included in the response automatically

        return response()->json([
            'success' => true,
            'data' => $hotel,
        ]);
    }
}
