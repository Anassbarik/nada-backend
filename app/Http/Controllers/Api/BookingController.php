<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    /**
     * Display a listing of user's bookings.
     * Route: GET /api/bookings
     */
    public function index(Request $request)
    {
        $userEmail = $request->user()->email;
        
        $bookings = Booking::where(function($query) use ($userEmail) {
                $query->where('guest_email', $userEmail)
                      ->orWhere('email', $userEmail);
            })
            ->with(['event', 'hotel', 'package'])
            ->latest()
            ->get();

        // Format bookings to include event information
        $formattedBookings = $bookings->map(function ($booking) {
            return [
                'id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'status' => $booking->status,
                'full_name' => $booking->full_name ?? $booking->guest_name,
                'email' => $booking->email ?? $booking->guest_email,
                'phone' => $booking->phone ?? $booking->guest_phone,
                'flight_number' => $booking->flight_number,
                'flight_date' => $booking->flight_date?->format('Y-m-d'),
                'flight_time' => $booking->flight_time?->format('H:i'),
                'airport' => $booking->airport,
                'checkin_date' => $booking->checkin_date?->format('Y-m-d'),
                'checkout_date' => $booking->checkout_date?->format('Y-m-d'),
                'guests_count' => $booking->guests_count,
                'price' => $booking->price,
                'resident_name_1' => $booking->resident_name_1,
                'resident_name_2' => $booking->resident_name_2,
                'special_instructions' => $booking->special_instructions ?? $booking->special_requests,
                'event' => $booking->event ? [
                    'id' => $booking->event->id,
                    'name' => $booking->event->name,
                    'slug' => $booking->event->slug,
                    'venue' => $booking->event->venue,
                    'start_date' => $booking->event->start_date?->format('Y-m-d'),
                    'end_date' => $booking->event->end_date?->format('Y-m-d'),
                ] : null,
                'hotel' => $booking->hotel ? [
                    'id' => $booking->hotel->id,
                    'name' => $booking->hotel->name,
                    'slug' => $booking->hotel->slug,
                    'location' => $booking->hotel->location,
                ] : null,
                'package' => $booking->package ? [
                    'id' => $booking->package->id,
                    'nom_package' => $booking->package->nom_package,
                    'type_chambre' => $booking->package->type_chambre,
                    'prix_ttc' => $booking->package->prix_ttc,
                ] : null,
                'created_at' => $booking->created_at?->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedBookings,
        ]);
    }

    /**
     * Store a newly created booking.
     * Route: POST /api/bookings or POST /api/events/{event:slug}/hotels/{hotel:slug}/bookings
     */
    public function store(Request $request, Event $event = null, Hotel $hotel = null)
    {
        // Validation rules - adjust based on route
        $validationRules = [
            'package_id' => 'required|exists:hotel_packages,id',
            'flight_number' => 'nullable|string|max:20',
            'flight_date' => 'nullable|date',
            'flight_time' => 'nullable',
            'airport' => 'nullable|string|max:10',
            'full_name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'special_instructions' => 'nullable|string',
            'resident_name_1' => 'nullable|string|max:255',
            'resident_name_2' => 'nullable|string|max:255',
            'terms_accepted' => 'required|accepted',
            // Legacy fields for backward compatibility
            'guest_name' => 'nullable|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'guest_phone' => 'nullable|string|max:255',
            'special_requests' => 'nullable|string|max:5000',
            'guests_count' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
        ];

        // If using direct /bookings route, require event_id and hotel_id
        if (!$event || !$hotel) {
            $validationRules['event_id'] = 'required|exists:events,id';
            $validationRules['hotel_id'] = 'required|exists:hotels,id';
        }

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Handle route model binding vs request parameters
        if ($event && $hotel) {
            // Route: /events/{event:slug}/hotels/{hotel:slug}/bookings
            // $event and $hotel are already Event and Hotel models from route binding
            // Verify hotel belongs to event
            if ($hotel->event_id != $event->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hotel does not belong to the specified event.',
                ], 422);
            }
        } else {
            // Route: /bookings (using request parameters)
            $hotelId = $request->hotel_id;
        $hotel = Hotel::findOrFail($hotelId);
            $event = Event::findOrFail($request->event_id);

            // Verify event_id matches the hotel's event
        if ($hotel->event_id != $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Hotel does not belong to the specified event.',
            ], 422);
        }
        }
        
        // Get package
        $package = \App\Models\Package::findOrFail($request->package_id);

        // Check if package belongs to hotel
        if ($package->hotel_id != $hotel->id) {
            return response()->json([
                'success' => false,
                'message' => 'Package does not belong to the specified hotel.',
            ], 422);
        }

        // Check if package is available
        if (!$package->disponibilite) {
            return response()->json([
                'success' => false,
                'message' => 'Package is not available.',
            ], 422);
        }

        // Check if there are rooms available
        if ($package->chambres_restantes <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'No rooms available for this package.',
            ], 422);
        }

        // Use new field names, fallback to legacy field names for backward compatibility
        $fullName = $request->full_name ?? $request->guest_name;
        $phone = $request->phone ?? $request->guest_phone;
        $email = $request->email ?? $request->guest_email;
        $specialInstructions = $request->special_instructions ?? $request->special_requests;

        $bookingData = [
            'event_id' => $event->id,
            'hotel_id' => $hotel->id,
            'package_id' => $package->id,
            'flight_number' => $request->flight_number ?? null,
            'flight_date' => $request->flight_date ? \Carbon\Carbon::parse($request->flight_date) : null,
            'flight_time' => $request->flight_time ? \Carbon\Carbon::parse($request->flight_time) : null,
            'airport' => $request->airport ?? null,
            'full_name' => $fullName,
            'company' => $request->company ?? null,
            'phone' => $phone,
            'email' => $email,
            'special_instructions' => $specialInstructions,
            'resident_name_1' => $request->resident_name_1 ?? null,
            'resident_name_2' => $request->resident_name_2 ?? null,
            'checkin_date' => $package->check_in,
            'checkout_date' => $package->check_out,
            'guests_count' => $request->guests_count ?? $package->occupants,
            'price' => $request->price ?? $package->prix_ttc,
            'status' => 'pending',
            // Legacy fields for backward compatibility
            'guest_name' => $fullName,
            'guest_email' => $email,
            'guest_phone' => $phone,
            'special_requests' => $specialInstructions,
        ];

        $booking = Booking::create($bookingData);

        // Decrease available rooms
        $package->chambres_restantes = max(0, $package->chambres_restantes - 1);
        $package->disponibilite = $package->chambres_restantes > 0;
        $package->save();

        $booking->load(['event', 'hotel', 'package']);

        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully.',
            'data' => [
                'booking' => [
                    'id' => $booking->id,
                    'reference' => $booking->booking_reference,
                    'status' => $booking->status,
                    'full_name' => $booking->full_name ?? $booking->guest_name,
                    'email' => $booking->email ?? $booking->guest_email,
                ]
            ],
        ], 200);
    }

    /**
     * Display the specified booking.
     */
    public function show(Request $request, Booking $booking)
    {
        // Ensure user can only view their own bookings
        $userEmail = $request->user()->email;
        if ($booking->guest_email !== $userEmail && $booking->email !== $userEmail) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $booking->load(['event', 'hotel', 'package']);

        return response()->json([
            'success' => true,
            'data' => $booking,
        ]);
    }

    /**
     * Update booking status.
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,paid,confirmed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Use database transaction to ensure data consistency
        // The Booking model's updating event will automatically handle room count updates
        DB::transaction(function () use ($booking, $request) {
            $booking->status = $request->status;
            $booking->save(); // Model event will handle package room count update
        });

        // Reload booking with relationships
        $booking->load(['event', 'hotel', 'package']);

        return response()->json([
            'success' => true,
            'message' => 'Booking status updated successfully.',
            'data' => $booking,
        ]);
    }
}
