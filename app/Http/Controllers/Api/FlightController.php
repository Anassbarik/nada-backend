<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\Event;
use App\Models\Flight;
use Illuminate\Http\Request;

class FlightController extends Controller
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
     * Display a listing of flights for an event.
     * Route: GET /api/events/{slug}/flights
     * Note: Flights are only available for Accommodations, not Events
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

        // Flights are only for Accommodations
        if (!($event instanceof Accommodation)) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $flights = Flight::where('accommodation_id', $event->id)
            ->where('beneficiary_type', 'organizer') // Only show organizer flights publicly
            ->select([
                'id',
                'accommodation_id',
                'full_name',
                'flight_class',
                'flight_category',
                'departure_date',
                'departure_time',
                'arrival_date',
                'arrival_time',
                'departure_flight_number',
                'departure_airport',
                'arrival_airport',
                'departure_price_ttc',
                'return_date',
                'return_departure_time',
                'return_arrival_date',
                'return_arrival_time',
                'return_flight_number',
                'return_departure_airport',
                'return_arrival_airport',
                'return_price_ttc',
                'reference',
                'eticket_path',
                'beneficiary_type',
                'status',
                'payment_method',
                'created_at',
                'updated_at'
            ])
            ->latest()
            ->get();

        // Check if prices should be shown
        $showPrices = $event->show_flight_prices ?? true;

        // Format flights for frontend
        $formattedFlights = $flights->map(function ($flight) use ($showPrices) {
            $departure = [
                'date' => $flight->departure_date?->format('Y-m-d'),
                'time' => $flight->departure_time ? \Carbon\Carbon::parse($flight->departure_time)->format('H:i') : null,
                'flight_number' => $flight->departure_flight_number,
                'airport' => $flight->departure_airport,
            ];

            if ($showPrices) {
                $departure['price_ttc'] = (float) ($flight->departure_price_ttc ?? 0);
            }

            $return = null;
            if ($flight->return_date) {
                $return = [
                    'date' => $flight->return_date->format('Y-m-d'),
                    'departure_time' => $flight->return_departure_time ? \Carbon\Carbon::parse($flight->return_departure_time)->format('H:i') : null,
                    'departure_airport' => $flight->return_departure_airport,
                    'arrival_date' => $flight->return_arrival_date?->format('Y-m-d'),
                    'arrival_time' => $flight->return_arrival_time ? \Carbon\Carbon::parse($flight->return_arrival_time)->format('H:i') : null,
                    'arrival_airport' => $flight->return_arrival_airport,
                    'flight_number' => $flight->return_flight_number,
                ];

                if ($showPrices) {
                    $return['price_ttc'] = (float) ($flight->return_price_ttc ?? 0);
                }
            }

            $result = [
                'id' => $flight->id,
                'accommodation_id' => $flight->accommodation_id,
                'full_name' => $flight->full_name,
                'flight_class' => $flight->flight_class,
                'flight_class_label' => $flight->flight_class_label,
                'flight_category' => $flight->flight_category ?? 'one_way',
                'flight_category_label' => $flight->flight_category_label,
                'departure' => $departure,
                'arrival' => [
                    'date' => $flight->arrival_date?->format('Y-m-d'),
                    'time' => $flight->arrival_time ? \Carbon\Carbon::parse($flight->arrival_time)->format('H:i') : null,
                    'airport' => $flight->arrival_airport,
                ],
                'return' => $return,
                'reference' => $flight->reference,
                'eticket_url' => $flight->eticket_url,
                'beneficiary_type' => $flight->beneficiary_type,
                'status' => $flight->status,
                'payment_method' => $flight->payment_method,
                'created_at' => $flight->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $flight->updated_at?->format('Y-m-d H:i:s'),
            ];

            if ($showPrices) {
                $result['total_price'] = $flight->total_price;
            }

            return $result;
        });

        return response()->json([
            'success' => true,
            'data' => $formattedFlights,
        ]);
    }

    /**
     * Display the specified flight.
     * Route: GET /api/events/{slug}/flights/{flight}
     */
    public function show($slug, Flight $flight)
    {
        $event = $this->findEventBySlug($slug);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found.',
            ], 404);
        }

        // Verify flight belongs to this accommodation
        if ($flight->accommodation_id !== $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Flight not found for this event.',
            ], 404);
        }

        // Check if prices should be shown
        $showPrices = $event->show_flight_prices ?? true;

        // Format flight for frontend
        $departure = [
            'date' => $flight->departure_date?->format('Y-m-d'),
            'time' => $flight->departure_time ? \Carbon\Carbon::parse($flight->departure_time)->format('H:i') : null,
            'flight_number' => $flight->departure_flight_number,
            'airport' => $flight->departure_airport,
        ];

        if ($showPrices) {
            $departure['price_ttc'] = (float) ($flight->departure_price_ttc ?? 0);
        }

        $return = null;
        if ($flight->return_date) {
            $return = [
                'date' => $flight->return_date->format('Y-m-d'),
                'departure_time' => $flight->return_departure_time ? \Carbon\Carbon::parse($flight->return_departure_time)->format('H:i') : null,
                'departure_airport' => $flight->return_departure_airport,
                'arrival_date' => $flight->return_arrival_date?->format('Y-m-d'),
                'arrival_time' => $flight->return_arrival_time ? \Carbon\Carbon::parse($flight->return_arrival_time)->format('H:i') : null,
                'arrival_airport' => $flight->return_arrival_airport,
                'flight_number' => $flight->return_flight_number,
            ];

            if ($showPrices) {
                $return['price_ttc'] = (float) ($flight->return_price_ttc ?? 0);
            }
        }

        $formattedFlight = [
            'id' => $flight->id,
            'accommodation_id' => $flight->accommodation_id,
            'full_name' => $flight->full_name,
            'flight_class' => $flight->flight_class,
            'flight_class_label' => $flight->flight_class_label,
            'flight_category' => $flight->flight_category ?? 'one_way',
            'flight_category_label' => $flight->flight_category_label,
            'departure' => $departure,
            'arrival' => [
                'date' => $flight->arrival_date?->format('Y-m-d'),
                'time' => $flight->arrival_time ? \Carbon\Carbon::parse($flight->arrival_time)->format('H:i') : null,
                'airport' => $flight->arrival_airport,
            ],
            'return' => $return,
            'reference' => $flight->reference,
            'eticket_url' => $flight->eticket_url,
            'beneficiary_type' => $flight->beneficiary_type,
            'status' => $flight->status,
            'payment_method' => $flight->payment_method,
            'accommodation' => [
                'id' => $event->id,
                'name' => $event->name,
                'slug' => $event->slug,
            ],
            'created_at' => $flight->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $flight->updated_at?->format('Y-m-d H:i:s'),
        ];

        if ($showPrices) {
            $formattedFlight['total_price'] = $flight->total_price;
        }

        return response()->json([
            'success' => true,
            'data' => $formattedFlight,
        ]);
    }
}
