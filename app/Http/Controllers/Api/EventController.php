<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\AccommodationContent;
use App\Models\Event;
use App\Models\EventContent;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of published events.
     * Supports query params: ?latest=true&limit=12 for carousel
     */
    public function index(Request $request)
    {
        $query = Accommodation::where('status', 'published')
            ->latest('created_at'); // Always order by latest first

        // Get limit from query param
        // If latest=true, default to 12 for carousel, otherwise no limit
        $limit = $request->get('limit');
        if ($limit !== null) {
            $query->limit((int) $limit);
        } elseif ($request->boolean('latest')) {
            // Default to 12 for carousel when latest=true
            $query->limit(12);
        }

        $events = $query->get();

        // Format response with all event fields
        $events = $events->map(function ($event) {
            return [
                'id' => $event->id,
                'name' => $event->name,
                'slug' => $event->slug,
                'venue' => $event->venue,
                'location' => $event->location,
                'google_maps_url' => $event->google_maps_url,
                'start_date' => $event->start_date?->format('Y-m-d'),
                'end_date' => $event->end_date?->format('Y-m-d'),
                'formatted_dates' => $event->formatted_dates,
                'compact_dates' => $event->compact_dates,
                'website_url' => $event->website_url,
                'organizer_logo' => $event->organizer_logo_url,
                'organizer_logo_path' => $event->organizer_logo,
                'logo_url' => $event->logo_url,
                'logo_path' => $event->logo_path,
                'banner_url' => $event->banner_url,
                'banner_path' => $event->banner_path,
                'description' => $event->description,
                'menu_links' => $event->menu_links,
                'status' => $event->status,
                'created_at' => $event->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $event->updated_at?->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

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
     * Display the specified event by slug.
     * Works for both Event and Accommodation types.
     */
    public function show($slug)
    {
        $event = $this->findEventBySlug($slug);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found.',
            ], 404);
        }

        // Load relationships based on model type
        if ($event instanceof Accommodation) {
            $event->load([
                'contents',
                'airports' => function ($query) {
                    $query->where('active', true);
                },
                'hotels' => function ($query) {
                    $query->where('status', 'active')
                        ->with([
                            'packages' => function ($q) {
                                $q->where('disponibilite', true);
                            },
                            'images' => function ($q) {
                                $q->where('status', 'active')->orderBy('sort_order');
                            }
                        ]);
                },
                'flights' => function ($query) {
                    $query->where('beneficiary_type', 'organizer') // Only show organizer flights publicly
                        ->latest();
                }
            ]);
        } else {
            // Event model
            $event->load([
                'contents',
                'airports' => function ($query) {
                    $query->where('active', true);
                },
            ]);
        }

        // Ensure contents relationship is loaded
        if (!$event->relationLoaded('contents')) {
            $event->load('contents');
        }

        // Format images to each hotel using URL accessor (only for Accommodations)
        if ($event instanceof Accommodation && $event->relationLoaded('hotels')) {
            $event->hotels->each(function ($hotel) {
                $hotel->images = $hotel->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'url' => $image->url,
                        'alt_text' => $image->alt_text,
                        'is_primary' => $image->sort_order === 0,
                    ];
                });
            });
        }

        // Format flights for frontend (only for Accommodations)
        if ($event instanceof Accommodation && $event->relationLoaded('flights')) {
            $event->flights = $event->flights->map(function ($flight) {
                return [
                    'id' => $flight->id,
                    'accommodation_id' => $flight->accommodation_id,
                    'full_name' => $flight->full_name,
                    'flight_class' => $flight->flight_class,
                    'flight_class_label' => $flight->flight_class_label,
                    'flight_category' => $flight->flight_category ?? 'one_way',
                    'flight_category_label' => $flight->flight_category_label,
                    'departure' => [
                        'date' => $flight->departure_date?->format('Y-m-d'),
                        'time' => $flight->departure_time ? \Carbon\Carbon::parse($flight->departure_time)->format('H:i') : null,
                        'flight_number' => $flight->departure_flight_number,
                        'airport' => $flight->departure_airport,
                        'price_ttc' => (float) ($flight->departure_price_ttc ?? 0),
                    ],
                    'arrival' => [
                        'date' => $flight->arrival_date?->format('Y-m-d'),
                        'time' => $flight->arrival_time ? \Carbon\Carbon::parse($flight->arrival_time)->format('H:i') : null,
                        'airport' => $flight->arrival_airport,
                    ],
                    'return' => $flight->return_date ? [
                        'date' => $flight->return_date->format('Y-m-d'),
                        'departure_time' => $flight->return_departure_time ? \Carbon\Carbon::parse($flight->return_departure_time)->format('H:i') : null,
                        'departure_airport' => $flight->return_departure_airport,
                        'arrival_date' => $flight->return_arrival_date?->format('Y-m-d'),
                        'arrival_time' => $flight->return_arrival_time ? \Carbon\Carbon::parse($flight->return_arrival_time)->format('H:i') : null,
                        'arrival_airport' => $flight->return_arrival_airport,
                        'flight_number' => $flight->return_flight_number,
                        'price_ttc' => (float) ($flight->return_price_ttc ?? 0),
                    ] : null,
                    'total_price' => $flight->total_price,
                    'reference' => $flight->reference,
                    'eticket_url' => $flight->eticket_url,
                    'beneficiary_type' => $flight->beneficiary_type,
                    'status' => $flight->status,
                    'payment_method' => $flight->payment_method,
                    'created_at' => $flight->created_at?->format('Y-m-d H:i:s'),
                    'updated_at' => $flight->updated_at?->format('Y-m-d H:i:s'),
                ];
            });
        }

        // Add URL accessors for event images (using model accessors)
        // Store original path before overwriting with URL
        $organizerLogoPath = $event->organizer_logo;
        // Ensure organizer_logo contains the full URL (consistent with index method)
        $event->organizer_logo = $event->organizer_logo_url;
        $event->organizer_logo_path = $organizerLogoPath;
        $event->logo_url = $event->logo_url;
        $event->banner_url = $event->banner_url;
        
        // Ensure location and google_maps_url are included
        $event->location = $event->location;
        $event->google_maps_url = $event->google_maps_url;
        
        // Add formatted dates for convenience
        $event->formatted_dates = $event->formatted_dates;
        $event->compact_dates = $event->compact_dates;

        // Format contents by page_type BEFORE converting to array
        $contents = [];
        foreach ($event->contents as $content) {
            $sections = $content->sections ?? [];
            
            // Format sections to ensure proper structure (title and points)
            $formattedSections = [];
            foreach ($sections as $section) {
                // Handle legacy content field - convert to points if needed
                $points = $section['points'] ?? [];
                if (empty($points) && isset($section['content'])) {
                    $contentText = $section['content'] ?? '';
                    $points = [];
                    
                    // Split by newlines and filter lines
                    $lines = explode("\n", $contentText);
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (!empty($line)) {
                            // Remove dash prefixes (hyphen, en-dash, em-dash) if present
                            $point = preg_replace('/^[-\x{2013}\x{2014}]\s*/u', '', $line);
                            $point = trim($point);
                            if (!empty($point)) {
                                $points[] = $point;
                            }
                        }
                    }
                    
                    // If no points were found, use the original content as a single point
                    if (empty($points) && !empty($contentText)) {
                        $cleanedContent = preg_replace('/^[-\x{2013}\x{2014}]\s*/u', '', trim($contentText));
                        if (!empty($cleanedContent)) {
                            $points[] = $cleanedContent;
                        }
                    }
                }
                
                $formattedSections[] = [
                    'title' => $section['title'] ?? '',
                    'points' => $points,
                ];
            }
            
            $contents[$content->page_type] = [
                'sections' => $formattedSections,
            ];
        }

        // Build response data manually to ensure contents are included
        $responseData = $event->toArray();
        // Overwrite contents with formatted version
        $responseData['contents'] = $contents;

        return response()->json([
            'success' => true,
            'data' => $responseData,
        ]);
    }

    /**
     * Get event content by type.
     * Route: GET /api/events/{slug}/{type}
     * Types: conditions, info, faq
     * Works for both Event and Accommodation types.
     */
    public function getContentByType($slug, string $type)
    {
        // Find event or accommodation by slug
        $event = $this->findEventBySlug($slug);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found.',
            ], 404);
        }

        // Map type aliases
        $pageTypeMap = [
            'conditions' => 'conditions',
            'info' => 'info',
            'informations' => 'info',
            'faq' => 'faq',
        ];

        $pageType = $pageTypeMap[$type] ?? $type;

        // Find content based on model type
        if ($event instanceof Accommodation) {
            $content = AccommodationContent::where('accommodation_id', $event->id)
                ->where('page_type', $pageType)
                ->first();
        } else {
            $content = EventContent::where('event_id', $event->id)
                ->where('page_type', $pageType)
                ->first();
        }

        if (!$content) {
            return response()->json([
                'success' => false,
                'message' => 'Content not found.',
                'data' => [
                    'event' => [
                        'id' => $event->id,
                        'name' => $event->name,
                        'slug' => $event->slug,
                    ],
                    'type' => $type,
                    'content' => null,
                ],
            ], 404);
        }

        // Format sections with points structure
        $sections = $content->sections ?? [];
        $formattedSections = [];
        foreach ($sections as $section) {
            // Handle legacy content field - convert to points if needed
            $points = $section['points'] ?? [];
            if (empty($points) && isset($section['content'])) {
                $contentText = $section['content'] ?? '';
                $points = [];
                
                // Split by newlines and filter lines
                $lines = explode("\n", $contentText);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!empty($line)) {
                        // Remove dash prefixes (hyphen, en-dash, em-dash) if present
                        $point = preg_replace('/^[-\x{2013}\x{2014}]\s*/u', '', $line);
                        $point = trim($point);
                        if (!empty($point)) {
                            $points[] = $point;
                        }
                    }
                }
                
                // If no points were found, use the original content as a single point
                if (empty($points) && !empty($contentText)) {
                    $cleanedContent = preg_replace('/^[-\x{2013}\x{2014}]\s*/u', '', trim($contentText));
                    if (!empty($cleanedContent)) {
                        $points[] = $cleanedContent;
                    }
                }
            }
            
            $formattedSections[] = [
                'title' => $section['title'] ?? '',
                'points' => $points,
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'event' => [
                    'id' => $event->id,
                    'name' => $event->name,
                    'slug' => $event->slug,
                ],
                'type' => $type,
                'sections' => $formattedSections,
            ],
        ]);
    }
}
