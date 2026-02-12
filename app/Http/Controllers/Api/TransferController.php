<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\Transfer;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    /**
     * Find event or accommodation by slug.
     */
    private function findEventBySlug($slug)
    {
        $event = Accommodation::where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (!$event) {
            $event = \App\Models\Event::where('slug', $slug)
                ->where('status', 'published')
                ->first();
        }

        return $event;
    }

    /**
     * Display a listing of transfers for an event.
     * Route: GET /api/events/{slug}/transfers
     */
    public function index(Request $request, $slug)
    {
        $event = $this->findEventBySlug($slug);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found.',
            ], 404);
        }

        // Transfers are typically for Accommodations
        if (!($event instanceof Accommodation)) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $query = $event->transfers()
            ->where('status', '!=', 'cancelled');

        // If 'my' parameter is passed or for dashboard logic, filter by user
        if ($request->query('type') === 'my' && $request->user()) {
            $query->where('user_id', $request->user()->id);
        } else {
            // Default: show organizer transfers (available for booking)
            $query->where('beneficiary_type', 'organizer');
        }

        $transfers = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $transfers->map(function ($transfer) {
                return [
                    'id' => $transfer->id,
                    'accommodation_id' => $transfer->accommodation_id,
                    'type' => $transfer->transfer_type,
                    'type_label' => $transfer->transfer_type_label,
                    'trip_type' => $transfer->trip_type,
                    'trip_type_label' => $transfer->trip_type_label,
                    'date' => $transfer->transfer_date ? \Carbon\Carbon::parse($transfer->transfer_date)->format('Y-m-d') : null,
                    'time' => $transfer->pickup_time,
                    'pickup' => $transfer->pickup_location,
                    'dropoff' => $transfer->dropoff_location,
                    'flight_number' => $transfer->flight_number,
                    'vehicle' => $transfer->vehicle_type,
                    'vehicle_label' => $transfer->vehicle_type_label,
                    'vehicle_type_id' => $transfer->vehicle_type_id,
                    'max_passengers' => $transfer->vehicleType?->max_passengers ?? 0,
                    'max_luggages' => $transfer->vehicleType?->max_luggages ?? 0,
                    'passengers' => $transfer->passengers,
                    'luggages' => $transfer->luggages,
                    'price' => (float) $transfer->price,
                    'status' => $transfer->status,
                    'eticket_url' => $transfer->eticket_url,
                    'beneficiary_type' => $transfer->beneficiary_type,
                    'driver_name' => $transfer->driver_name,
                    'driver_phone' => $transfer->driver_phone,
                    'additional_passengers' => $transfer->additional_passengers,
                ];
            })
        ]);
    }

    /**
     * Display the specified transfer.
     * Route: GET /api/events/{slug}/transfers/{transfer}
     */
    public function show($slug, Transfer $transfer)
    {
        $event = $this->findEventBySlug($slug);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found.',
            ], 404);
        }

        // Ensure transfer belongs to accommodation
        if ($transfer->accommodation_id !== $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Transfer not found for this event.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $transfer->id,
                'accommodation_id' => $transfer->accommodation_id,
                'type' => $transfer->transfer_type,
                'type_label' => $transfer->transfer_type_label,
                'trip_type' => $transfer->trip_type,
                'trip_type_label' => $transfer->trip_type_label,
                'date' => $transfer->transfer_date ? \Carbon\Carbon::parse($transfer->transfer_date)->format('Y-m-d') : null,
                'time' => $transfer->pickup_time,
                'pickup' => $transfer->pickup_location,
                'dropoff' => $transfer->dropoff_location,
                'flight_number' => $transfer->flight_number,
                'vehicle' => $transfer->vehicle_type,
                'vehicle_label' => $transfer->vehicle_type_label,
                'vehicle_type_id' => $transfer->vehicle_type_id,
                'max_passengers' => $transfer->vehicleType?->max_passengers ?? 0,
                'max_luggages' => $transfer->vehicleType?->max_luggages ?? 0,
                'passengers' => $transfer->passengers,
                'luggages' => $transfer->luggages,
                'price' => (float) $transfer->price,
                'status' => $transfer->status,
                'eticket_url' => $transfer->eticket_url,
                'beneficiary_type' => $transfer->beneficiary_type,
                'driver_name' => $transfer->driver_name,
                'driver_phone' => $transfer->driver_phone,
                'additional_passengers' => $transfer->additional_passengers,
            ]
        ]);
    }
}
