<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\BookingConfirmation;
use App\Mail\BookingNotification;
use App\Models\Booking;
use App\Models\Accommodation;
use App\Models\Event;
use App\Models\Hotel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Services\DualStorageService;

class BookingController extends Controller
{
    /**
     * Display a listing of user's bookings.
     * Route: GET /api/bookings
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get bookings by user_id (primary) or email (fallback for legacy bookings)
        $bookings = Booking::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere(function($q) use ($user) {
                          $q->whereNull('user_id')
                            ->where(function($emailQuery) use ($user) {
                                $emailQuery->where('guest_email', $user->email)
                                          ->orWhere('email', $user->email);
                            });
                      });
            })
            ->with(['accommodation', 'hotel', 'package', 'user', 'flight'])
            ->latest()
            ->get();

        // Format bookings to include event information
        $formattedBookings = $bookings->map(function ($booking) {
            // Check if flight prices should be shown (client dashboard setting)
            $showFlightPrices = $booking->accommodation ? ($booking->accommodation->show_flight_prices_client_dashboard ?? true) : true;
            
            // Format flight data conditionally
            $flightData = null;
            if ($booking->flight) {
                $flightData = [
                    'id' => $booking->flight->id,
                    'full_name' => $booking->flight->full_name,
                    'flight_class' => $booking->flight->flight_class,
                    'flight_class_label' => $booking->flight->flight_class_label,
                    'flight_category' => $booking->flight->flight_category ?? 'one_way',
                    'flight_category_label' => $booking->flight->flight_category_label,
                    'departure' => [
                        'date' => $booking->flight->departure_date ? \Carbon\Carbon::parse($booking->flight->departure_date)->format('Y-m-d') : null,
                        'time' => $booking->flight->departure_time ? \Carbon\Carbon::parse($booking->flight->departure_time)->format('H:i') : null,
                        'flight_number' => $booking->flight->departure_flight_number,
                        'airport' => $booking->flight->departure_airport,
                    ],
                    'arrival' => [
                        'date' => $booking->flight->arrival_date ? \Carbon\Carbon::parse($booking->flight->arrival_date)->format('Y-m-d') : null,
                        'time' => $booking->flight->arrival_time ? \Carbon\Carbon::parse($booking->flight->arrival_time)->format('H:i') : null,
                        'airport' => $booking->flight->arrival_airport,
                    ],
                    'reference' => $booking->flight->reference,
                ];
                
                // Conditionally include prices
                if ($showFlightPrices) {
                    $flightData['departure']['price_ttc'] = (float) ($booking->flight->departure_price_ttc ?? 0);
                }
                
                // Add return flight if exists
                if ($booking->flight->return_date) {
                    $flightData['return'] = [
                        'date' => $booking->flight->return_date ? \Carbon\Carbon::parse($booking->flight->return_date)->format('Y-m-d') : null,
                        'departure_time' => $booking->flight->return_departure_time ? \Carbon\Carbon::parse($booking->flight->return_departure_time)->format('H:i') : null,
                        'departure_airport' => $booking->flight->return_departure_airport,
                        'arrival_date' => $booking->flight->return_arrival_date ? \Carbon\Carbon::parse($booking->flight->return_arrival_date)->format('Y-m-d') : null,
                        'arrival_time' => $booking->flight->return_arrival_time ? \Carbon\Carbon::parse($booking->flight->return_arrival_time)->format('H:i') : null,
                        'arrival_airport' => $booking->flight->return_arrival_airport,
                        'flight_number' => $booking->flight->return_flight_number,
                    ];
                    
                    if ($showFlightPrices) {
                        $flightData['return']['price_ttc'] = (float) ($booking->flight->return_price_ttc ?? 0);
                        $flightData['total_price'] = $booking->flight->total_price;
                    }
                } elseif ($showFlightPrices) {
                    $flightData['total_price'] = (float) ($booking->flight->departure_price_ttc ?? 0);
                }
            }
            
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
                'payment_document_path' => $booking->payment_document_path,
                'payment_document_url' => $booking->payment_document_url,
                'flight_ticket_path' => $booking->flight_ticket_path,
                'flight_ticket_url' => $booking->flight_ticket_url,
                'flight' => $flightData,
                'event' => $booking->accommodation ? [
                    'id' => $booking->accommodation->id,
                    'name' => $booking->accommodation->name,
                    'slug' => $booking->accommodation->slug,
                    'venue' => $booking->accommodation->venue,
                    'location' => $booking->accommodation->location,
                    'google_maps_url' => $booking->accommodation->google_maps_url,
                    'start_date' => $booking->accommodation->start_date?->format('Y-m-d'),
                    'end_date' => $booking->accommodation->end_date?->format('Y-m-d'),
                    'show_flight_prices_public' => $booking->accommodation->show_flight_prices_public ?? true,
                    'show_flight_prices_client_dashboard' => $booking->accommodation->show_flight_prices_client_dashboard ?? true,
                    'show_flight_prices_organizer_dashboard' => $booking->accommodation->show_flight_prices_organizer_dashboard ?? true,
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
     * Find booking by reference.
     * Route: GET /api/bookings/reference/{reference}
     * Public endpoint to verify booking reference before linking hotel package
     */
    public function findByReference($reference)
    {
        $booking = Booking::where('booking_reference', $reference)
            ->with(['accommodation:id,name,slug', 'flight:id,full_name,departure_date,departure_flight_number'])
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking reference not found.',
            ], 404);
        }

        // Check if booking can be linked (must be flight-only, not already linked)
        $canLink = $booking->flight_id && !$booking->hotel_id && !$booking->package_id;

        return response()->json([
            'success' => true,
            'data' => [
                'booking_reference' => $booking->booking_reference,
                'accommodation' => $booking->accommodation ? [
                    'id' => $booking->accommodation->id,
                    'name' => $booking->accommodation->name,
                    'slug' => $booking->accommodation->slug,
                ] : null,
                'flight' => $booking->flight ? [
                    'id' => $booking->flight->id,
                    'full_name' => $booking->flight->full_name,
                    'departure_date' => $booking->flight->departure_date?->format('Y-m-d'),
                    'departure_flight_number' => $booking->flight->departure_flight_number,
                ] : null,
                'guest_name' => $booking->guest_name ?? $booking->full_name,
                'guest_email' => $booking->guest_email ?? $booking->email,
                'can_link' => $canLink,
                'already_linked' => !$canLink && ($booking->hotel_id || $booking->package_id),
            ],
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
     * Store a newly created booking.
     * Route: POST /api/bookings or POST /api/events/{slug}/hotels/{hotel:slug}/bookings
     */
    public function store(Request $request, $slug = null, Hotel $hotel = null)
    {
        // DEBUG: Log incoming request
        Log::info('Booking request received:', [
            'all_data' => $request->all(),
            'auth_user_id' => Auth::check() ? Auth::id() : null,
            'headers' => $request->headers->all(),
        ]);

        // Check authentication
        if (!Auth::check()) {
            Log::warning('Booking creation attempted without authentication');
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        Log::info('Auth user for booking:', ['id' => Auth::id(), 'email' => Auth::user()->email]);

        // Validation rules - adjust based on route
        // Support both new field names and legacy field names
        $validationRules = [
            'booking_reference' => 'nullable|string|max:50', // For linking to existing flight booking
            'package_id' => 'required|exists:hotel_packages,id',
            // Flight fields - support both flight_number and flight_num
            // These will be conditionally required based on booking_reference
            'flight_number' => 'nullable|string|max:20',
            'flight_num' => 'nullable|string|max:20', // Frontend may send this
            'flight_date' => 'nullable|date',
            'flight_time' => 'nullable',
            'airport' => 'nullable|string|max:10',
            // Name fields - support both full_name and guest_name
            'full_name' => 'nullable|string|max:255',
            'guest_name' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            // Contact fields
            'phone' => 'nullable|string|max:20',
            'guest_phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'guest_email' => 'nullable|email|max:255', // Changed from required to nullable
            // Other fields
            'special_instructions' => 'nullable|string',
            'special_requests' => 'nullable|string|max:5000',
            'booker_is_resident' => 'required|boolean',
            'resident_name_1' => 'nullable|string|max:255',
            'resident_name_2' => 'nullable|string|max:255',
            'resident_name_3' => 'nullable|string|max:255',
            'terms_accepted' => 'nullable|accepted', // Made nullable for API calls
            'guests_count' => 'nullable|integer|min:1',
            // Price fields - support both price and total
            'price' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0.01', // Frontend may send this
            // Date fields
            'checkin_date' => 'nullable|date',
            'checkout_date' => 'nullable|date',
            // Status
            'status' => 'nullable|in:pending,confirmed,paid,cancelled,refunded',
            // Payment method
            'payment_method' => 'nullable|in:wallet,bank,both',
            // User ID (will be overridden by auth()->id())
            'user_id' => 'nullable|exists:users,id',
        ];

        // Resolve event from slug if provided
        $event = null;
        if ($slug) {
            $event = $this->findEventBySlug($slug);
            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event not found.',
                ], 404);
            }
            // Hotels are only for Accommodations
            if (!($event instanceof Accommodation)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hotels are not available for this event type.',
                ], 404);
            }
        }

        // If using direct /bookings route, require event_id and hotel_id
        if (!$event || !$hotel) {
            $validationRules['accommodation_id'] = 'required|exists:accommodations,id';
            $validationRules['hotel_id'] = 'required|exists:hotels,id';
        }

        // Conditional validation: If booking_reference is provided, check if it's valid
        // If valid, flight fields are optional. If not provided or invalid, flight fields are required.
        $hasValidBookingReference = false;
        if ($request->filled('booking_reference')) {
            // Check if booking reference exists and is a valid flight-only booking
            $existingBookingCheck = Booking::where('booking_reference', $request->booking_reference)
                ->whereNotNull('flight_id')
                ->whereNull('hotel_id')
                ->whereNull('package_id')
                ->first();
            
            if ($existingBookingCheck) {
                // If event is resolved, verify the booking belongs to the same accommodation
                if ($event) {
                    if ($existingBookingCheck->accommodation_id == $event->id) {
                        $hasValidBookingReference = true;
                    }
                } else {
                    // If event is not resolved yet, we'll check it later, but mark as potentially valid
                    // This allows validation to pass, but we'll verify later
                    $hasValidBookingReference = true;
                }
            }
        }

        // If no valid booking_reference, flight fields are required
        if (!$hasValidBookingReference) {
            // At least one of flight_number or flight_num must be provided
            $validationRules['flight_number'] = 'required_without:flight_num|nullable|string|max:20';
            $validationRules['flight_num'] = 'required_without:flight_number|nullable|string|max:20';
            $validationRules['flight_date'] = 'required|date';
            $validationRules['flight_time'] = 'required';
            $validationRules['airport'] = 'required|string|max:10';
        }
        // If has valid booking_reference, flight fields remain nullable (already set above)

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            Log::error('Booking validation failed:', [
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->all(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Get package to determine conditional validation for resident names
        $package = \App\Models\Package::findOrFail($request->package_id);
        
        // Add conditional validation for resident names based on package occupants
        $occupants = (int) $package->occupants;
        $bookerIsResident = $request->boolean('booker_is_resident');
        $requiredNames = $occupants - ($bookerIsResident ? 1 : 0);

        // Validate resident names conditionally
        $residentNameErrors = [];
        if ($requiredNames >= 1 && empty(trim($request->resident_name_1 ?? ''))) {
            $residentNameErrors['resident_name_1'] = ['The resident name 1 field is required.'];
        }
        if ($requiredNames >= 2 && empty(trim($request->resident_name_2 ?? ''))) {
            $residentNameErrors['resident_name_2'] = ['The resident name 2 field is required.'];
        }
        if ($requiredNames >= 3 && empty(trim($request->resident_name_3 ?? ''))) {
            $residentNameErrors['resident_name_3'] = ['The resident name 3 field is required.'];
        }

        if (!empty($residentNameErrors)) {
            Log::error('Booking resident name validation failed:', [
                'errors' => $residentNameErrors,
                'occupants' => $occupants,
                'booker_is_resident' => $bookerIsResident,
                'required_names' => $requiredNames,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $residentNameErrors,
            ], 422);
        }

        // Handle route model binding vs request parameters
        if ($event && $hotel) {
            // Route: /events/{event:slug}/hotels/{hotel:slug}/bookings
            // $event and $hotel are already Event and Hotel models from route binding
            // Verify hotel belongs to event
            if ($hotel->accommodation_id != $event->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hotel does not belong to the specified event.',
                ], 422);
            }
        } else {
            // Route: /bookings (using request parameters)
            $hotelId = $request->hotel_id;
        $hotel = Hotel::findOrFail($hotelId);
            $event = Accommodation::findOrFail($request->accommodation_id);

            // Verify event_id matches the hotel's event
        if ($hotel->event_id != $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Hotel does not belong to the specified event.',
            ], 422);
        }
        }
        
        // Package is already loaded from validation above

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
        
        // Support both flight_number and flight_num
        $flightNumber = $request->flight_number ?? $request->flight_num ?? null;
        
        // Support both price and total
        $total = $request->price ?? $request->total ?? $package->prix_ttc;
        
        // Support checkin/checkout dates from request or use package dates
        $checkinDate = $request->checkin_date ? \Carbon\Carbon::parse($request->checkin_date) : $package->check_in;
        $checkoutDate = $request->checkout_date ? \Carbon\Carbon::parse($request->checkout_date) : $package->check_out;

        // Ensure we have required fields
        if (!$fullName) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => ['full_name' => ['The full name field is required.']],
            ], 422);
        }

        // SMART WALLET PAYMENT LOGIC
        $user = Auth::user();
        
        // Ensure wallet exists and load it
        $wallet = \App\Models\Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0.00]
        );
        
        $walletBalance = (float) $wallet->balance;
        $paymentMethod = $request->payment_method ?? 'bank'; // Default to bank if not specified
        
        // Initialize payment amounts
        $walletAmount = 0;
        $bankAmount = 0;
        $paymentType = 'bank';
        $bookingStatus = $request->status ?? 'pending';
        
        // Calculate payment split based on payment method
        switch ($paymentMethod) {
            case 'wallet':
                if ($walletBalance >= $total) {
                    $walletAmount = $total;
                    $paymentType = 'wallet';
                    $bookingStatus = 'confirmed'; // Wallet payments auto-confirmed
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Solde insuffisant dans le portefeuille.',
                        'errors' => [
                            'payment_method' => ['Votre solde (' . number_format($walletBalance, 2, ',', ' ') . ' MAD) est insuffisant pour couvrir le montant total (' . number_format($total, 2, ',', ' ') . ' MAD).'],
                        ],
                    ], 422);
                }
                break;
                
            case 'bank':
                $bankAmount = $total;
                $paymentType = 'bank';
                break;
                
            case 'both':
                $walletAmount = min($walletBalance, $total);
                $bankAmount = $total - $walletAmount;
                $paymentType = $walletAmount > 0 ? 'both' : 'bank';
                // If wallet covers full amount, auto-confirm
                if ($walletAmount >= $total) {
                    $bookingStatus = 'confirmed';
                }
                break;
        }

        // Check if linking to existing flight booking via booking_reference
        $existingBooking = null;
        if ($request->filled('booking_reference')) {
            $existingBooking = Booking::where('booking_reference', $request->booking_reference)
                ->whereNotNull('flight_id')
                ->whereNull('hotel_id')
                ->whereNull('package_id')
                ->first();
            
            if (!$existingBooking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking reference not found or invalid. Please check your reference number.',
                    'errors' => [
                        'booking_reference' => ['Invalid booking reference. It must be a flight-only booking that hasn\'t been linked to a hotel yet.'],
                    ],
                ], 422);
            }
            
            // Verify the booking belongs to the same accommodation
            if ($event && $existingBooking->accommodation_id != $event->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking reference does not match this event.',
                    'errors' => [
                        'booking_reference' => ['The booking reference belongs to a different event.'],
                    ],
                ], 422);
            }
            
            // Verify booking is not already linked to a hotel
            if ($existingBooking->hotel_id || $existingBooking->package_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This booking reference has already been linked to a hotel package.',
                    'errors' => [
                        'booking_reference' => ['This booking is already complete.'],
                    ],
                ], 422);
            }
        }

        // TRANSACTION SAFETY: Create booking and deduct wallet in a transaction
        try {
            DB::beginTransaction();
            
            // Deduct wallet amount if applicable
            if ($walletAmount > 0) {
                // Refresh wallet to get latest balance
                $wallet->refresh();
                if ($wallet->balance < $walletAmount) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Solde insuffisant dans le portefeuille.',
                        'errors' => [
                            'payment_method' => ['Le solde disponible a changé. Veuillez réessayer.'],
                        ],
                    ], 422);
                }
                $wallet->decrement('balance', $walletAmount);
                Log::info('Wallet amount deducted:', [
                    'user_id' => $user->id,
                    'amount' => $walletAmount,
                    'new_balance' => $wallet->fresh()->balance,
                ]);
            }

            // Set created_by if booking is created by an admin
            $createdBy = null;
            if ($user->isAdmin()) {
                $createdBy = $user->id;
            }

            // Calculate commission amount
            $commissionAmount = null;
            if ($event->commission_percentage && $event->commission_percentage > 0) {
                $bookingPrice = $existingBooking ? (($existingBooking->price ?? 0) + $total) : $total;
                $commissionAmount = round(($bookingPrice * $event->commission_percentage) / 100, 2);
            }

            if ($existingBooking) {
                // Update existing flight booking with hotel/package details
                $existingBooking->update([
                    'user_id' => $user->id, // Link user account
                    'hotel_id' => $hotel->id,
                    'package_id' => $package->id,
                    'flight_number' => $flightNumber ?? $existingBooking->flight_number,
                    'flight_date' => $request->flight_date ? \Carbon\Carbon::parse($request->flight_date) : $existingBooking->flight_date,
                    'flight_time' => $request->flight_time ? \Carbon\Carbon::parse($request->flight_time) : $existingBooking->flight_time,
                    'airport' => $request->airport ?? $existingBooking->airport,
                    'full_name' => $fullName ?? $existingBooking->full_name,
                    'company' => $request->company ?? $existingBooking->company,
                    'phone' => $phone ?? $existingBooking->phone,
                    'email' => $email ?? $existingBooking->email,
                    'special_instructions' => $specialInstructions ?? $existingBooking->special_instructions,
                    'booker_is_resident' => (bool) ($request->booker_is_resident ?? $existingBooking->booker_is_resident ?? true),
                    'resident_name_1' => trim($request->resident_name_1 ?? '') ?: $existingBooking->resident_name_1,
                    'resident_name_2' => trim($request->resident_name_2 ?? '') ?: $existingBooking->resident_name_2,
                    'resident_name_3' => trim($request->resident_name_3 ?? '') ?: $existingBooking->resident_name_3,
                    'checkin_date' => $checkinDate,
                    'checkout_date' => $checkoutDate,
                    'guests_count' => $package->occupants,
                    'price' => ($existingBooking->price ?? 0) + $total, // Add hotel price to existing flight price (flight price already includes departure + return if round trip)
                    'commission_amount' => $commissionAmount,
                    'payment_type' => $paymentType,
                    'wallet_amount' => ($existingBooking->wallet_amount ?? 0) + $walletAmount,
                    'bank_amount' => ($existingBooking->bank_amount ?? 0) + $bankAmount,
                    'status' => $bookingStatus,
                    // Legacy fields
                    'guest_name' => $fullName ?? $existingBooking->guest_name,
                    'guest_email' => $email ?? $existingBooking->guest_email,
                    'guest_phone' => $phone ?? $existingBooking->guest_phone,
                    'special_requests' => $specialInstructions ?? $existingBooking->special_requests,
                ]);
                
                $booking = $existingBooking->fresh();
                Log::info('Existing flight booking updated with hotel/package:', [
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'flight_id' => $booking->flight_id,
                    'hotel_id' => $booking->hotel_id,
                    'package_id' => $booking->package_id,
                ]);
            } else {
                // Create new booking
            $bookingData = [
                'user_id' => $user->id,
                'created_by' => $createdBy,
                'accommodation_id' => $event->id,
                'hotel_id' => $hotel->id,
                'package_id' => $package->id,
                'flight_number' => $flightNumber,
                'flight_date' => $request->flight_date ? \Carbon\Carbon::parse($request->flight_date) : null,
                'flight_time' => $request->flight_time ? \Carbon\Carbon::parse($request->flight_time) : null,
                'airport' => $request->airport ?? null,
                'full_name' => $fullName,
                'company' => $request->company ?? null,
                'phone' => $phone,
                'email' => $email,
                'special_instructions' => $specialInstructions,
                'booker_is_resident' => (bool) ($request->booker_is_resident ?? true),
                'resident_name_1' => trim($request->resident_name_1 ?? '') ?: null,
                'resident_name_2' => trim($request->resident_name_2 ?? '') ?: null,
                'resident_name_3' => trim($request->resident_name_3 ?? '') ?: null,
                'checkin_date' => $checkinDate,
                'checkout_date' => $checkoutDate,
                'guests_count' => $package->occupants, // Total occupants (always equals package.occupants)
                'price' => $total,
                'commission_amount' => $commissionAmount,
                'payment_type' => $paymentType,
                'wallet_amount' => $walletAmount,
                'bank_amount' => $bankAmount,
                'status' => $bookingStatus,
                // Legacy fields for backward compatibility
                'guest_name' => $fullName,
                'guest_email' => $email,
                'guest_phone' => $phone,
                'special_requests' => $specialInstructions,
            ];
            
            Log::info('Booking data prepared:', $bookingData);

            $booking = Booking::create($bookingData);
            }

            // Decrease available rooms
            $package->chambres_restantes = max(0, $package->chambres_restantes - 1);
            $package->disponibilite = $package->chambres_restantes > 0;
            $package->save();
            
            DB::commit();
            
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Booking creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la réservation.',
                'error' => config('app.debug') ? $e->getMessage() : 'Une erreur est survenue.',
            ], 500);
        }

        // Auto-create invoice and generate PDF (best-effort; do not block booking)
        try {
            $booking->loadMissing(['event', 'hotel', 'package', 'flight']);

            // Set created_by if booking was created by an admin
            $createdBy = null;
            if (Auth::check() && Auth::user()->isAdmin()) {
                $createdBy = Auth::id();
            }

            $invoice = $booking->invoice()->create([
                'invoice_number' => 'FAC-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4)),
                'total_amount' => $booking->price ?? 0,
                'status' => 'draft',
                'created_by' => $createdBy,
            ]);

            $pdf = Pdf::loadView('invoices.template', compact('booking', 'invoice'));
            \App\Services\DualStorageService::makeDirectory('invoices');
            $relativePath = "invoices/{$invoice->id}.pdf";
            \App\Services\DualStorageService::put($relativePath, $pdf->output(), 'public');
            $invoice->update(['pdf_path' => $relativePath]);
        } catch (\Throwable $e) {
            Log::error('Failed to auto-create invoice or generate invoice PDF', [
                'booking_id' => $booking->id,
                'error_message' => $e->getMessage(),
            ]);
        }

        // Auto-create voucher and generate PDF (best-effort; do not block booking)
        try {
            $booking->loadMissing(['event', 'hotel', 'package', 'user', 'flight']);

            // Generate unique voucher number
            $voucherNumber = 'VOC-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4));
            while (\App\Models\Voucher::where('voucher_number', $voucherNumber)->exists()) {
                $voucherNumber = 'VOC-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4));
            }

            $userId = $booking->user_id ?? (Auth::check() ? Auth::id() : null);
            if (!$userId) {
                // Skip voucher creation if no user_id (legacy booking without user)
                Log::warning('Cannot create voucher: booking has no user_id', [
                    'booking_id' => $booking->id,
                ]);
                throw new \Exception('User ID is required to create voucher');
            }

            $voucher = $booking->voucher()->create([
                'user_id' => $userId,
                'voucher_number' => $voucherNumber,
                'emailed' => false,
            ]);

            // Generate voucher PDF
            $pdf = Pdf::loadView('vouchers.template', compact('booking', 'voucher'));
            \App\Services\DualStorageService::makeDirectory('vouchers');
            $relativePath = "vouchers/{$voucher->id}.pdf";
            \App\Services\DualStorageService::put($relativePath, $pdf->output(), 'public');
            $voucher->update(['pdf_path' => $relativePath]);
        } catch (\Throwable $e) {
            Log::error('Failed to auto-create voucher or generate voucher PDF', [
                'booking_id' => $booking->id,
                'error_message' => $e->getMessage(),
            ]);
        }

        // Reload booking with all relationships including invoice and voucher
        $booking->load(['event', 'hotel', 'package', 'invoice', 'voucher']);

        // Send email notification to admin
        $adminEmail = null;
        try {
            $adminEmail = config('mail.admin_email');
            
            // Fallback to from address if admin_email is not set
            if (empty($adminEmail)) {
                $adminEmail = config('mail.from.address');
                Log::warning('MAIL_ADMIN_EMAIL not configured, using MAIL_FROM_ADDRESS: ' . $adminEmail);
            }
            
            // Ensure we have a valid email address
            if (empty($adminEmail)) {
                Log::error('No admin email configured. Booking created but email not sent.', [
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                ]);
            } else {
                // Send the email
                Mail::to($adminEmail)->send(new BookingNotification($booking));
                Log::info('Booking notification email sent successfully', [
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'admin_email' => $adminEmail,
                ]);
            }
        } catch (\Exception $e) {
            // Log detailed error but don't fail the booking creation
            Log::error('Failed to send booking notification email', [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'admin_email' => $adminEmail ?? 'not configured',
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
            ]);
        }

        // Send email confirmation to user
        $userEmail = $booking->email ?? $booking->guest_email ?? $user->email ?? null;
        if ($userEmail) {
            try {
                Mail::to($userEmail)->send(new BookingConfirmation($booking));
                Log::info('Booking confirmation email sent to user', [
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'user_email' => $userEmail,
                ]);
            } catch (\Exception $e) {
                // Log detailed error but don't fail the booking creation
                Log::error('Failed to send booking confirmation email to user', [
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'user_email' => $userEmail,
                    'error_message' => $e->getMessage(),
                    'error_trace' => $e->getTraceAsString(),
                ]);
            }
        } else {
            Log::warning('Cannot send booking confirmation email: no user email found', [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
            ]);
        }

        // Reload wallet to get updated balance
        $wallet->refresh();
        
        return response()->json([
            'success' => true,
            'message' => $walletAmount > 0 ? 'Réservation payée avec succès.' : 'Réservation créée avec succès.',
            'data' => [
                'booking' => [
                    'id' => $booking->id,
                    'reference' => $booking->booking_reference,
                    'booking_reference' => $booking->booking_reference, // Alias for frontend compatibility
                    'status' => $booking->status,
                    'full_name' => $booking->full_name ?? $booking->guest_name,
                    'email' => $booking->email ?? $booking->guest_email,
                    'booker_is_resident' => $booking->booker_is_resident ?? true,
                    'resident_name_1' => $booking->resident_name_1,
                    'resident_name_2' => $booking->resident_name_2,
                    'resident_name_3' => $booking->resident_name_3,
                ],
                'payment' => [
                    'payment_type' => $paymentType,
                    'total' => number_format($total, 2, '.', ''),
                    'wallet_amount' => number_format($walletAmount, 2, '.', ''),
                    'bank_amount' => number_format($bankAmount, 2, '.', ''),
                    'wallet_balance_after' => number_format((float)$wallet->balance, 2, '.', ''),
                ],
                'invoice' => $booking->invoice ? [
                    'id' => $booking->invoice->id,
                    'invoice_number' => $booking->invoice->invoice_number,
                    'status' => $booking->invoice->status,
                ] : null,
                'voucher' => $booking->voucher ? [
                    'id' => $booking->voucher->id,
                    'voucher_number' => $booking->voucher->voucher_number,
                    'emailed' => $booking->voucher->emailed,
                    'visible' => $booking->status === 'paid', // Only visible when paid
                ] : null,
            ],
        ], 201); // 201 Created for successful POST
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

        $booking->load(['accommodation', 'hotel', 'package', 'flight']);
        
        // Check if flight prices should be shown (client dashboard setting)
        $showFlightPrices = $booking->accommodation ? ($booking->accommodation->show_flight_prices_client_dashboard ?? true) : true;
        
        // Format booking data
        $bookingData = $booking->toArray();
        
        // Format flight data conditionally
        if ($booking->flight) {
            $flightData = [
                'id' => $booking->flight->id,
                'full_name' => $booking->flight->full_name,
                'flight_class' => $booking->flight->flight_class,
                'flight_class_label' => $booking->flight->flight_class_label,
                'flight_category' => $booking->flight->flight_category ?? 'one_way',
                'flight_category_label' => $booking->flight->flight_category_label,
                'departure' => [
                    'date' => $booking->flight->departure_date ? \Carbon\Carbon::parse($booking->flight->departure_date)->format('Y-m-d') : null,
                    'time' => $booking->flight->departure_time ? \Carbon\Carbon::parse($booking->flight->departure_time)->format('H:i') : null,
                    'flight_number' => $booking->flight->departure_flight_number,
                    'airport' => $booking->flight->departure_airport,
                ],
                'arrival' => [
                    'date' => $booking->flight->arrival_date ? \Carbon\Carbon::parse($booking->flight->arrival_date)->format('Y-m-d') : null,
                    'time' => $booking->flight->arrival_time ? \Carbon\Carbon::parse($booking->flight->arrival_time)->format('H:i') : null,
                    'airport' => $booking->flight->arrival_airport,
                ],
                'reference' => $booking->flight->reference,
            ];
            
            // Conditionally include prices
            if ($showFlightPrices) {
                $flightData['departure']['price_ttc'] = (float) ($booking->flight->departure_price_ttc ?? 0);
            }
            
            // Add return flight if exists
            if ($booking->flight->return_date) {
                $flightData['return'] = [
                    'date' => $booking->flight->return_date ? \Carbon\Carbon::parse($booking->flight->return_date)->format('Y-m-d') : null,
                    'departure_time' => $booking->flight->return_departure_time ? \Carbon\Carbon::parse($booking->flight->return_departure_time)->format('H:i') : null,
                    'departure_airport' => $booking->flight->return_departure_airport,
                    'arrival_date' => $booking->flight->return_arrival_date ? \Carbon\Carbon::parse($booking->flight->return_arrival_date)->format('Y-m-d') : null,
                    'arrival_time' => $booking->flight->return_arrival_time ? \Carbon\Carbon::parse($booking->flight->return_arrival_time)->format('H:i') : null,
                    'arrival_airport' => $booking->flight->return_arrival_airport,
                    'flight_number' => $booking->flight->return_flight_number,
                ];
                
                if ($showFlightPrices) {
                    $flightData['return']['price_ttc'] = (float) ($booking->flight->return_price_ttc ?? 0);
                    $flightData['total_price'] = $booking->flight->total_price;
                }
            } elseif ($showFlightPrices) {
                $flightData['total_price'] = (float) ($booking->flight->departure_price_ttc ?? 0);
            }
            
            $bookingData['flight'] = $flightData;
        }
        
        // Add flight price visibility settings to accommodation data
        if ($booking->accommodation) {
            $bookingData['accommodation']['show_flight_prices_public'] = $booking->accommodation->show_flight_prices_public ?? true;
            $bookingData['accommodation']['show_flight_prices_client_dashboard'] = $booking->accommodation->show_flight_prices_client_dashboard ?? true;
            $bookingData['accommodation']['show_flight_prices_organizer_dashboard'] = $booking->accommodation->show_flight_prices_organizer_dashboard ?? true;
        }

        return response()->json([
            'success' => true,
            'data' => $bookingData,
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

    /**
     * Upload payment document for a booking.
     */
    public function uploadPaymentDocument(Request $request, Booking $booking)
    {
        // Ensure user can only upload documents for their own bookings
        $user = $request->user();
        if ($booking->user_id !== $user->id && 
            $booking->email !== $user->email && 
            $booking->guest_email !== $user->email) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'payment_document' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Delete old document if exists
            if ($booking->payment_document_path) {
                DualStorageService::delete($booking->payment_document_path, 'public');
            }

            // Store new document using DualStorageService
            $file = $request->file('payment_document');
            $filename = 'booking-' . $booking->id . '-ordre-paiement-' . time() . '.' . $file->getClientOriginalExtension();
            $path = DualStorageService::store($file, 'payment-documents', 'public');
            
            // Rename the file to our desired format
            $newPath = 'payment-documents/' . $filename;
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->move($path, $newPath);
                // Also move in public storage
                if (file_exists(public_path('storage/' . $path))) {
                    rename(public_path('storage/' . $path), public_path('storage/' . $newPath));
                }
            } else {
                $newPath = $path; // Use original path if move failed
            }

            $booking->update(['payment_document_path' => $newPath]);

            return response()->json([
                'success' => true,
                'message' => 'Payment document uploaded successfully',
                'data' => [
                    'booking' => [
                        'id' => $booking->id,
                        'payment_document_path' => $booking->payment_document_path,
                        'payment_document_url' => $booking->payment_document_url,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to upload payment document', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload payment document.',
            ], 500);
        }
    }

    /**
     * Upload flight ticket for a booking.
     */
    public function uploadFlightTicket(Request $request, Booking $booking)
    {
        // Ensure user can only upload documents for their own bookings
        $user = $request->user();
        if ($booking->user_id !== $user->id && 
            $booking->email !== $user->email && 
            $booking->guest_email !== $user->email) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'flight_ticket' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Delete old document if exists
            if ($booking->flight_ticket_path) {
                DualStorageService::delete($booking->flight_ticket_path, 'public');
            }

            // Store new document using DualStorageService
            $file = $request->file('flight_ticket');
            $filename = 'booking-' . $booking->id . '-flight-ticket-' . time() . '.' . $file->getClientOriginalExtension();
            $path = DualStorageService::store($file, 'flight-tickets', 'public');
            
            // Rename the file to our desired format
            $newPath = 'flight-tickets/' . $filename;
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->move($path, $newPath);
                // Also move in public storage
                if (file_exists(public_path('storage/' . $path))) {
                    rename(public_path('storage/' . $path), public_path('storage/' . $newPath));
                }
            } else {
                $newPath = $path; // Use original path if move failed
            }

            $booking->update(['flight_ticket_path' => $newPath]);

            return response()->json([
                'success' => true,
                'message' => 'Flight ticket uploaded successfully',
                'data' => [
                    'booking' => [
                        'id' => $booking->id,
                        'flight_ticket_path' => $booking->flight_ticket_path,
                        'flight_ticket_url' => $booking->flight_ticket_url,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to upload flight ticket', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload flight ticket.',
            ], 500);
        }
    }
}
